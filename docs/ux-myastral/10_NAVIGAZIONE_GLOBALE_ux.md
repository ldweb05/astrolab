# Analisi UX — Navigazione globale

**Stato:** PRONTA PER L'ANALISI
**Priorità:** ALTA
**Ordine:** 10
**Confronto:** MyAstral.org / Astrolab
**Ambito:** UX e workflow; nessuna copia o revisione estetica generale.

---

## Decisione applicata — UX-0009

Riorganizzazione della nav a 9 voci e sostituzione del dropdown "Ricerche" con link secco
verso la nuova pagina dedicata (vedi UX-0010). Decisione registrata in `DECISION_LOG_ux.md`
il 2026-08-20, in attesa di implementazione tecnica (roadmap Fase 3,
`PROMPT_OPERATIVO_ASTROLAB_ALLIUNEAMENTO_UX`). Il protocollo di analisi comparativa sotto
resta valido per eventuali affinamenti futuri della navigazione globale.

---

## 1. Obiettivo

Valutare coerenza, prevedibilità e continuità dell'intera applicazione, individuando duplicazioni, percorsi interrotti e perdita del contesto.

---

## 2. Prerequisiti

1. Leggere `START_HERE_ux.md`.
2. Leggere `00_METODOLOGIA_ux.md`.
3. Verificare `DECISION_LOG_ux.md` e `BACKLOG_ux.md`.
4. Utilizzare gli stessi soggetti e scenari nelle due applicazioni.
5. Usare esclusivamente dati di prova non sensibili.

---

## 3. Evidenze richieste

- `evidence/myastral/navigazione-globale/`
- `evidence/astrolab/navigazione-globale/`

Acquisire screenshot dell'accesso, dei parametri, del risultato, delle modifiche, degli errori e delle azioni finali.

---

## 4. Step A — Mappa globale

1. Censire menu principali, secondari e contestuali.
2. Creare una mappa delle aree e dei collegamenti.

---

## 5. Step B — Terminologia

1. Verificare coerenza dei nomi tra menu, titoli, pulsanti e documenti.
2. Individuare termini tecnici o ambigui.

---

## 6. Step C — Contesto corrente

1. Verificare visibilità del soggetto, anno, località e analisi corrente.
2. Controllare cosa accade passando tra funzioni.

---

## 7. Step D — Ritorno e orientamento

1. Verificare pulsanti indietro, breadcrumb e ritorno alla dashboard.
2. Individuare percorsi senza uscita.

---

## 8. Step E — Persistenza

1. Controllare quali dati e filtri vengono mantenuti.
2. Verificare perdite involontarie di lavoro.

---

## 9. Step F — Duplicazioni

1. Individuare funzioni replicate in più menu.
2. Valutare se la duplicazione aiuta o confonde.

---

## 10. Step G — Coerenza delle azioni

1. Confrontare nomi e posizione di salva, modifica, annulla, elimina, stampa e confronta.

---

## 11. Step H — Flussi completi

1. Eseguire almeno tre percorsi end-to-end:
2. soggetto → tema natale → RS;
3. soggetto → ricerca RSM → dettaglio località;
4. soggetto → rilocazione → confronto → stampa.

---

## 12. Ripetizione in Astrolab

Ripetere integralmente tutti gli step in Astrolab utilizzando gli stessi dati, scenari e ordine operativo.

Annotare separatamente le differenze funzionali; la valutazione corrente riguarda organizzazione, comprensione e continuità del workflow.

---

## 13. Metriche obbligatorie

| Metrica | MyAstral.org | Astrolab | Note |
|---|---:|---:|---|
| Menu principali | | | |
| Voci duplicate | | | |
| Termini incoerenti | | | |
| Percorsi senza uscita | | | |
| Perdite del soggetto corrente | | | |
| Perdite dei filtri | | | |
| Clic medi tra aree correlate | | | |
| Errori non spiegati | | | |

Valutare inoltre da 1 a 5:

- chiarezza;
- reperibilità;
- continuità del contesto;
- naturalezza della terminologia;
- carico cognitivo;
- prevedibilità;
- recupero dagli errori.

---

## 14. Confronto finale

| Aspetto | MyAstral.org | Astrolab | Vantaggio | Motivazione |
|---|---|---|---|---|
| Accesso | | | | |
| Configurazione | | | | |
| Lettura | | | | |
| Modifica | | | | |
| Continuità | | | | |
| Gestione errori | | | | |

Distinguere tra problema UX verificabile, superiorità funzionale, superiorità organizzativa, differenza neutra e preferenza soggettiva.

---

## 15. Decisioni

Registrare:

- punti di forza di Astrolab da preservare;
- criticità riproducibili;
- passaggi evitabili;
- terminologia migliorabile;
- proposte nel `BACKLOG_ux.md`;
- decisioni approvate nel `DECISION_LOG_ux.md`.

Non proporre modifiche soltanto perché MyAstral.org utilizza una soluzione differente.

---

## 16. Condizioni di completamento

- [ ] Evidenze MyAstral.org raccolte.
- [ ] Evidenze Astrolab raccolte.
- [ ] Tutti gli step eseguiti.
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

## 17. Esito

**Stato finale:** DA COMPILARE

- [ ] Nessuna modifica necessaria.
- [ ] Affinamenti minori consigliati.
- [ ] Riorganizzazione parziale consigliata.
- [ ] Analisi tecnica separata necessaria.
- [ ] Evidenze insufficienti.
