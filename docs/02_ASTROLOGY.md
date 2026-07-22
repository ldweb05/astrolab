# ===================================================================
# Astro-Val Documentation
# Document : 02_ASTROLOGY.md
# Version  : 2.0
# Status   : Authoritative
# Part     : 1 / 4
# ===================================================================

# 2. ASTROLOGICAL MODEL

---

# 1. Scopo del documento

Questo documento definisce il modello astrologico adottato da Astro-Val.

Non rappresenta un trattato di astrologia generale.

Rappresenta invece la formalizzazione delle regole che il software deve implementare per interpretare una Rivoluzione Solare secondo i principi dell'Astrologia Attiva della scuola di Ciro Discepolo.

Ogni componente software che produce interpretazioni astrologiche dovrà essere coerente con quanto definito in questo documento.

Nel caso in cui il comportamento del software sia in contrasto con questo documento, il software dovrà essere modificato.

Questo documento rappresenta quindi la fonte normativa dell'intero dominio astrologico.

---

# 2. Obiettivo dell'interpretazione

Lo scopo dell'interpretazione non è prevedere il futuro.

Lo scopo è comprendere il significato simbolico delle configurazioni della Rivoluzione Solare.

La relazione finale dovrà aiutare il consultante a comprendere:

- quali aree della vita saranno maggiormente sollecitate;
- quali energie sembrano predominare;
- quali ambiti potrebbero richiedere maggiore attenzione;
- quali risorse simboliche risultano disponibili;
- come affrontare l'anno con maggiore consapevolezza.

Il software non deve mai trasformarsi in uno strumento deterministico.

---

# 3. Filosofia dell'Astrologia Attiva

Astro-Val adotta esclusivamente il modello interpretativo dell'Astrologia Attiva.

In tale modello la Rivoluzione Solare non elimina gli eventi della vita.

Può tuttavia modificare profondamente il modo nel quale essi vengono vissuti.

Questo principio rappresenta uno dei fondamenti dell'intero progetto.

Il software dovrà quindi interpretare ogni configurazione come una modifica della qualità dell'esperienza, non come una previsione assoluta dell'evento.

---

# 4. Il Principio della Pensilina

Uno dei principi fondamentali dell'intera architettura interpretativa deriva dalla metafora utilizzata da Ciro Discepolo.

Una buona Rivoluzione Solare non fa smettere di piovere.

Costruisce una pensilina.

Questa immagine deve guidare ogni futura evoluzione del software.

Dal punto di vista operativo significa che:

una configurazione favorevole

NON implica

l'assenza di eventi difficili.

Può invece indicare:

- maggiore capacità di affrontare la situazione;
- interventi tempestivi;
- diagnosi precoci;
- incontri favorevoli;
- persone disponibili ad aiutare;
- riduzione delle conseguenze;
- maggiore resilienza;
- recupero più rapido.

Il software dovrà sempre privilegiare questa interpretazione.

---

# REQ-AST-001

Una configurazione favorevole non deve mai essere descritta come garanzia dell'assenza di problemi.

---

# REQ-AST-002

Le configurazioni favorevoli devono essere interpretate come possibili fattori di protezione, facilitazione o attenuazione.

---

# REQ-AST-003

Ogni narrativa che descriva una protezione dovrà utilizzare un linguaggio probabilistico.

---

# 5. Il principio della probabilità

L'astrologia lavora su simboli.

La vita reale dipende da un numero enorme di variabili.

Di conseguenza il software non può trasformare un simbolo astrologico in una previsione certa.

La narrativa dovrà utilizzare sistematicamente espressioni quali:

- potrebbe
- sembrerebbe
- lascerebbe ipotizzare
- è possibile che
- potrebbe favorire
- potrebbe richiedere
- potrebbe indicare

---

# REQ-AST-004

Le forme verbali deterministiche sono vietate.

---

# REQ-AST-005

Ogni previsione dovrà essere formulata come possibilità e mai come certezza.

---

# 6. Simbolo ed evento

Questo costituisce probabilmente il punto più importante dell'intero progetto.

Un simbolo astrologico non coincide con un evento.

Ad esempio:

Marte in VI Casa

non significa automaticamente

malattia.

Può rappresentare:

- intensa attività lavorativa;
- maggiore stress;
- attività sportiva;
- intervento chirurgico;
- conflittualità nell'ambiente di lavoro;
- necessità di utilizzare molte energie;
- maggiore combattività.

L'evento concreto dipenderà dal contesto personale del consultante.

