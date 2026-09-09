# Analisi UX — Scheda dati utente

**Stato:** PRONTA PER L'ANALISI
**Priorità:** ALTA
**Ordine:** 2
**Confronto:** MyAstral.org / Astrolab
**Ambito:** gestione del soggetto e continuità del workflow astrologico.

---

## 1. Obiettivo

Valutare se l'astrologo può creare, trovare, aprire, modificare e utilizzare un soggetto in modo naturale, rapido e sicuro.

La scheda deve consentire di comprendere immediatamente:
- chi è il soggetto corrente;
- quali dati sono disponibili;
- quali dati mancano;
- quali analisi possono essere avviate;
- come correggere o aggiornare le informazioni.

---

## 2. Prerequisiti

1. Leggere `START_HERE_ux.md`.
2. Leggere `00_METODOLOGIA_ux.md`.
3. Verificare `DECISION_LOG_ux.md` e `BACKLOG_ux.md`.
4. Utilizzare soggetti di prova privi di dati personali reali.
5. Preparare almeno un soggetto completo e uno con dati incompleti.

---

## 3. Evidenze richieste

Creare e utilizzare:
- `evidence/myastral/scheda-utente/`
- `evidence/astrolab/scheda-utente/`

Screenshot minimi:
1. elenco soggetti;
2. creazione nuovo soggetto;
3. scheda completa;
4. scheda con dati incompleti;
5. modalità modifica;
6. azioni astrologiche disponibili;
7. messaggi di errore o validazione;
8. conferma di eliminazione, se prevista.

Non acquisire dati personali reali.

---

## 4. Step A — Ricerca e selezione del soggetto

1. Aprire l'area soggetti.
2. Verificare come viene presentato l'elenco.
3. Cercare un soggetto per nome.
4. Verificare eventuali filtri e ordinamenti.
5. Aprire il soggetto.
6. Tornare all'elenco.
7. Selezionare un secondo soggetto.
8. Verificare se il contesto corrente cambia in modo evidente.

Annotare:
- numero di clic;
- chiarezza della selezione;
- riconoscibilità del soggetto attivo;
- facilità nel tornare all'elenco;
- eventuali ambiguità tra apertura e selezione.

---

## 5. Step B — Creazione del soggetto

1. Avviare la creazione di un nuovo soggetto.
2. Elencare tutti i campi richiesti.
3. Distinguere campi obbligatori, facoltativi e derivati.
4. Inserire dati validi.
5. Salvare.
6. Verificare il risultato.
7. Ripetere con dati mancanti o non validi.
8. Annotare messaggi di errore e suggerimenti.

| Campo | Obbligatorio | Formato | Valore predefinito | Validazione | Note |
|---|---:|---|---|---|---|
| Nome | | | | | |

---

## 6. Step C — Lettura della scheda

Verificare quali informazioni sono immediatamente visibili:
- nome;
- data di nascita;
- ora;
- località;
- coordinate;
- fuso orario;
- eventuale ora legale;
- note;
- dati tecnici;
- ultime attività;
- analisi collegate.

Valutare:
- gerarchia delle informazioni;
- distinzione tra dati astrologici e dati tecnici;
- visibilità dei dati essenziali;
- presenza di informazioni ridondanti;
- comprensibilità per un astrologo non tecnico.

---

## 7. Step D — Modifica e correzione

1. Aprire la modalità modifica.
2. Cambiare un dato non critico.
3. Salvare e verificare l'aggiornamento.
4. Modificare data, ora o località.
5. Verificare eventuali avvisi sull'impatto astrologico.
6. Annullare una modifica.
7. Verificare se i dati precedenti vengono conservati.
8. Controllare se il sistema distingue salvataggio e ricalcolo.

Annotare eventuali rischi di modifica involontaria.

---

## 8. Step E — Azioni disponibili dalla scheda

Verificare se dalla scheda è possibile avviare direttamente:
- tema natale;
- Rivoluzione Solare;
- ricerca RSM;
- rilocazione;
- Rivoluzione Lunare;
- confronti;
- stampa;
- report;
- duplicazione del soggetto;
- eliminazione del soggetto.

