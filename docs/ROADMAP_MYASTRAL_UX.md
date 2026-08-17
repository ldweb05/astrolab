# ROADMAP — Allineamento ASTROLAB / MyAstral.org

**Stato:** aperta — analisi completata, implementazione da pianificare
**Ultimo aggiornamento:** 2026-08-17
**Origine:** confronto diretto tra ASTROLAB e myastral.org (software ufficiale di Ciro Discepolo,
autore delle 34 regole dell'Astrologia Attiva), condotto su un caso reale (ricerca RSM condizione
Decima, aeroporti, soggetto Jannik Sinner, anno 2025).

---

## 1. Contesto

MyAstral.org è il riferimento "ufficiale" per le 34 regole di Astrologia Attiva di Ciro Discepolo.
ASTROLAB implementa una versione propria dello stesso corpus di regole (`includes/RuleEngine.php`),
sviluppata indipendentemente. Un confronto diretto tra i due sistemi, sullo stesso soggetto/anno/
condizione, ha mostrato risultati radicalmente diversi (685 aeroporti mostrati da ASTROLAB contro
8 raccomandati da MyAstral, con distribuzione geografica quasi opposta).

L'analisi ha escluso la presenza di un bug nel senso stretto: sono state individuate quattro cause
concrete e verificabili nel codice, elencate di seguito come voci di roadmap.

---

## 2. Grafici vettoriali — ✅ Nessuna azione richiesta

MyAstral mostra le ruote zodiacali come immagini vettoriali. Verificato che ASTROLAB usa già SVG
nativo del browser (`js/zodiac_wheel.js`, `ZodiacWheel.disegna()`) — già vettoriale, già
ridimensionabile senza perdita di qualità. Nessuna modifica tecnica necessaria su questo punto.
Resta aperto solo un eventuale confronto di **stile grafico** (colori, font, spessore linee aspetti),
da trattare come voce estetica separata se richiesto in futuro.

---

## 3. Sistema di valutazione a stelle — scala e bonus mancanti

### 3.1 Tetto rigido a 5 stelle

`includes/RuleEngine.php`, funzione di calcolo finale delle stelle, applica:

```php
return max(0, min(5, $stelle));
```

Questo comprime in un'unica fascia "★★★★★" qualunque risultato che meriterebbe, secondo la
descrizione di Discepolo, un punteggio da 5 a 10. Effetto pratico: ASTROLAB non riesce a
distinguere un risultato "discreto" da uno "eccezionale" — sono entrambi mostrati come 5 stelle.

**Azione proposta:** valutare l'estensione della scala a 0-10 stelle (o comunque oltre il tetto
attuale di 5), rivedendo tutti i punti dell'interfaccia che assumono un massimo di 5 (badge
stelline, filtro `stelline_min`, ordinamento, eventuale rendering grafico a stelle pieno/vuoto).

**Impatto:** medio-alto — tocca il cuore del RuleEngine e più punti UI a valle (tabelle risultati
RSM/RL, filtri, badge). Da pianificare come intervento dedicato, non una micro-patch.

### 3.2 Bonus mancante: Giove entro 2° dalla cuspide della X casa

Per condizione "Decima", secondo quanto riportato dall'utente dall'intervista di Discepolo:
Sole da solo in X = 2 stelle; + Venere = 4 stelle; + Giove = punteggio più alto ancora,
specialmente se **Giove è entro 2° dalla cuspide della X casa** (posizione fortemente angolare) →
punteggio 8-10.

Verificato in `includes/RuleEngine.php`: esistono già regole "entro 2-3° dalla cuspide", ma sono
tutte **veti** (es. Sole/Giove entro 2° dalla cuspide XI = troppo vicino a uscire dalla X = veto;
Marte/Saturno entro 2° dagli angoli = veto, regola 33). **Non esiste una regola di bonus positivo**
per Giove angolare in ingresso/presenza forte nella X casa.