Il software non deve scegliere arbitrariamente una sola manifestazione possibile.

Dovrà invece descrivere il significato simbolico della configurazione.

---

# REQ-AST-006

Il motore non deve mai trasformare direttamente un simbolo astrologico in un evento reale.

---

# REQ-AST-007

La narrativa dovrà sempre mantenere aperte le possibili manifestazioni del simbolo.

---

# 7. Il principio del contesto

Nessun pianeta deve essere interpretato isolatamente.

Ogni configurazione assume significato esclusivamente all'interno dell'intero tema della Rivoluzione Solare.

Ad esempio:

Giove in X Casa

potrebbe suggerire:

- crescita professionale;
- maggiore riconoscimento;
- protezione della posizione lavorativa.

Tuttavia la presenza contemporanea di altre configurazioni potrà modificare profondamente tale interpretazione.

Il software dovrà quindi ragionare sempre a livello sistemico.

Mai a livello di singolo pianeta.

---

# REQ-AST-008

Ogni interpretazione dovrà tenere conto del contesto complessivo del tema.

---

# REQ-AST-009

Le interpretazioni isolate dovranno essere considerate informazioni di basso livello.

La narrativa dovrà invece utilizzare esclusivamente il risultato dell'aggregazione finale.

---

# 8. Gerarchia delle informazioni

Durante l'elaborazione il software distinguerà quattro livelli.

Livello 1

Dato astronomico

Esempio

Giove a 15° Toro.

---

Livello 2

Condizione astrologica

Esempio

Giove angolare.

---

Livello 3

Evidenza

Esempio

Protezione della carriera.

---

Livello 4

Tema

Esempio

Realizzazione professionale.

La narrativa lavorerà esclusivamente sul Livello 4.

Mai direttamente sul Livello 1.

Mai direttamente sul Livello 2.

Questo permette di separare completamente il calcolo astronomico dalla comunicazione verso il consultante.

# 9. Il concetto di Protezione

Uno degli errori più comuni nell'interpretazione astrologica consiste nel considerare una configurazione favorevole come una garanzia di eventi positivi.

Il modello Astro-Val rifiuta questa impostazione.

Una configurazione favorevole rappresenta invece un possibile fattore di protezione.

La protezione può manifestarsi in molte forme differenti.

Ad esempio:

- maggiore lucidità nelle decisioni;
- incontri favorevoli;
- aiuti esterni;
- capacità di reagire;
- diagnosi tempestive;
- soluzioni impreviste;
- minore gravità delle conseguenze;
- recupero più rapido.

La forma concreta della protezione dipenderà sempre dalla situazione reale vissuta dal consultante.

---

# REQ-AST-010

Il software non dovrà mai promettere l'assenza di problemi.

---

# REQ-AST-011

Il concetto di protezione dovrà essere sempre espresso come possibile attenuazione o migliore gestione degli eventi.

---

# 10. Il concetto di Esposizione

Allo stesso modo una configurazione impegnativa non deve essere interpretata come certezza di eventi negativi.

Essa descrive un'area maggiormente esposta.

Una maggiore esposizione può significare:

- maggiore coinvolgimento;
- maggiore responsabilità;
- maggiore fatica;
- maggiore necessità di attenzione;
- richiesta di maturazione.

L'esposizione non coincide con il danno.

---

# REQ-AST-012

Le configurazioni critiche dovranno essere interpretate come aree che richiedono maggiore consapevolezza.

---

# 11. Intensità

Ogni tema possiede una propria intensità.

L'intensità rappresenta quanto quell'ambito sarà presente durante l'anno.

Non indica se sarà positivo o negativo.

Ad esempio:

Carriera

Intensità alta

può significare:

- crescita;
- cambiamenti;
- responsabilità;
- nuove opportunità;
- ridefinizione del ruolo.

L'intensità misura la presenza del tema.

Non il suo giudizio morale.

---

# REQ-AST-013

Intensità e polarità rappresentano concetti distinti.

---

# 12. Polarità

Ogni tema possiede una polarità.

La polarità rappresenta l'equilibrio tra:

- fattori favorevoli;
- fattori impegnativi;
- fattori neutri.

La polarità non dovrà mai essere rappresentata come:

bene

contro

ma come una misura dell'equilibrio simbolico.

---

# REQ-AST-014

La polarità dovrà essere sempre calcolata sull'insieme delle evidenze.

Mai sul singolo pianeta.

---

# 13. Gerarchia interpretativa

