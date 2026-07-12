# ===================================================================
# Astro-Val Documentation
# Document : 05_NARRATIVE.md
# Version  : 2.0
# Status   : Authoritative
# Part     : 1 / 5
# ===================================================================

# 1. Scopo

Il Narrative Engine rappresenta l'ultimo livello intelligente del sistema Astro-Val.

Il suo compito non consiste nello scrivere testo.

Il suo compito consiste nel trasformare i temi astrologici validati dal dominio in una relazione professionale destinata al consultante.

La narrativa rappresenta quindi un livello di comunicazione.

Non un livello interpretativo.

L'interpretazione termina nel Theme Engine.

La narrativa inizia dal Theme Engine.

---

# REQ-NAR-001

Il Narrative Engine non deve mai produrre nuove interpretazioni astrologiche.

---

# REQ-NAR-002

Il Narrative Engine comunica esclusivamente significati già validati.

---

# 2. Obiettivo della relazione

La relazione deve rispondere ad una sola domanda.

"Cosa rappresenta simbolicamente il mio prossimo anno?"

Non deve spiegare:

- tutti gli aspetti

- tutte le dignità

- tutte le Case

- tutti i pianeti

Questi appartengono al dominio astrologico.

Il consultante desidera comprendere il significato complessivo dell'anno.

---

# 3. Ruolo della relazione

La relazione non conclude il lavoro dell'astrologo.

Lo prepara.

Essa costituisce il punto di partenza del colloquio.

Per questo motivo deve:

- essere sintetica;

- essere chiara;

- essere leggibile;

- stimolare domande;

- favorire il dialogo.

---

# REQ-NAR-003

La relazione deve preparare il consultante al colloquio.

Mai sostituirlo.

---

# 4. Lunghezza

La relazione deve poter essere letta comodamente.

L'obiettivo è:

2–3 pagine A4.

Tempo medio di lettura:

10–15 minuti.

Una relazione troppo lunga riduce l'efficacia del consulto.

Una relazione troppo breve non offre sufficiente valore.

---

# REQ-NAR-004

La lunghezza della relazione deve rimanere entro il limite previsto salvo esplicita richiesta dell'astrologo.

---

# 5. Struttura narrativa

La relazione seguirà sempre questa struttura.

Titolo

↓

Premessa

↓

Il significato dell'anno

↓

Temi principali

↓

Dinamiche trasversali

↓

Opportunità

↓

Aree di attenzione

↓

Conclusione

Questa struttura costituisce uno standard.

---

# REQ-NAR-005

L'ordine delle sezioni non deve essere modificato senza revisione architetturale.

---

# 6. Il titolo

Il titolo deve essere semplice.

Esempio.

"La tua Rivoluzione Solare 2027"

oppure

"Rivoluzione Solare 2027"

Il titolo non deve contenere giudizi.

Non deve anticipare l'interpretazione.

---

# REQ-NAR-006

Il titolo deve essere neutro.

---

# 7. Premessa

Ogni relazione dovrà iniziare ricordando al consultante la natura simbolica dell'interpretazione.

Esempio.

"Le considerazioni contenute in questa relazione rappresentano una lettura simbolica della tua Rivoluzione Solare secondo i principi dell'Astrologia Attiva. Esse descrivono tendenze, aree di maggiore attenzione e possibili opportunità, senza costituire previsioni certe."

Questa introduzione protegge sia il consultante sia il professionista.

---

# REQ-NAR-007

Ogni relazione deve contenere una premessa metodologica.

---

# 8. Il significato dell'anno

Questo costituisce il capitolo più importante.

Dopo aver letto questa sezione il consultante deve aver compreso:

"Che tipo di anno sto vivendo?"

Non dovrà ancora conoscere tutti i dettagli.

Dovrà aver compreso il clima generale.

Esempio.

"Questa Rivoluzione Solare sembrerebbe orientare l'anno verso un periodo di consolidamento e costruzione. Le configurazioni principali suggeriscono una maggiore attenzione alle responsabilità personali e professionali, accompagnata dalla possibilità di rafforzare quanto costruito negli anni precedenti."

Questa sezione deve essere completamente discorsiva.

Mai tecnica.

Mai astrologica.

---

# REQ-NAR-008

