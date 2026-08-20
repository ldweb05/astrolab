# Analisi UX — Ricerca RSM

**Stato:** PRONTA PER L'ANALISI
**Priorità:** ALTA
**Ordine:** 3
**Confronto:** MyAstral.org / Astrolab
**Ambito:** workflow completo della ricerca di Rivoluzione Solare Mirata.

---

## Decisione applicata — UX-0010

Nuova pagina dedicata `ricerche.php` (Anno + dropdown RS/RL dell'anno gia calcolate via
calcolo batch on-demand + Condizione + pulsanti Rilocazione/Transiti) come punto di ingresso
guidato verso questo workflow, senza modificare la logica o i risultati di `ricerca.php`
descritti sotto. Decisione registrata in `DECISION_LOG_ux.md` il 2026-08-20, in attesa di
implementazione tecnica (roadmap Fase 1-2, `PROMPT_OPERATIVO_ASTROLAB_ALLIUNEAMENTO_UX`). Il
protocollo di analisi comparativa sotto resta valido per eventuali affinamenti futuri della
ricerca RSM stessa.

---

## 1. Obiettivo

Valutare se l'astrologo può impostare, eseguire, comprendere, correggere e confrontare una ricerca RSM senza perdere il contesto del soggetto.

L'analisi deve verificare:
- chiarezza dei parametri;
- naturalezza dei filtri;
- gestione del numero di località;
- leggibilità dei risultati;
- continuità tra ricerca, valutazione e confronto;
- facilità di modifica e rilancio della ricerca.

---

## 2. Prerequisiti

1. Leggere `START_HERE_ux.md`.
2. Leggere `00_METODOLOGIA_ux.md`.
3. Verificare `DECISION_LOG_ux.md` e `BACKLOG_ux.md`.
4. Utilizzare lo stesso soggetto di prova in entrambe le applicazioni.
5. Definire in anticipo gli scenari e il numero di località da analizzare.
6. Non confrontare l'accuratezza astronomica in questa fase: l'oggetto è esclusivamente la UX.

---

## 3. Evidenze richieste

Creare e utilizzare:
- `evidence/myastral/ricerca-rsm/`
- `evidence/astrolab/ricerca-rsm/`

Screenshot minimi:
1. accesso alla ricerca RSM;
2. soggetto e anno selezionati;
3. filtri iniziali;
4. ricerca con poche località;
5. ricerca media;
6. ricerca estesa;
7. risultati ordinati;
8. dettaglio di una località;
9. confronto tra località;
10. modifica dei parametri;
11. stato senza risultati;
12. eventuali errori o avvisi.

---

## 4. Scenari standard

Utilizzare gli stessi scenari in MyAstral.org e Astrolab.

| Scenario | Numero località | Scopo |
|---|---:|---|
| RSM-S1 | 5 | Verificare il flusso minimo |
| RSM-S2 | 25 | Verificare leggibilità e ordinamento |
| RSM-S3 | 100 | Verificare gestione di molti risultati |
| RSM-S4 | Massimo ragionevole | Verificare ricerca estesa e prestazioni percepite |

Il numero può essere adattato alle possibilità di MyAstral.org, ma ogni variazione deve essere documentata.

---

## 5. Step A — Accesso alla ricerca

1. Selezionare il soggetto di prova.
2. Individuare il comando per la ricerca RSM.
3. Contare i clic necessari.
4. Verificare se il soggetto viene mantenuto.
5. Verificare se anno e località di partenza sono precompilati.
6. Acquisire uno screenshot della schermata iniziale.
7. Annotare eventuali passaggi intermedi.

---

## 6. Step B — Censimento dei parametri

Elencare tutti i parametri disponibili.

| ID | Parametro | Obbligatorio | Valore iniziale | Terminologia chiara | Note |
|---|---|---:|---|---:|---|
| P-01 | | Sì/No | | Sì/No | |

Verificare almeno:
- anno della Rivoluzione Solare;
- area geografica;
- nazioni o continenti;
- distanza o raggio;
- aeroporti o località;
- filtri astrologici;
- esclusioni;
- ordinamento;
- quantità massima di risultati;
- eventuali preferenze salvate.

---

## 7. Step C — Ricerca minima

1. Configurare lo scenario `RSM-S1`.
2. Avviare la ricerca.
3. Contare clic e schermate.
4. Misurare il tempo percepito.
5. Verificare se lo stato di avanzamento è comprensibile.
6. Controllare che il soggetto e i parametri restino visibili.
7. Acquisire screenshot dei risultati.
8. Aprire il dettaglio della prima località.

---

## 8. Step D — Ricerca media

1. Configurare lo scenario `RSM-S2`.
2. Avviare la ricerca.
3. Verificare paginazione, scorrimento e ordinamento.
4. Verificare se i risultati più importanti emergono chiaramente.
5. Controllare se è possibile tornare ai parametri senza perdere i risultati.
6. Aprire almeno tre località.
7. Verificare se il confronto è immediato.

---

## 9. Step E — Ricerca estesa

1. Configurare gli scenari `RSM-S3` e `RSM-S4`.
2. Avviare la ricerca.
3. Verificare feedback, attesa e possibilità di annullamento.
4. Controllare se l'interfaccia resta utilizzabile.
5. Verificare ordinamento, filtri successivi e paginazione.
6. Controllare se i risultati vengono deduplicati in modo comprensibile.
7. Verificare se il sistema comunica eventuali limiti.