**Azione proposta:** aggiungere una regola di bonus dedicata (nome di lavoro: "Giove angolare in
X") che, quando Giove è entro un orbe configurabile (2° come da indicazione di Discepolo) dalla
cuspide della X casa RS, applichi un bonus significativo al punteggio — da tarare insieme
all'estensione della scala (punto 3.1), perché senza quella il bonus resterebbe comunque
compresso dal tetto a 5.

**Impatto:** medio — è una regola aggiuntiva, isolabile, ma il suo effetto reale dipende dal
completamento del punto 3.1.

### 3.3 Soglia minima di visualizzazione (`stelline_min`)

`api/ricerca_api.php`: il parametro `stelline_min` ha default **0**, quindi ASTROLAB mostra ogni
aeroporto che supera i controlli di veto/esclusione, indipendentemente dal punteggio. MyAstral,
nello stesso caso di test, mostra solo gli 8 aeroporti "raccomandati" (presumibilmente il vertice
della sua scala 0-10).

**Azione proposta:** valutare, dopo il completamento dei punti 3.1 e 3.2, se alzare il default di
`stelline_min` (es. a un valore corrispondente alle vecchie "5 stelle piene" sulla nuova scala
estesa) per avvicinare l'esperienza utente di default a quella di MyAstral, lasciando comunque
la possibilità di allargare la ricerca abbassando il filtro.

**Impatto:** basso — è già un parametro esistente e configurabile, si tratta di rivedere un default
e/o l'etichettatura in UI, non di nuova logica.

---

## 4. FiltroEsclusione.php — filtro aggiuntivo non ufficiale

`includes/FiltroEsclusione.php` applica 5 esclusioni supplementari (Sole/Marte RS in I-VI-XII,
ASC RS in I-VI-XII natale, Saturno RS in X, stellium 3+ pianeti in una casa) che **non fanno parte
delle 34 regole ufficiali di Discepolo** — è un livello aggiuntivo richiesto in passato
dall'astrologo (l'utente) stesso, come documentato nell'intestazione del file.

Ipotesi verificata come plausibile: alle latitudini estreme (dove MyAstral trova i suoi risultati
migliori per questo caso) le case si comprimono, rendendo più probabile uno stellium — che
ASTROLAB esclude sistematicamente tramite questo filtro, MyAstral no.

**Azione proposta:** nessuna modifica di default — il filtro è stato richiesto intenzionalmente e
resta valido finché l'utente non decide diversamente. Da tenere presente come fattore noto di
divergenza rispetto a MyAstral quando si fanno confronti diretti, e da menzionare eventualmente
nell'interfaccia (es. tooltip/nota) per trasparenza verso l'utente finale dell'app.

**Impatto:** nessuno richiesto ora — solo documentazione/trasparenza.

---

## 5. Piano di lavoro proposto (ordine consigliato)

1. **Punto 3.1** — Estensione scala stelle oltre il tetto di 5 (intervento strutturale, da fare
   per primo perché condiziona il punto successivo)
2. **Punto 3.2** — Aggiunta regola di bonus "Giove angolare in X" (condizione Decima; valutare se
   estendere il principio anche ad altre condizioni tematiche in un secondo momento)
3. **Punto 3.3** — Revisione default `stelline_min` e relativa UI, a valle dei due punti precedenti
4. **Punto 4** — Nota di trasparenza in UI su FiltroEsclusione.php (facoltativo, bassa priorità)
5. Ulteriori confronti UX (grafica, altre pagine di MyAstral) da valutare volta per volta man mano
   che l'utente porta nuovo materiale (screenshot, video, trascrizioni) da analizzare

Ogni punto sopra elencato **deve** essere sviluppato seguendo il workflow operativo descritto in
`docs/PROMPT_OPERATIVO_ASTROLAB.md`, un punto alla volta, con verifica e conferma esplicita prima
del commit.