Il capitolo "Il significato dell'anno" deve essere scritto senza utilizzare terminologia astrologica specialistica.

# ===================================================================
# Astro-Val Documentation
# Document : 05_NARRATIVE.md
# Version  : 2.0
# Status   : Authoritative
# Part     : 2 / 5
# ===================================================================

# 9. I temi principali

Terminata l'introduzione generale, la relazione dovrà approfondire le aree della vita che il Theme Engine avrà identificato come dominanti.

Normalmente saranno da tre a cinque.

Ad esempio.

- Realizzazione professionale
- Relazioni
- Benessere
- Economia
- Famiglia

L'ordine dovrà essere determinato esclusivamente dalla priorità calcolata dal Theme Engine.

Mai da un ordine fisso.

---

# REQ-NAR-009

I temi devono essere presentati in ordine decrescente di priorità.

---

# 10. Come sviluppare un tema

Ogni tema dovrà seguire sempre la medesima struttura narrativa.

Introduzione

↓

Significato simbolico

↓

Possibili manifestazioni

↓

Fattori di protezione

↓

Aree di attenzione

↓

Collegamento con gli altri temi

In questo modo tutte le relazioni manterranno uno stile uniforme.

---

# REQ-NAR-010

Ogni tema deve mantenere la medesima struttura narrativa.

---

# 11. Introduzione del tema

Il primo paragrafo deve spiegare perché quel tema è importante.

Esempio.

"L'ambito professionale emerge come uno dei punti centrali della tua Rivoluzione Solare. Diverse configurazioni sembrerebbero convergere verso quest'area, suggerendo che molte delle energie dell'anno potrebbero concentrarsi sulla realizzazione personale, sulle responsabilità e sulla definizione del proprio ruolo."

Il consultante deve capire immediatamente il peso di quel tema.

---

# REQ-NAR-011

Il primo paragrafo deve descrivere l'importanza del tema, non i dettagli tecnici.

---

# 12. Significato simbolico

Successivamente il testo dovrà spiegare il significato generale.

Non gli eventi.

Ad esempio.

"Le configurazioni presenti sembrerebbero favorire un periodo nel quale il lavoro potrebbe assumere un ruolo particolarmente significativo. Più che promettere risultati immediati, l'anno sembrerebbe invitare a consolidare quanto costruito nel tempo."

Questa parte descrive il clima.

Non gli episodi.

---

# REQ-NAR-012

La narrativa deve descrivere il clima simbolico del tema.

---

# 13. Possibili manifestazioni

Solo successivamente si potranno descrivere alcune possibili manifestazioni.

È importante utilizzare il plurale.

Ad esempio.

"Tale configurazione potrebbe manifestarsi attraverso nuove responsabilità, cambiamenti organizzativi, opportunità di crescita oppure una diversa percezione del proprio ruolo professionale."

Questo evita interpretazioni eccessivamente rigide.

---

# REQ-NAR-013

Quando esistono più manifestazioni possibili il software deve citarne diverse.

Mai una sola.

---

# 14. Fattori di protezione

Ogni tema dovrà evidenziare le risorse disponibili.

Non soltanto le criticità.

Ad esempio.

"La presenza di elementi favorevoli sembrerebbe offrire una buona capacità di affrontare eventuali difficoltà, facilitando l'individuazione di soluzioni pratiche o il sostegno da parte di persone significative."

Questa è la traduzione narrativa del Principio della Pensilina.

---

# REQ-NAR-014

Ogni tema deve evidenziare sia le risorse sia le aree di attenzione.

---

# 15. Aree di attenzione

Le criticità non dovranno mai essere espresse in modo allarmistico.

Esempio scorretto.

"Quest'anno perderai il lavoro."

Esempio corretto.

"Alcune configurazioni suggeriscono che l'ambito professionale potrebbe richiedere maggiore flessibilità e capacità di adattamento, soprattutto qualora si rendesse necessario affrontare cambiamenti organizzativi."

L'obiettivo è informare.

Mai spaventare.

---

# REQ-NAR-015

Il linguaggio allarmistico è vietato.

---

# 16. Collegamenti tra temi

Una buona relazione non descrive compartimenti separati.

I temi devono dialogare.

Ad esempio.

"Le responsabilità professionali potrebbero influenzare anche la sfera familiare, rendendo particolarmente importante la ricerca di un equilibrio tra gli impegni e la vita privata."

