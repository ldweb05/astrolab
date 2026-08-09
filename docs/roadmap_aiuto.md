# Roadmap: Menu Aiuto e Manuale d'Uso ASTROLAB

Questo documento traccia la progettazione, l'organizzazione e lo sviluppo del menu "Aiuto" e del manuale d'uso integrato nell'applicazione ASTROLAB.

## Obiettivo
Fornire agli utenti un manuale d'uso strutturato, accessibile direttamente dall'interfaccia tramite un menu "Aiuto", che spieghi le funzionalità della piattaforma senza duplicare la documentazione tecnica o architetturale.

## Struttura proposta del Menu Aiuto

### 1. Introduzione e Account
- Benvenuto in ASTROLAB
- Piani di abbonamento (Free e Supporter) e quote
- Gestione del profilo, password e sicurezza sessioni

### 2. Gestione Soggetti
- Inserimento dati di nascita e modifica dei soggetti
- Gestione della lista soggetti e limiti per piano

### 3. Calcoli e Analisi Principali
- Tema Natale
- Rivoluzione Solare
- Rivoluzione Lunare
- Rilocazioni

### 4. Ricerca Geografica Avanzata
- Ricerca RSM v3 (Aeroporti e Località mondiali)
- Ricerca "Astri nelle Case"

### 5. Report, Narrazione e Stampa
- Come leggere l'Annual Report
- Theme Engine e Narrative Engine
- Esportazione e Stampa PDF

### 6. Comparatore e Supporto Decisionale
- Il Comparator (confronto multiplo RS e Rilocazioni)
- Il Decision Support System (DSS)
- Comprendere le Rule e le Evidenze

### 7. Interfaccia e Visualizzazione
- La Ruota Zodiacale e la codifica colore (Pianeti diretti, retrogradi, in cuspide)
- Navigazione globale, menu e responsive design

### 8. FAQ e Limiti Operativi
- Ricerche salvate e limiti di utilizzo
- Domande frequenti e risoluzione problemi

## Fasi di Sviluppo

- [x] Fase 1: Definizione della struttura e dei titoli dei capitoli
- [x] Fase 2: Infrastruttura menu Aiuto (dropdown 8 voci + modale contenuti per sezione)
- [~] Fase 3: Redazione contenuti testuali (Sezione 1 completata in help_account.php, Sezioni 2-8 placeholder)
- [ ] Fase 4: Redazione dei contenuti testuali per le sezioni 3, 4 e 5
- [ ] Fase 5: Redazione dei contenuti testuali per le sezioni 6, 7 e 8
- [ ] Fase 6: Integrazione finale, test UX e collegamento alla documentazione principale

## Mappatura Voci Manuale -> Pagine Applicazione

Sulla base dell'analisi di `www/includes/header_nav.php` e dei file PHP presenti, ecco la mappatura proposta per i link contestuali del manuale:

| Sezione Manuale | Pagina Applicazione Principale |
|---|---|
| 1. Introduzione e Account | `login.php`, `registrazione.php`, `cambia_password.php` |
| 2. Gestione Soggetti | `index.php` (Lista Soggetti) |
| 3. Calcoli e Analisi: Tema Natale | `tema.php` |
| 3. Calcoli e Analisi: Rivoluzione Solare | `rs.php` |
| 3. Calcoli e Analisi: Rivoluzione Lunare | `rl.php` |
| 3. Calcoli e Analisi: Rilocazioni | `rilocazione.php` |
| 4. Ricerca Geografica Avanzata | `ricerca.php` |
| 5. Report, Narrazione e Stampa | `stampa.php`, API PDF |
| 6. Comparatore | `compare_rs.php`, `compare_ril.php` |

## Strategia di Implementazione Interfaccia (Prossimo Passo)

Per integrare il menu "Aiuto" nell'interfaccia, le opzioni valutate sono:
1. **Modale/Popup JS**: Un'icona "?" o la voce "Aiuto" nel menu apre un modale che carica dinamicamente i contenuti testuali (mantenendo l'utente sulla pagina corrente e fornendo aiuto contestuale).
2. **Pagina Dedicata**: Una pagina statica `help.php` o `manuale.php` raggiungibile dal menu.

**Scelta consigliata**: Modale/Popup contestuale, per non interrompere il workflow dell'astrologo. I contenuti testuali potranno essere gestiti come stringhe PHP o piccoli file JSON caricati via API.
