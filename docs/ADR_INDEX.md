# Astro-Val — ADR Index

Indice delle decisioni architetturali permanenti.

| ADR | Decisione | Stato | Versione |
|---|---|---|---|
| ADR-001 | Atlas come Single Source of Truth | Accettata | V4.1 |
| ADR-002 | Rule Engine alimentato solo da PlanetCondition | Da formalizzare | V4.2 |
| ADR-003 | Evidence Contract V4 retrocompatibile | Da formalizzare | V4.2 |
| ADR-004 | Generazione assistita delle Rule | Pianificata | V4.2 |
| ADR-005 | Validazione automatica delle Rule | Pianificata | V4.2 |
| ADR-012 | Freeze del Rule Engine dopo il completamento delle 120 Rule | Accettata | V5 |

## Regola

Ogni nuova decisione architetturale significativa deve:

1. ricevere un identificativo ADR;
2. descrivere contesto, decisione e conseguenze;
3. essere registrata in questo indice;
4. aggiornare la timeline della roadmap.



-------------------------------------------------------------------------------

# DIRETTIVA OPERATIVA PERMANENTE

L'architettura di Astro-Val e il Rule Engine sono considerati
**STABILI**.

Il Rule Engine è completo a 120 Rule e rimane in stato di **FREEZE**.

Non devono essere introdotte modifiche architetturali o modifiche al
dominio astrologico, salvo:

- bug documentati;
- incompatibilità tecniche;
- refactoring che non alterino il comportamento;
- decisioni esplicite formalizzate tramite ADR.

Le attività correnti possono riguardare:

- Evidence Engine;
- Theme Engine;
- Narrative Engine;
- Annual Report;
- PDF;
- UX/UI;
- frontend e CSS condiviso;
- test automatici;
- documentazione;
- preparazione delle release.

Ogni attività completata deve essere registrata cronologicamente in:

`docs/HANDOVER_OPERATIVO.md`

Per riprendere il lavoro leggere nell'ordine:

1. `START_HERE.md`;
2. `HANDOVER_OPERATIVO.md`;
3. `ROADMAP.md`;
4. `ADR_INDEX.md`.

Non è richiesto reinterpretare l'architettura.

Qualsiasi nuova decisione architetturale significativa deve essere
formalizzata tramite un nuovo ADR.

-------------------------------------------------------------------------------

-------------------------------------------------------------------------------

# ADR-012 — Freeze del Rule Engine

**Stato:** Accettata
**Versione:** V5

## Contesto

La milestone V4 ha completato il Rule Engine con:

- 120 Rule implementate;
- 120 Rule registrate;
- Knowledge Coverage 100%;
- Full Regression stabile.

Il commit di riferimento è:

`0bc53d0` — `feat(rule): implement RULE-0119 and RULE-0120 Pluto Houses 11-12`

## Decisione

Il Rule Engine entra ufficialmente in **FREEZE**.

Non devono essere:

- aggiunte nuove Rule;
- modificati i pesi;
- modificata la logica interpretativa;
- introdotti refactoring che alterino il comportamento.

Sono consentite modifiche esclusivamente in presenza di:

- bug documentati;
- incompatibilità tecniche;
- refactoring strettamente comportamentali e verificati dalla Full Regression.

Le evoluzioni successive devono avvenire esclusivamente nei livelli superiori:

- Evidence Engine;
- Theme Engine;
- Narrative Engine;
- Annual Report;
- PDF e UX.

## Conseguenze

La V5 consolida il Report Professionale senza riaprire il dominio delle 120 Rule.

Ogni intervento sul Rule Engine richiede una motivazione documentata, test specifici e Full Regression completa.