Durante l'elaborazione il software dovrà seguire rigorosamente questa gerarchia.

Configurazione astronomica

↓

Condizione planetaria

↓

Regola astrologica

↓

Evidenza

↓

Tema

↓

Relazione

Ogni livello potrà utilizzare esclusivamente il livello immediatamente precedente.

La narrativa non dovrà mai leggere direttamente i pianeti.

---

# REQ-AST-015

Ogni frase della relazione dovrà poter essere ricondotta alle evidenze astrologiche che l'hanno generata.

---

# 14. Giove

Nel modello Astro-Val Giove rappresenta prevalentemente:

- protezione;
- facilitazione;
- crescita;
- sostegno;
- recupero;
- fiducia;
- ampliamento.

Non rappresenta automaticamente fortuna.

Ad esempio.

Una persona potrebbe affrontare un problema sanitario.

La presenza di Giove potrebbe manifestarsi attraverso:

- diagnosi precoce;
- scelta del medico corretto;
- terapia efficace;
- recupero favorevole.

Questo costituisce perfettamente una protezione.

---

# REQ-AST-016

Giove non deve mai essere descritto come garanzia di successo.

---

# 15. Venere

Venere rappresenta:

- armonia;
- equilibrio;
- mediazione;
- benessere;
- relazioni;
- qualità della vita.

La sua presenza può facilitare la gestione delle situazioni.

Non elimina automaticamente i conflitti.

---

# REQ-AST-017

Venere dovrà essere interpretata come fattore di armonizzazione.

---

# 16. Saturno

Saturno rappresenta:

- responsabilità;
- costruzione;
- disciplina;
- maturazione;
- lentezza;
- consolidamento.

Non rappresenta automaticamente sofferenza.

Una configurazione saturnina può essere estremamente utile quando richiede stabilità e perseveranza.

---

# REQ-AST-018

La narrativa non dovrà utilizzare termini catastrofici per descrivere Saturno.

---

# 17. Marte

Marte rappresenta:

- energia;
- iniziativa;
- azione;
- conflitto;
- coraggio;
- competizione.

Può manifestarsi in numerose modalità differenti.

Il software dovrà descrivere la qualità dell'energia.

Non un singolo evento.

---

# REQ-AST-019

Marte non dovrà mai essere interpretato esclusivamente come violenza o malattia.

---

# 18. Urano

Urano rappresenta:

- cambiamento;
- innovazione;
- rottura degli schemi;
- improvvisazione;
- libertà.

Può introdurre elementi inattesi.

L'imprevisto non coincide necessariamente con un evento negativo.

---

# REQ-AST-020

Le configurazioni uraniane dovranno essere descritte come fattori di cambiamento.

Mai come eventi certi.


# 19. Nettuno

Nettuno rappresenta il rapporto con ciò che non è immediatamente misurabile.

Può manifestarsi attraverso:

- ispirazione;
- intuizione;
- spiritualità;
- idealizzazione;
- immaginazione;
- sensibilità;
- confusione;
- perdita di riferimenti;
- bisogno di evasione.

La sua interpretazione dipenderà sempre dal contesto generale della Rivoluzione Solare.

Una forte componente nettuniana non dovrà mai essere descritta esclusivamente come elemento negativo.

Allo stesso modo non dovrà essere interpretata come "illuminazione spirituale" in maniera automatica.

---

# REQ-AST-021

Nettuno dovrà essere interpretato come fattore di permeabilità simbolica.

Il software dovrà descrivere sia il potenziale creativo sia il rischio di dispersione.

Mai uno soltanto.

---

# 20. Plutone

Plutone rappresenta i processi di trasformazione profonda.

Può indicare:

- conclusione di un ciclo;
- rigenerazione;
- cambiamento radicale;
- presa di coscienza;
- trasformazione psicologica;
- cambiamento irreversibile.

Plutone raramente descrive eventi superficiali.

Il suo significato riguarda generalmente processi interiori o trasformazioni strutturali.

---

# REQ-AST-022

Le configurazioni plutoniane dovranno essere descritte come processi trasformativi.

Mai come eventi catastrofici.

---

# 21. Il Sole

Il Sole rappresenta:

- identità;
- vitalità;
- centralità;
- volontà;
- espressione personale.

La sua posizione nella Rivoluzione Solare contribuisce a definire il settore nel quale il consultante tenderà a investire maggiormente le proprie energie.

---

# REQ-AST-023

Il Sole non rappresenta automaticamente successo.

