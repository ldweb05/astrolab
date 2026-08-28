# Analisi UX — Dashboard principale

**Stato:** PRONTA PER L'ANALISI
**Priorità:** ALTA
**Ordine:** 1
**Confronto:** MyAstral.org / Astrolab
**Ambito:** UX e workflow; la UI grafica resta invariata salvo problemi di comprensione.

---

## 1. Obiettivo

Valutare se la dashboard orienta immediatamente l'astrologo, mantiene chiaro il soggetto corrente e consente di raggiungere le attività principali con un percorso naturale.

---

## 2. Prerequisiti

1. Leggere `START_HERE_ux.md`.
2. Leggere `00_METODOLOGIA_ux.md`.
3. Verificare `DECISION_LOG_ux.md` e `BACKLOG_ux.md`.
4. Usare account e soggetti di prova.
5. Non acquisire password, dati di pagamento o informazioni personali non necessarie.

---

## 3. Evidenze richieste

Creare e utilizzare:
- `evidence/myastral/dashboard/`
- `evidence/astrolab/dashboard/`

Screenshot minimi:
1. dashboard completa dopo il login;
2. menu principale;
3. stato senza soggetto selezionato;
4. stato con soggetto selezionato;
5. azioni astrologiche principali;
6. eventuali menu contestuali o collegamenti rapidi.

Usare nomi progressivi, per esempio `01_dashboard_completa.png`.

---

## 4. Step A — Dashboard MyAstral.org

1. Accedere alla prima schermata operativa dopo il login.
2. Acquisire lo screenshot completo.
3. Annotare titolo, messaggi, avvisi e soggetto corrente.
4. Identificare l'azione proposta come principale.
5. Identificare le azioni secondarie.
6. Verificare se l'utente comprende immediatamente dove si trova e cosa può fare.

---

## 5. Step B — Censimento menu

Elencare tutte le voci presenti nei menu principali, laterali, superiori, contestuali e nei collegamenti rapidi.

| ID | Voce | Posizione | Funzione percepita | Frequenza | Note |
|---|---|---|---|---|---|
| M-01 | | | | Alta/Media/Bassa | |

Per ogni voce verificare:
- chiarezza del nome;
- coerenza del raggruppamento;
- presenza di duplicazioni;
- necessità di un soggetto selezionato;
- numero di passaggi prima del risultato.

---

## 6. Step C — Stato senza soggetto

1. Deselezionare il soggetto, quando possibile.
2. Tornare alla dashboard.
3. Acquisire uno screenshot.
4. Verificare quali funzioni restano disponibili.
5. Verificare se il sistema spiega come procedere.
6. Annotare errori, blocchi o percorsi senza uscita.

---

## 7. Step D — Stato con soggetto

1. Selezionare un soggetto di prova.
2. Tornare alla dashboard.
3. Acquisire uno screenshot.
4. Annotare quali dati sono immediatamente visibili.
5. Verificare se il soggetto corrente rimane riconoscibile.
6. Verificare se il contesto viene mantenuto passando ad altre funzioni.

---

## 8. Step E — Azioni principali

Verificare l'accesso a:
- scheda soggetto;
- tema natale;
- ricerca RSM;
- rilocazione;
- Rivoluzione Solare;
- Rivoluzione Lunare;
- confronti;
- stampa e report.

| Azione | Visibile subito | Clic | Soggetto mantenuto | Destinazione chiara |
|---|---:|---:|---:|---:|
| | Sì/No | | Sì/No | Sì/No |

---

## 9. Step F — Ripetizione in Astrolab

Ripetere integralmente gli Step A, B, C, D ed E in Astrolab.

Osservare il prodotto dal punto di vista dell'astrologo, senza analizzare codice, API o struttura tecnica.

---

## 10. Metriche obbligatorie

| Metrica | MyAstral.org | Astrolab | Note |
|---|---:|---:|---|
| Clic per selezionare un soggetto | | | |
| Clic per aprire il tema natale | | | |
| Clic per avviare una ricerca RSM | | | |
| Clic per aprire una rilocazione | | | |
| Clic per aprire una Rivoluzione Solare | | | |
| Funzioni principali visibili subito | | | |
| Schermate intermedie | | | |
| Azioni duplicate | | | |

Valutare inoltre da 1 a 5:
- orientamento iniziale;
- chiarezza del contesto;
- naturalezza dei termini;
- reperibilità delle funzioni;
- continuità operativa;
- carico cognitivo;
- recupero dagli errori.

---

## 11. Confronto finale

| Aspetto | MyAstral.org | Astrolab | Vantaggio | Motivazione |
|---|---|---|---|---|
| Orientamento iniziale | | | | |
| Selezione soggetto | | | | |
| Accesso alle funzioni | | | | |
| Chiarezza dei menu | | | | |
| Continuità del contesto | | | | |
| Numero di passaggi | | | | |

Distinguere sempre tra:
- problema UX verificabile;
- superiorità organizzativa;
- superiorità funzionale;
- differenza neutra;
- preferenza soggettiva.

---

## 12. Decisioni

Registrare esplicitamente:
- punti di forza di Astrolab da preservare;
- criticità riproducibili;
- beneficio atteso di ogni proposta;
- costo tecnico indicativo;
- rischi;
- eventuale inserimento in `BACKLOG_ux.md`;
- eventuale decisione in `DECISION_LOG_ux.md`.

---

## 13. Condizioni di completamento

- [ ] Screenshot MyAstral.org raccolti.
- [ ] Screenshot Astrolab raccolti.
- [ ] Menu censiti.
- [ ] Stato senza soggetto analizzato.
- [ ] Stato con soggetto analizzato.
- [ ] Azioni principali verificate.
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

## 14. Esito

**Stato finale:** DA COMPILARE

- [ ] Nessuna modifica necessaria.
- [ ] Affinamenti minori consigliati.
- [ ] Riorganizzazione parziale consigliata.
- [ ] Analisi tecnica separata necessaria.
- [ ] Evidenze insufficienti.

---
