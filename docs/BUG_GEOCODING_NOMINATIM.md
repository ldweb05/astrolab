# Bug — Geocoding impreciso (residenza e luogo di nascita)

## Sintomo
Inserendo una città come "Caserta" o "Zurigo" nei campi di ricerca
località (sia "Luogo di Nascita" che "Città di Residenza"), le
coordinate salvate risultano spostate di diversi km dal centro città
reale — verificato visivamente tramite la mappa aggiunta a
`dashboard.php` (branch `new_dashboard`), che ha reso visibile un
problema preesistente e passato finora inosservato.

Casi osservati (2026-08-23):
- Soggetto "Lorenzo Diana" (id 23/25), residenza "Caserta":
  salvato (41.2035, 14.1169) — spostato a nord-ovest rispetto al
  centro città reale.
- Soggetto "Test 2" (id 85), residenza "Zurigo": stesso tipo di
  scostamento.

## Causa sospetta (non ancora confermata con certezza)
`cercaLuogoResidenza()` e `cercaLuogo()` in `www/js/app.js` sono
identiche nella query verso Nominatim: https://nominatim.openstreetmap.org/search?q=...&format=json&limit=8&addressdetails=1 
Nessun filtro sul tipo di risultato (`featuretype`/`class`). Quando
il nome cercato corrisponde a un **confine amministrativo** (il
comune nel suo insieme) invece che a un **punto/luogo preciso**,
Nominatim può restituire il **centroide dell'intero territorio
comunale**, che per comuni con territorio esteso/irregolare può
cadere a diversi km dal centro città. Coerente con lo scostamento
osservato in entrambi i casi.

## Superficie del bug
Riguarda potenzialmente ENTRAMBI i campi di geocoding (non solo
residenza): `cercaLuogo()` (luogo di nascita) e
`cercaLuogoResidenza()` (residenza), stesso codice condiviso in
`www/js/app.js` — quindi impatta ogni pagina che permette di
inserire/modificare un soggetto.

## Da fare (non ancora iniziato)
- Confermare la causa (verificare la risposta raw di Nominatim per
  "Caserta" e "Zurigo" e controllare il campo `class`/`type` del
  risultato scelto).
- Valutare un fix: es. preferire risultati con `class=place` rispetto
  a `class=boundary`, o mostrare il tipo di risultato nel dropdown
  cosicché chi inserisce il soggetto possa scegliere consapevolmente.
- Branch dedicato da aprire quando si affronta (non `new_dashboard`,
  fuori scope — tocca `js/app.js` condiviso da tutte le pagine).
- Verificare se soggetti già esistenti hanno coordinate imprecise da
  ricorreggere manualmente.