Rappresenta il centro dell'attenzione dell'anno.

---

# 22. La Luna

La Luna rappresenta:

- emotività;
- bisogni;
- sicurezza;
- adattamento;
- sensibilità.

Le configurazioni lunari dovranno essere interpretate considerando il loro impatto sulla percezione soggettiva dell'anno.

---

# REQ-AST-024

La Luna descrive prevalentemente il modo in cui gli eventi potrebbero essere vissuti.

Non gli eventi stessi.

---

# 23. Mercurio

Mercurio rappresenta:

- comunicazione;
- studio;
- scambio;
- ragionamento;
- mobilità;
- informazioni.

Una forte presenza mercuriale può favorire:

- nuovi contatti;
- apprendimento;
- trattative;
- negoziazioni;
- attività intellettuali.

---

# REQ-AST-025

Mercurio dovrà essere interpretato come fattore di elaborazione e comunicazione.

---

# 24. Le Case

Nel modello Astro-Val le Case rappresentano gli ambiti nei quali i simboli astrologici tendono a manifestarsi.

Esse non determinano da sole l'interpretazione.

Ogni Casa costituisce un contenitore simbolico.

L'interpretazione finale nasce dall'interazione tra:

- pianeta;
- condizione;
- Casa;
- regole dell'Astrologia Attiva;
- contesto generale.

---

# REQ-AST-026

La Casa non deve essere interpretata indipendentemente dal pianeta.

---

# 25. Gli aspetti

Gli aspetti non rappresentano automaticamente eventi.

Essi modificano la qualità della relazione tra i simboli.

Un aspetto armonico può facilitare.

Un aspetto dinamico può richiedere maggiore impegno.

Entrambi possono risultare evolutivamente utili.

---

# REQ-AST-027

Gli aspetti dovranno essere interpretati come modificatori.

Mai come eventi autonomi.

---

# 26. Le dignità

Le dignità rappresentano la qualità espressiva del pianeta.

Esse modificano l'efficacia della funzione simbolica.

Non modificano il significato fondamentale del pianeta.

Ad esempio.

Giove in buona dignità

non diventa "più fortunato".

Esprime con maggiore coerenza la propria funzione protettiva.

---

# REQ-AST-028

Le dignità costituiscono modificatori dell'intensità interpretativa.

---

# 27. L'angularità

L'angularità misura il livello di evidenza del simbolo.

Un pianeta angolare tende ad assumere un ruolo più visibile durante l'anno.

Questo non implica automaticamente maggiore positività o negatività.

Indica semplicemente maggiore rilevanza.

---

# REQ-AST-029

L'angularità incrementa la priorità della configurazione.

Non modifica la natura simbolica del pianeta.

---

# 28. Retrogradazione

La retrogradazione modifica il modo nel quale il simbolo tende ad esprimersi.

Può suggerire:

- revisione;
- rallentamento;
- introspezione;
- riconsiderazione.

Non costituisce automaticamente una condizione sfavorevole.

---

# REQ-AST-030

La retrogradazione dovrà essere interpretata come modificatore della dinamica evolutiva del simbolo.

Mai come penalizzazione automatica.


# 29. L'importanza dell'insieme

Il significato di una Rivoluzione Solare non nasce dalla somma meccanica delle singole configurazioni.

Nasce dalla loro integrazione.

Ogni elemento del tema può rafforzare, attenuare o modificare il significato degli altri.

L'obiettivo del motore non è interpretare cento configurazioni.

L'obiettivo è comprendere quale storia raccontano insieme.

---

# REQ-AST-031

La relazione finale dovrà descrivere il significato complessivo dell'anno.

Non l'elenco delle configurazioni astrologiche.

---

# 30. I temi prevalgono sui dettagli

Durante l'elaborazione il sistema produce centinaia di informazioni.

Il consultante non deve leggerle tutte.

Il consultante deve comprendere:

- quale sarà il clima dell'anno;
- quali aree saranno centrali;
- quali energie accompagneranno il periodo.

Il software dovrà quindi privilegiare i grandi temi rispetto ai singoli dettagli tecnici.

---

# REQ-AST-032

La narrativa dovrà essere costruita a partire dai temi dominanti.

Non dall'elenco dei pianeti.

---

# 31. La centralità del consultante

La relazione non parla dei pianeti.

Parla della persona.

Il consultante non è interessato a sapere che:

"Giove è trigono a..."

È interessato a comprendere:

"Cosa potrebbe significare questo anno nella mia vita?"

