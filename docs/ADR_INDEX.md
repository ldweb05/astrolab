# Astro-Val — ADR Index

Indice delle decisioni architetturali permanenti.

| ADR | Decisione | Stato | Versione |
|---|---|---|---|
| ADR-001 | Atlas come Single Source of Truth | Accettata | V4.1 |
| ADR-002 | Rule Engine alimentato solo da PlanetCondition | Da formalizzare | V4.2 |
| ADR-003 | Evidence Contract V4 retrocompatibile | Da formalizzare | V4.2 |
| ADR-004 | Generazione assistita delle Rule | Pianificata | V4.2 |
| ADR-005 | Validazione automatica delle Rule | Pianificata | V4.2 |

## Regola

Ogni nuova decisione architetturale significativa deve:

1. ricevere un identificativo ADR;
2. descrivere contesto, decisione e conseguenze;
3. essere registrata in questo indice;
4. aggiornare la timeline della roadmap.



-------------------------------------------------------------------------------

# DIRETTIVA OPERATIVA PERMANENTE (V4.2)

A partire dalla versione V4.2 l'architettura di Astro-Val è considerata
STABILE.

Non devono più essere introdotte modifiche architetturali,
refactoring generali o riprogettazioni del dominio, salvo la correzione
di bug documentati.

L'obiettivo esclusivo del progetto diventa il completamento della base
di conoscenza astrologica fino alla copertura totale dell'Atlas.

Le attività consentite sono esclusivamente:

- implementazione delle nuove Rule;
- implementazione delle Composite Rule previste;
- aggiunta di test automatici;
- esecuzione della Full Regression;
- aggiornamento della documentazione;
- commit Git.

Ogni attività completata DEVE essere registrata nel file:

docs/HANDOVER_OPERATIVO.md

L'Handover rappresenta il diario ufficiale del progetto.

Ogni sviluppatore dovrà poter riprendere il lavoro leggendo solamente:

1. HANDOVER_OPERATIVO.md
2. ROADMAP.md
3. KNOWLEDGE_COVERAGE.md
4. RULE_BACKLOG.md

Non è richiesto reinterpretare l'architettura.

È richiesto esclusivamente proseguire il completamento del progetto.

-------------------------------------------------------------------------------