Questo rende la relazione naturale.

---

# REQ-NAR-016

Ogni tema dovrebbe richiamare almeno un altro tema quando esiste una relazione significativa.

---

# 17. Le dinamiche trasversali

Terminata la descrizione dei singoli temi, la relazione dovrà presentare una sezione dedicata alle interazioni.

Qui il software descriverà il quadro complessivo.

Esempio.

"Osservando nel loro insieme le configurazioni della Rivoluzione Solare emerge un filo conduttore: molte delle scelte professionali potrebbero riflettersi direttamente anche sulla vita affettiva e familiare, rendendo importante mantenere una visione equilibrata delle diverse priorità."

Questa sezione rappresenta uno degli elementi che distinguono una relazione professionale da un semplice elenco di interpretazioni.

---

# REQ-NAR-017

La relazione deve contenere una sintesi trasversale dei temi principali.

# ===================================================================
# Astro-Val Documentation
# Document : 05_NARRATIVE.md
# Version  : 2.0
# Status   : Authoritative
# Part     : 3 / 5
# ===================================================================

# 18. Opportunità

Dopo aver descritto il quadro generale, la relazione dovrà dedicare una sezione specifica alle opportunità che sembrano emergere dalla Rivoluzione Solare.

Questa sezione non deve essere costruita come un elenco di "fortune".

Deve descrivere gli ambiti nei quali il consultante potrebbe trovare condizioni favorevoli per sviluppare il proprio percorso.

Esempio.

"Le configurazioni dell'anno sembrerebbero favorire una maggiore disponibilità di risorse interiori per affrontare cambiamenti importanti. Qualora si presentassero occasioni coerenti con il percorso personale già intrapreso, potrebbe risultare più semplice consolidare quanto costruito negli anni precedenti."

L'accento deve essere posto sulla possibilità.

Mai sulla certezza.

---

# REQ-NAR-018

Le opportunità devono essere presentate come potenzialità da riconoscere e sviluppare.

Mai come eventi garantiti.

---

# 19. Aree che richiedono attenzione

Questa sezione rappresenta probabilmente la più delicata dell'intera relazione.

Lo scopo non è individuare pericoli.

Lo scopo è aumentare la consapevolezza del consultante.

Il tono deve rimanere sempre professionale, equilibrato e rispettoso.

Esempio.

"Alcune configurazioni sembrerebbero suggerire l'opportunità di dedicare particolare attenzione alla gestione delle energie personali. Periodi di intenso impegno potrebbero richiedere una migliore organizzazione del tempo e delle priorità."

Il consultante deve uscire dalla lettura sentendosi più preparato.

Mai spaventato.

---

# REQ-NAR-019

Le aree di attenzione devono sempre essere accompagnate da possibili strategie di gestione.

---

# 20. Il principio della responsabilizzazione

Una buona relazione non dice al consultante cosa accadrà.

Lo aiuta a comprendere come affrontare l'anno.

L'obiettivo non è creare dipendenza dall'astrologo.

È favorire maggiore consapevolezza.

Per questo motivo la narrativa dovrà privilegiare formulazioni orientate all'azione.

Ad esempio.

"Potrebbe risultare utile..."

"Potrebbe essere opportuno..."

"Potrebbe rivelarsi importante..."

"Potrebbe favorire..."

Queste espressioni stimolano una partecipazione attiva del consultante.

---

# REQ-NAR-020

La relazione deve favorire un atteggiamento attivo.

Mai passivo.

---

# 21. Linguaggio

Il linguaggio rappresenta uno degli elementi distintivi di Astro-Val.

Deve essere:

- naturale;
- elegante;
- scorrevole;
- professionale;
- accessibile.

Il consultante non deve percepire di leggere un testo tecnico.

Deve percepire di leggere una relazione scritta da un professionista.

---

# REQ-NAR-021

Il linguaggio tecnico deve essere limitato allo stretto necessario.

---

# 22. Terminologia astrologica

Salvo casi particolari, la relazione non dovrebbe utilizzare termini come:

- trigono;
- quadratura;
- domicilio;
- esaltazione;
- rivoluzione solare mirata;
- Gauquelin;
- retrogradazione;
- cuspide.