| Azione | Visibile | Clic | Contesto mantenuto | Conferma richiesta | Note |
|---|---:|---:|---:|---:|---|
| | Sì/No | | Sì/No | Sì/No | |

---

## 9. Step F — Dati incompleti ed errori

Creare o utilizzare un soggetto con dati incompleti.

Verificare:
- quali analisi restano disponibili;
- come vengono segnalati i dati mancanti;
- se il sistema spiega come correggere il problema;
- se esistono percorsi senza uscita;
- se i messaggi sono comprensibili;
- se viene preservato il lavoro già inserito.

---

## 10. Step G — Eliminazione e protezione

Quando disponibile in ambiente sicuro:
1. tentare l'eliminazione di un soggetto di prova;
2. verificare la presenza di una conferma;
3. verificare la chiarezza del messaggio;
4. verificare se l'azione è reversibile;
5. controllare eventuali conseguenze sulle analisi salvate.

Non eliminare soggetti reali.

---

## 11. Step H — Ripetizione in Astrolab

Ripetere integralmente gli Step A, B, C, D, E, F e G in Astrolab.

Non analizzare il codice: osservare esclusivamente il comportamento percepito dall'astrologo.

---

## 12. Metriche obbligatorie

| Metrica | MyAstral.org | Astrolab | Note |
|---|---:|---:|---|
| Clic per trovare un soggetto | | | |
| Clic per aprire la scheda | | | |
| Clic per creare un soggetto | | | |
| Clic per modificare un dato | | | |
| Clic per avviare il tema natale | | | |
| Campi obbligatori | | | |
| Schermate intermedie | | | |
| Errori non spiegati | | | |
| Azioni principali visibili | | | |

Valutare inoltre da 1 a 5:
- chiarezza della scheda;
- velocità di selezione;
- qualità delle validazioni;
- sicurezza delle modifiche;
- continuità verso le analisi;
- comprensibilità della terminologia;
- facilità di recupero dagli errori.

---

## 13. Confronto finale

| Aspetto | MyAstral.org | Astrolab | Vantaggio | Motivazione |
|---|---|---|---|---|
| Ricerca soggetto | | | | |
| Creazione | | | | |
| Lettura dati | | | | |
| Modifica | | | | |
| Validazione | | | | |
| Accesso alle analisi | | | | |
| Protezione eliminazione | | | | |
| Continuità del contesto | | | | |

---

## 14. Decisioni

Registrare:
- punti di forza di Astrolab da preservare;
- criticità riproducibili;
- differenze puramente tecniche da non considerare UX;
- proposte organizzative;
- rischi per dati e continuità del lavoro;
- eventuali voci nel `BACKLOG_ux.md`;
- eventuali decisioni nel `DECISION_LOG_ux.md`.

---

## 15. Condizioni di completamento

- [ ] Ricerca e selezione analizzate.
- [ ] Creazione analizzata.
- [ ] Campi censiti.
- [ ] Scheda completa analizzata.
- [ ] Dati incompleti analizzati.
- [ ] Modifica e annullamento verificati.
- [ ] Azioni astrologiche censite.
- [ ] Validazioni e messaggi verificati.
- [ ] Eliminazione verificata in sicurezza.
- [ ] Screenshot MyAstral.org raccolti.
- [ ] Screenshot Astrolab raccolti.
- [ ] Metriche compilate.
- [ ] Confronto completato.
- [ ] Punti di forza registrati.
- [ ] Criticità e proposte registrate.
- [ ] `START_HERE_ux.md` aggiornato.
- [ ] `CHANGELOG_ux.md` aggiornato.
- [ ] Test documentali eseguiti.
- [ ] Documentazione allineata.
- [ ] Diff Git revisionato.
- [ ] Commit eseguito.
- [ ] Push eseguito.
- [ ] Working tree finale verificato.

---

## 16. Esito

**Stato finale:** DA COMPILARE

- [ ] Nessuna modifica necessaria.
- [ ] Affinamenti minori consigliati.
- [ ] Riorganizzazione parziale consigliata.
- [ ] Analisi tecnica separata necessaria.
- [ ] Evidenze insufficienti.

---
