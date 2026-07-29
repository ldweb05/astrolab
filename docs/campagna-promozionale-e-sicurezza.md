# AstroLab — Roadmap di Configurazione Privacy-First & Strategia di Lancio

Questo documento delinea il percorso tecnico e strategico per il deployment, la messa in sicurezza e il lancio organico di **AstroLab**, garantendo lo **stretto anonimato** del developer e dei consulenti coinvolti.

---

## FASE 1: Privacy e Identità Digitale (Settimana 1)

L'obiettivo di questa fase è scollegare completamente l'infrastruttura di AstroLab da qualsiasi identità reale, carta di credito o indirizzo IP personale.

### 1.1 Creazione Identità Operativa Isolata
- [ ] **Email Cifrata Isolata:**
  - Creare un account su un provider orientato alla privacy (es. ProtonMail, Tuta) tramite connessione VPN/Tor.
  - Non associare recapiti telefonici personali per il recupero dell'account.
- [ ] **Gestione dei Pagamenti Anonymous/Privacy-First:**
  - Impiegare carte prepagate usa-e-getta/virtuali non riconducibili (es. carte regalo virtuali, servizi di virtual carding) oppure **Cryptovalute (Bitcoin / Monero)** per l'acquisto di dominio e server.

### 1.2 Registrazione del Dominio (WHOIS Privacy Rigoroso)
- [ ] **Registrar Consigliati:**
  - **Njalla** (`njal.la`) o **Njalla/Ninjadomain**: Agisce come "prestanome" ufficiale del dominio; il registrar risulta proprietario legale e schermerà al 100% i tuoi dati.
  - Alternativa: **Porkbun** o **Namecheap** effettuando la registrazione tramite la mail cifrata e attivando la protezione **WHOIS Privacy/PrivacyGuard gratuita a vita**.
- [ ] **Scelta dell'Estensione (.com / .app / .io / .org):**
  - Prediligere un TLD neutro e professionale (es. `.app` o `.io` per evidenziare la matrice software, o `.com`).
- [ ] **Configurazione DNS:**
  - Far puntare i Nameserver (NS) direttamente a un gestore DNS esterno con protezione DDoS e mascheramento IP (es. Cloudflare gratuito, configurato sotto l'email anonima).

---

## FASE 2: Infrastruttura e Hosting Isolato (Settimana 1 - 2)

Per eseguire l'architettura scelta (**PHP 8.3 + PostgreSQL 16 + Swiss Ephemeris FFI + Docker**), serve un server VPS Linux (Ubuntu 22.04 LTS o 24.04 LTS) ad alte prestazioni.

### 2.1 Selezione del Provider Hosting Privacy-Friendly
- [ ] **Provider VPS Offshore/No-KYC:**
  - **Njalla VPS / FlokiNET / Cockbox / BitLaunch**: Permettono la registrazione e il pagamento diretto in Crypto o metodi anonimi senza verifica dell'identità (No-KYC).
  - Alternativa (Provider standard): **Hetzner** o **DigitalOcean** (pagati tramite carta virtuale/PayPal dedicato e intestati alla mail di progetto).
- [ ] **Specifiche Server Consigliate (Iniziali):**
  - CPU: 2 vCPU
  - RAM: 4 GB
  - Storage: 40–80 GB NVMe (veloce per le ricerche su PostgreSQL e librerie C)
  - OS: Ubuntu 24.04 LTS

### 2.2 Hardening e Messa in Sicurezza del Server
- [ ] **Protezione IP del Server:**
  - Mascherare l'IP sorgente del VPS dietro il proxy di **Cloudflare** (modalità *Proxy/Orange Cloud* attiva). L'IP reale del VPS non deve mai essere visibile nei record DNS pubblici.
- [ ] **Pulizia dei Metadati e Intestazioni HTTP:**
  - Rimuovere da NGINX/Apache e da PHP le intestazioni di versione (`X-Powered-By`, `Server`).
  - Disabilitare i log di accesso che salvano gli IP dei visitatori o anonimizzare gli indirizzi IP nei log (`127.0.0.1`).
- [ ] **Sicurezza Accesso:**
  - Disattivare l'accesso SSH tramite password; consentire solo chiavi SSH con coppia di chiavi dedicate.
  - Modificare la porta SSH standard (es. da 22 a 2222).
  - Configurare UFW (Uncomplicated Firewall) per accettare traffico solo sulle porte SSH personalizzata, HTTP (80) e HTTPS (443).

---

## FASE 3: Deployment e Configurazione dell'Applicazione (Settimana 2)

### 3.1 Ambiente Docker & Backend
- [ ] **Container Docker:**
  - Container PHP 8.3-FPM con modulo `FFI` abilitato (`ffi.enable=true` nel file `php.ini`).
  - Container PostgreSQL 16 isolato nella rete interna Docker (non esposto all'esterno).
  - Container NGINX come Web Server locale.
- [ ] **Libreria Swiss Ephemeris (`libswe`):**
  - Compilazione/Inclusione dei binari C di Swiss Ephemeris e verifica dei permessi di lettura dei file effemeridi (`.se1`).
- [ ] **Frontend & Asset:**
  - Verificare che il file `style.css` e i file JavaScript Vanilla non contengano riferimenti nei commenti, path o metadati ad ambienti di sviluppo personali, nome dell'autore o nickname passati.

### 3.2 Audit Metadati e Privacy del Codice
- [ ] Rimuovere ogni commento nel codice PHP/JS contenente nomi, iniziali o path locali (`C:\Users\NomeUtente\...`).
- [ ] Pulire i metadati EXIF da tutte le immagini/icone utilizzate nella WebApp.
- [ ] Verificare che nei report stampabili/PDF di 4-5 pagine figuri esclusivamente la dicitura neutra: *"Elaborato da AstroLab Engine v1.0 — Swiss Ephemeris Native Calculation"*.

---

## FASE 4: Test e Selezione dei Primo Gruppo di Tester (Settimana 3)

### 4.1 Testing Interno ed Esito Algoritmico
- [ ] Calcolo di controllo con casi limite storici di fuso orario e cambio ora legale.
- [ ] Verifica del ricalcolo dinamico su mappa Leaflet e scansione a griglia 0,5°.
- [ ] Controllo rigoroso dell'applicazione automatica delle 34 regole dell'Astrologia Attiva.

### 4.2 Inizio Reclutamento (Target: 30 Tester Qualificati)
- [ ] **Preparazione del Form Interno di Feedback:**
  - Creare un form di segnalazione direttamente nell'app (anonimo, senza richiesta di registrazione) con campi per: *RSM inserita*, *Valutazione precisione domificazione*, *Suggerimenti tecnici*.
- [ ] **Contatti Diretti Privati (LinkedIn / Email di settore):**
  - Invio riservato ad astrologi consulenti accreditati usando il format accademico ("Team di Sviluppo Indipendente").
- [ ] **Pubblicazione su Forum di Settore (Piattaforme Neutre):**
  - Presentazione del progetto nei thread dedicati al calcolo astrologico.

---

## FASE 5: Consolidamento e Roadmap Futura (Settimana 4+)

- [ ] Valutazione del feedback tecnico fornito dai primi 30 utenti qualificati.
- [ ] Ottimizzazione delle query di scansione della griglia geometrica a 0,5°.
- [ ] Valutazione dell'internazionalizzazione (Traduzione interfaccia in Spagnolo / Francese / Inglese).