Queste informazioni appartengono al colloquio con l'astrologo.

Non alla relazione.

Quando necessario, esse dovranno essere tradotte in linguaggio naturale.

---

# REQ-NAR-022

La narrativa deve tradurre il simbolismo astrologico in linguaggio comprensibile.

---

# 23. Coerenza stilistica

Ogni paragrafo deve sembrare scritto dalla stessa persona.

Non devono esistere differenze di stile tra un tema e l'altro.

La relazione deve avere un ritmo uniforme.

Il lettore non deve percepire che ogni sezione deriva da un modulo software differente.

---

# REQ-NAR-023

L'intera relazione deve mantenere uno stile narrativo uniforme.

---

# 24. Ripetizioni

Le ripetizioni costituiscono uno dei principali rischi della generazione automatica.

Il Narrative Engine dovrà evitarle.

In particolare dovrà limitare la ripetizione di espressioni come:

- potrebbe;
- sembrerebbe;
- appare;
- emerge;
- configurazione.

Potranno essere utilizzati sinonimi e riformulazioni.

L'obiettivo è ottenere un testo naturale.

---

# REQ-NAR-024

Le ripetizioni lessicali devono essere ridotte al minimo.

---

# 25. Lunghezza dei paragrafi

I paragrafi non devono essere eccessivamente lunghi.

Indicativamente:

3–6 frasi.

Questo migliora la leggibilità e favorisce una migliore comprensione.

---

# REQ-NAR-025

I paragrafi devono mantenere una lunghezza equilibrata.

---

# 26. Transizioni

Ogni sezione deve collegarsi naturalmente alla successiva.

Esempio.

"Dopo aver considerato gli aspetti professionali, è interessante osservare come tali dinamiche possano riflettersi anche sulla sfera delle relazioni."

Le transizioni rendono la relazione fluida.

---

# REQ-NAR-026

Tra due capitoli consecutivi deve essere presente almeno un elemento di continuità narrativa.

# ===================================================================
# Astro-Val Documentation
# Document : 05_NARRATIVE.md
# Version  : 2.0
# Status   : Authoritative
# Part     : 4 / 5
# ===================================================================

# 27. Il principio del condizionale

Il condizionale rappresenta uno dei pilastri della narrativa Astro-Val.

Non costituisce semplicemente una scelta stilistica.

È una scelta:

- astrologica;
- metodologica;
- etica;
- professionale.

L'astrologia descrive configurazioni simboliche.

Non eventi certi.

Per questo motivo il software dovrà utilizzare sistematicamente forme linguistiche probabilistiche.

Ad esempio.

Preferire:

- potrebbe
- sembrerebbe
- lascerebbe ipotizzare
- appare possibile
- potrebbe favorire
- potrebbe richiedere
- potrebbe rappresentare

Evitare:

- accadrà
- sicuramente
- certamente
- avrai
- perderai
- troverai
- subirai

---

# REQ-NAR-027

Ogni riferimento al futuro deve essere espresso mediante linguaggio probabilistico.

---

# 28. Il caso della salute

La salute rappresenta probabilmente il tema più delicato dell'intera relazione.

Per questo motivo il Narrative Engine dovrà utilizzare la massima prudenza.

Caso reale.

Una Rivoluzione Solare potrebbe presentare una forte protezione simbolica grazie alla presenza di Giove e Venere.

Durante l'anno il consultante potrebbe tuttavia ricevere una diagnosi di tumore.

Questa situazione non contraddice necessariamente il significato astrologico.

La protezione potrebbe infatti manifestarsi attraverso:

- una diagnosi estremamente precoce;
- l'individuazione dello specialista corretto;
- una terapia particolarmente efficace;
- un recupero completo;
- conseguenze molto più limitate rispetto a quanto sarebbe potuto accadere.

Questo esempio rappresenta perfettamente il Principio della Pensilina.

Il software non dovrà quindi scrivere:

"La salute sarà ottima."

Dovrà invece scrivere:

"Le configurazioni dell'anno sembrerebbero offrire una buona capacità di affrontare eventuali situazioni legate al benessere. Qualora dovessero presentarsi circostanze che richiedano attenzione, potrebbero essere disponibili risorse utili per gestirle con efficacia."

Questa formulazione rimane corretta in entrambe le situazioni.