L'intero progetto Astro-Val nasce per rispondere a questa domanda.

---

# REQ-AST-033

La narrativa dovrà essere centrata sull'esperienza umana del consultante.

Mai sulla terminologia astrologica.

---

# 32. Il ruolo del consulto

La relazione non conclude l'interpretazione.

La relazione apre il colloquio.

Essa dovrà:

- preparare il consultante;
- chiarire il quadro generale;
- favorire domande;
- facilitare il dialogo con l'astrologo.

---

# REQ-AST-034

La relazione non dovrà sostituire il consulto professionale.

---

# 33. Prudenza interpretativa

Quando una configurazione presenta molte possibili manifestazioni, il software dovrà scegliere la descrizione più generale.

Esempio.

NON

"Quest'anno subirai un intervento chirurgico."

MA

"L'area del benessere potrebbe richiedere una maggiore attenzione, suggerendo l'opportunità di non trascurare eventuali segnali e di affrontare con tempestività le situazioni che dovessero presentarsi."

Questa formulazione è coerente con:

- il principio della probabilità;
- il principio della pensilina;
- la deontologia professionale;
- la pratica dell'Astrologia Attiva.

---

# REQ-AST-035

In presenza di più interpretazioni possibili il software dovrà preferire la formulazione più ampia e meno deterministica.

---

# 34. Coerenza narrativa

La relazione dovrà essere percepita come un testo scritto da un unico autore.

Non come l'unione di paragrafi indipendenti.

Ogni sezione dovrà preparare naturalmente la successiva.

I temi dovranno richiamarsi tra loro.

Le conclusioni dovranno derivare dai capitoli precedenti.

---

# REQ-AST-036

La narrativa dovrà possedere continuità logica e stilistica.

---

# 35. Principio di spiegabilità

Ogni frase prodotta dal sistema deve poter essere spiegata.

Per ciascuna affermazione dovrà essere possibile risalire a:

Configurazioni Astronomiche

↓

Condizioni Planetarie

↓

Regole dell'Astrologia Attiva

↓

Evidenze

↓

Temi

↓

Paragrafo Narrativo

Questa catena costituisce uno dei principi architetturali fondamentali di Astro-Val.

---

# REQ-AST-037

Nessuna frase della relazione può essere generata senza una catena completa di evidenze.

---

# 36. Principio di trasparenza

Il motore deve sempre essere in grado di giustificare le proprie conclusioni.

La narrativa non deve mai contenere:

- intuizioni non motivate;
- deduzioni arbitrarie;
- frasi puramente ornamentali;
- interpretazioni non riconducibili alle regole astrologiche.

---

# REQ-AST-038

Ogni conclusione dovrà essere verificabile attraverso le evidenze prodotte dal dominio.

---

# 37. Obiettivo finale del progetto

Astro-Val non nasce per dire al consultante:

"Cosa succederà."

Nasce per aiutarlo a comprendere:

- quali aree della vita saranno maggiormente coinvolte;
- quali risorse simboliche potrebbero sostenerlo;
- quali atteggiamenti potrebbero risultare più efficaci;
- come affrontare con maggiore consapevolezza il significato dell'anno.

Questo rappresenta il principio fondamentale dell'intero progetto.

---

# REQ-AST-039

Il software deve descrivere il significato simbolico dell'anno.

Mai pretendere di prevedere il futuro.

---

# REQ-AST-040

L'intero motore interpretativo di Astro-Val dovrà sempre rimanere coerente con i principi dell'Astrologia Attiva della scuola di Ciro Discepolo e con la filosofia della "pensilina", privilegiando la prudenza interpretativa, il linguaggio probabilistico e la centralità del consultante.

---

# Stato del documento

Versione: 2.0

Stato: Authoritative

Documento normativo.

Qualsiasi implementazione software in contrasto con questo documento dovrà essere modificata.

# ===========================


---

# REQ-AST-041

La crescita della conoscenza astrologica deve essere progressiva e verificabile.

Ogni nuova regola astrologica implementata deve:

- essere documentata;
- essere tracciabile;
- contribuire al Knowledge Coverage del dominio.




---

# REQ-AST-042

La conoscenza astrologica del progetto deve essere mantenuta
esclusivamente nell'Atlas.

Le Rule non devono duplicare il significato simbolico delle
configurazioni astrologiche.

Ogni modifica interpretativa deve essere effettuata
nell'Atlas.


# FINE DOCUMENTO
# ===========================



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