---

## 10. Step F — Lettura dei risultati

Per ogni risultato verificare la presenza e la chiarezza di:
- località;
- nazione;
- coordinate;
- distanza;
- aeroporto o punto di riferimento;
- elementi astrologici rilevanti;
- indicatori di priorità;
- motivazioni dell'ordinamento;
- collegamento al tema completo;
- azioni disponibili.

| Informazione | Visibile subito | Comprensibile | Utile alla decisione | Note |
|---|---:|---:|---:|---|
| | Sì/No | Sì/No | Sì/No | |

---

## 11. Step G — Ordinamento e filtri sui risultati

1. Cambiare ordinamento.
2. Applicare un filtro dopo la ricerca.
3. Rimuovere il filtro.
4. Verificare se i risultati si aggiornano senza perdere il contesto.
5. Verificare se l'ordinamento corrente è sempre visibile.
6. Controllare se i criteri sono comprensibili a un astrologo.
7. Annotare eventuali termini eccessivamente tecnici.

---

## 12. Step H — Dettaglio e confronto località

1. Aprire il dettaglio di una località.
2. Tornare ai risultati.
3. Aprire una seconda località.
4. Verificare se è possibile confrontarle direttamente.
5. Controllare se il soggetto, l'anno e i filtri restano visibili.
6. Verificare salvataggio, preferiti o lista di confronto.
7. Controllare eventuale limite al numero di confronti.

---

## 13. Step I — Modifica e nuova ricerca

1. Tornare ai parametri.
2. Modificare un solo filtro.
3. Eseguire nuovamente la ricerca.
4. Verificare quali parametri vengono mantenuti.
5. Verificare se i risultati precedenti sono recuperabili.
6. Controllare se il sistema distingue chiaramente vecchia e nuova ricerca.
7. Verificare reset e ripristino dei valori iniziali.

---

## 14. Step J — Assenza di risultati ed errori

1. Impostare filtri volutamente restrittivi.
2. Eseguire una ricerca senza risultati.
3. Verificare il messaggio mostrato.
4. Controllare se vengono suggerite correzioni.
5. Simulare, quando possibile, un input non valido.
6. Verificare se il lavoro già inserito viene conservato.
7. Annotare percorsi senza uscita o messaggi tecnici.

---

## 15. Step K — Ripetizione in Astrolab

Ripetere integralmente gli Step A-J in Astrolab utilizzando:
- lo stesso soggetto;
- lo stesso anno;
- gli stessi scenari;
- filtri equivalenti;
- lo stesso ordine di esecuzione.

Le differenze funzionali devono essere annotate, ma non devono alterare il confronto del workflow.

---

## 16. Metriche obbligatorie

| Metrica | MyAstral.org | Astrolab | Note |
|---|---:|---:|---|
| Clic per aprire la ricerca RSM | | | |
| Clic per configurare RSM-S1 | | | |
| Clic per avviare la ricerca | | | |
| Schermate intermedie | | | |
| Parametri obbligatori | | | |
| Risultati visibili senza scorrere | | | |
| Clic per aprire un dettaglio | | | |
| Clic per confrontare due località | | | |
| Clic per modificare e rilanciare | | | |
| Passaggi che fanno perdere il contesto | | | |

Valutare da 1 a 5:
- chiarezza dei filtri;
- comprensibilità della terminologia;
- percezione dello stato di avanzamento;
- leggibilità dei risultati;
- facilità di confronto;
- continuità soggetto-ricerca-risultato;
- facilità di correzione;
- carico cognitivo.

---

## 17. Confronto finale

| Aspetto | MyAstral.org | Astrolab | Vantaggio | Motivazione |
|---|---|---|---|---|
| Accesso alla ricerca | | | | |
| Configurazione parametri | | | | |
| Quantità di località | | | | |
| Feedback durante il calcolo | | | | |
| Lettura risultati | | | | |
| Ordinamento e filtri | | | | |
| Dettaglio località | | | | |
| Confronto località | | | | |
| Modifica della ricerca | | | | |
| Gestione errori | | | | |

---

## 18. Decisioni

Registrare:
- punti di forza di Astrolab da preservare;
- passaggi inutili o duplicati;
- terminologia migliorabile;
- criticità riproducibili;
- problemi che dipendono dalla quantità di risultati;
- proposte nel `BACKLOG_ux.md`;
- decisioni approvate nel `DECISION_LOG_ux.md`.

Non proporre una modifica solo perché MyAstral.org adotta una soluzione differente.

---

## 19. Condizioni di completamento

- [ ] Evidenze MyAstral.org raccolte.
- [ ] Evidenze Astrolab raccolte.
- [ ] Parametri censiti.
- [ ] RSM-S1 completato.
- [ ] RSM-S2 completato.
- [ ] RSM-S3 completato.
- [ ] RSM-S4 completato o limite documentato.
- [ ] Ordinamento e filtri verificati.
- [ ] Dettaglio località verificato.
- [ ] Confronto località verificato.
- [ ] Modifica e rilancio verificati.
- [ ] Assenza di risultati verificata.
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

## 20. Esito

**Stato finale:** DA COMPILARE

- [ ] Nessuna modifica necessaria.
- [ ] Affinamenti minori consigliati.
- [ ] Riorganizzazione parziale consigliata.
- [ ] Analisi tecnica separata necessaria.
- [ ] Evidenze insufficienti.

---