---

# REQ-NAR-028

Le sezioni dedicate alla salute non devono mai promettere assenza di malattie.

---

# REQ-NAR-029

Le configurazioni favorevoli devono essere tradotte come possibili fattori di protezione.

---

# 29. Il caso del lavoro

Anche l'ambito professionale richiede particolare attenzione.

Una configurazione favorevole non garantisce:

- promozioni;
- aumenti;
- nuovi impieghi.

Può invece favorire:

- consolidamento;
- crescita;
- riconoscimento;
- occasioni;
- maggiore autorevolezza.

Esempio scorretto.

"Quest'anno otterrai una promozione."

Esempio corretto.

"L'ambito professionale potrebbe rappresentare uno dei principali terreni di sviluppo dell'anno. Qualora si presentassero occasioni coerenti con il percorso già intrapreso, potrebbero favorire un consolidamento del ruolo o nuove responsabilità."

---

# REQ-NAR-030

La narrativa professionale deve descrivere opportunità.

Mai risultati garantiti.

---

# 30. Il caso delle relazioni

Le relazioni costituiscono un'altra area particolarmente sensibile.

Il software non dovrà mai formulare affermazioni come:

"Troverai l'amore."

Oppure.

"Il matrimonio finirà."

Entrambe rappresentano affermazioni incompatibili con la filosofia di Astro-Val.

Esempio corretto.

"La vita relazionale potrebbe assumere un'importanza maggiore rispetto agli anni precedenti, favorendo nuove occasioni di incontro oppure una diversa evoluzione dei rapporti già esistenti."

---

# REQ-NAR-031

La narrativa relazionale deve descrivere dinamiche.

Mai eventi certi.

---

# 31. Il ruolo dell'astrologo

La relazione non rappresenta il prodotto finale.

Rappresenta l'inizio del colloquio.

L'astrologo potrà:

- approfondire;
- contestualizzare;
- personalizzare;
- chiarire.

Il software non deve sostituire questa fase.

Al contrario.

Deve valorizzarla.

---

# REQ-NAR-032

La relazione deve lasciare spazio all'approfondimento professionale dell'astrologo.

---

# 32. Cosa NON deve fare il Narrative Engine

Il Narrative Engine non deve:

- fare diagnosi;
- dare consigli medici;
- formulare sentenze;
- spaventare il consultante;
- creare aspettative irrealistiche;
- utilizzare toni sensazionalistici;
- enfatizzare inutilmente gli aspetti negativi;
- promettere risultati.

Questi comportamenti sono incompatibili con Astro-Val.

---

# REQ-NAR-033

Qualsiasi formulazione deterministica deve essere considerata un errore di generazione narrativa.

---

# 33. Il lettore ideale

Durante la scrittura il Narrative Engine dovrà immaginare un consultante che:

- non conosce l'astrologia;
- desidera comprendere il proprio anno;
- si affida ad un professionista;
- cerca chiarezza e non spettacolarizzazione.

Ogni frase dovrà essere scritta pensando a questo lettore.

---

# REQ-NAR-034

La relazione deve risultare comprensibile anche a chi non possiede alcuna conoscenza astrologica.

# ===================================================================
# Astro-Val Documentation
# Document : 05_NARRATIVE.md
# Version  : 2.0
# Status   : Authoritative
# Part     : 5 / 5
# ===================================================================

# 34. La conclusione della relazione

La conclusione rappresenta l'ultima impressione che il consultante conserverà della lettura.

Per questo motivo non deve limitarsi a riassumere quanto già detto.

Deve invece restituire una visione d'insieme dell'anno.

L'obiettivo è permettere al consultante di chiudere la lettura con una maggiore consapevolezza del proprio percorso.

Una buona conclusione:

- richiama il tema dominante;
- sintetizza le principali dinamiche;
- ricorda le risorse disponibili;
- invita ad affrontare l'anno con partecipazione attiva.

---

# REQ-NAR-035

La conclusione deve rappresentare una sintesi interpretativa dell'intera relazione.

---

# 35. Lo stile complessivo

La relazione deve trasmettere la sensazione di essere stata scritta personalmente da un astrologo esperto.

Il lettore non deve mai percepire:

- ripetizioni meccaniche;
- frasi stereotipate;
- paragrafi scollegati;
- testo generato automaticamente.

