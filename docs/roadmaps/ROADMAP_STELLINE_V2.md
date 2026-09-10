# Roadmap — Feature Stelline V2

**Data creazione:** 2026-08-25
**Ultimo aggiornamento:** 2026-08-26
**Branch:** new_dashboard
**Stato:** COMPLETATO (RS) — DA CLONARE (RL)

## Principi Inviolabili

1. Zero modifiche a file esistenti (RuleEngine.php e tutti gli altri restano intatti)
2. Un file alla volta, verifica sintassi prima del successivo
3. Patch via sed/python per ogni modifica
4. Commit dopo ogni step completato
5. Veti assoluti immutati — V2 è solo ranking qualitativo post-filtro
6. Pagina admin-only — nessun impatto sugli utenti attuali

## Architettura

### File Creati
1. www/includes/StellineV2Calculator.php — classe standalone calcolatrice
2. www/api/ricerca_stream_v2_api.php — endpoint streaming con integrazione V2
3. www/test_stelline_v2.php — pagina laboratorio stelline (admin-only)
4. Modifica minima www/includes/header_nav.php — link navbar admin-only

### Flusso Dati
test_stelline_v2.php → ricerca_stream_v2_api.php
    ├─► RuleEngine::valuta()              ← attuale (read-only)
    └─► StellineV2Calculator::calcola()   ← V2 (additivo)

## Tabella Pesi Definitiva (Aggiornata 2026-08-26)

| Configurazione                              | Stelle | Colore  |
|---------------------------------------------|--------|---------|
| Giove/Venere in casa condizione             | 4      | Verde   |
| Sole in casa condizione                     | 3      | Verde   |
| Luna/Mercurio in casa condizione            | 2      | Giallo  |
| Benefico in cuspide angolare (non cond.)    | 2      | Giallo  |
| Bistabile (GI/SO) in II/VII/VIII           | 2      | Arancio |
| Malefico in casa condizione/malus           | 2      | Rosso   |
| Stellium misto                              | ⚠️     | Giallo  |
| ASC in X                                    | 4      | Verde   |
| Venere in II/VII/VIII                       | 4      | Verde   |
| Malefico in III/IX                          | 0      | —       |

Malus sottrattivi: -2 ASC RS in casa natale VIII, -1 Malefico in VII. Floor totale a 0.

### Priorità Condizione
La condizione ha priorità assoluta: un pianeta in casa condizione viene valutato PRIMA per la condizione, non per la posizione angolare/bistabile.

### Case Tematiche Corrette
- Casa I = personalità/identità
- Casa IV = famiglia/radici
- Casa VII = relazioni/partner
- Casa X = carriera/status
- Casa II/VII/VIII = case bistabili (solo GI/SO)

### Filtro Risultati Irrilevanti
Risultati con 0 stelle e nessun contributo significativo vengono filtrati automaticamente.

## Legenda
Legenda stelline colorata visualizzata sotto la paginazione in tutte le viste (standard, griglia, cuspidi).

## Fasi Operative

### Fase 0: Preparazione [COMPLETATA]
- [x] Analisi RuleEngine.php e logica attuale
- [x] Studio 34 regole ufficiali
- [x] Definizione architettura con utente
- [x] Creazione roadmap

### Fase 1: Core Calculator [COMPLETATA]
- [x] Creare www/includes/StellineV2Calculator.php
- [x] Verifica sintassi
- [x] Allineamento valori stelle (Giove/Venere=4, Sole=3, Luna/Merc=2, cuspide=2)

### Fase 2: API Endpoint [COMPLETATA]
- [x] Integrare StellineV2Calculator in ricerca_stream_v2_api.php
- [x] Verifica sintassi
- [x] Test API

### Fase 3: Pagina Admin [COMPLETATA]
- [x] Creare www/test_stelline_v2.php
- [x] Colonne Lat/Lon aggiunte
- [x] Legenda stelline sotto paginazione
- [x] Margine legenda aggiornato
- [x] Link navbar admin-only
- [x] Riavvio container
- [x] Test funzionale reale nel browser

### Fase 4: Validazione e Documentazione [IN CORSO]
- [x] Confronto risultati V2 vs attuali su casi reali
- [x] Aggiornamento ROADMAP_STELLINE_V2.md
- [ ] Clone V2 per RL (test_stelline_rl_v2.php + API)
- [ ] Aggiornamento HANDOVER_OPERATIVO_astrolab.md

## Decisioni Chiave

| Data       | Decisione                              | Motivazione                                          |
|------------|----------------------------------------|------------------------------------------------------|
| 2026-08-25 | Zero touch file esistenti              | Continuità operativa garantita                       |
| 2026-08-25 | Logica additiva senza clamp            | Visualizzazione arcobaleno richiesta                 |
| 2026-08-25 | Venere mai bistabile                   | Solo GI/SO sono bistabili (Regola 8/9)               |
| 2026-08-25 | Numeri celesti eliminati               | Sovrappiù                                            |
| 2026-08-25 | Stellium = entità unica                | Richiesta esplicita                                  |
| 2026-08-26 | Priorità condizione su posizione       | Condizione è il criterio primario di valutazione     |
| 2026-08-26 | Giove/Venere cond. = 4★ (non 5)       | Allineamento con tabella pesi definitiva             |
| 2026-08-26 | Sole cond. = 3★                        | Gerarchia: benefico > sole > luna/merc               |
| 2026-08-26 | Luna/Merc cond. = 2★                   | Ricalibrazione da 1 a 2                              |
| 2026-08-26 | Cuspide angolare = 2★                  | Separata da condizione, peso minore                  |
| 2026-08-26 | Case tematiche corrette                | I=personalità, IV=famiglia, VII=relazioni, X=carriera|
| 2026-08-26 | Filtro risultati irrilevanti           | Nascondere righe con 0 contributo                    |
| 2026-08-26 | Legenda stelline sotto paginazione     | Visibilità immediata dei criteri di valutazione      |
| 2026-08-26 | Commit dopo ogni modifica              | Tracciabilità e rollback sicuro                      |