Il ritmo della lettura deve essere naturale.

Ogni paragrafo deve preparare quello successivo.

Il linguaggio deve risultare autorevole ma mai distante.

---

# REQ-NAR-036

La qualità stilistica costituisce parte integrante della qualità del software.

---

# 36. Coerenza interna

La relazione deve mantenere coerenza dall'inizio alla fine.

Se il significato generale dell'anno è quello di consolidamento, ogni sezione dovrà essere coerente con questa idea.

Se invece emerge un anno di trasformazione, anche le opportunità e le aree di attenzione dovranno riflettere questa dinamica.

La relazione non deve mai apparire come una semplice somma di paragrafi indipendenti.

---

# REQ-NAR-037

Ogni capitolo deve contribuire a costruire una narrazione unica e coerente.

---

# 37. Priorità dei temi

Non tutti i temi meritano lo stesso spazio.

Il Theme Engine assegna una priorità.

Il Narrative Engine dovrà utilizzarla per determinare:

- ordine dei capitoli;
- lunghezza dei paragrafi;
- livello di approfondimento.

I temi secondari non dovranno mai oscurare quelli principali.

---

# REQ-NAR-038

La lunghezza della trattazione deve essere proporzionale alla priorità del tema.

---

# 38. Gestione delle contraddizioni apparenti

La vita reale è complessa.

È possibile che la Rivoluzione Solare presenti contemporaneamente simboli di protezione e simboli di sfida nello stesso ambito.

Il Narrative Engine non dovrà scegliere arbitrariamente uno dei due.

Dovrà integrarli.

Ad esempio.

"L'area professionale sembrerebbe assumere un'importanza particolare durante quest'anno. Accanto a possibili opportunità di crescita potrebbero emergere responsabilità più impegnative, richiedendo una gestione equilibrata delle energie disponibili."

Questa formulazione descrive il simbolo nella sua completezza.

---

# REQ-NAR-039

Le polarità opposte devono essere integrate in un'unica interpretazione coerente.

---

# 39. Il principio della dignità del consultante

Ogni relazione deve rispettare profondamente la persona che la leggerà.

Il software non deve mai:

- giudicare;
- colpevolizzare;
- etichettare;
- creare dipendenza;
- alimentare paure.

L'obiettivo è accompagnare il consultante verso una maggiore comprensione di sé.

Non impressionarlo.

---

# REQ-NAR-040

La narrativa deve sempre preservare la dignità e l'autonomia del consultante.

---

# 40. Obiettivo finale del Narrative Engine

Il successo della relazione non si misura dal numero di parole.

Non si misura nemmeno dalla quantità di informazioni astrologiche contenute.

Una relazione è riuscita quando, terminata la lettura, il consultante può dire:

- "Ho capito quale potrebbe essere il significato del mio anno."

- "Mi sento più preparato ad affrontarlo."

- "Ho voglia di approfondire questi temi con il mio astrologo."

Se questi tre obiettivi vengono raggiunti, il Narrative Engine ha assolto correttamente la propria funzione.

---

# REQ-NAR-041

La relazione deve aumentare la consapevolezza del consultante.

Mai sostituire la sua libertà di scelta.

---

# 41. Principi fondamentali del Narrative Engine

L'intero motore narrativo di Astro-Val si fonda sui seguenti principi.

• Centralità del consultante.

• Linguaggio probabilistico.

• Principio della Pensilina.

• Uso sistematico del condizionale.

• Assenza di determinismo.

• Traduzione del simbolismo astrologico in linguaggio naturale.

• Coerenza narrativa.

• Eleganza stilistica.

• Prudenza interpretativa.

• Valorizzazione del colloquio con l'astrologo.

Ogni futura evoluzione del Narrative Engine dovrà rispettare integralmente questi principi.

---

# Stato del documento

Versione: 2.0

Status: Authoritative

Documento normativo.

Qualsiasi implementazione del Narrative Engine in contrasto con il presente documento dovrà essere modificata.

# ===================================================================


---

# Evoluzione del Narrative Engine

La crescita del Narrative Engine dovrà essere registrata nella roadmap
del progetto per mantenere la tracciabilità dell'evoluzione narrativa.


# FINE DOCUMENTO
# ===================================================================

