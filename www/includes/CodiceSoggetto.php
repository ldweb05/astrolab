<?php
/**
 * CodiceSoggetto.php - generazione automatica del CODICE dei soggetti.
 *
 * Riferimento: docs/roadmaps/ROADMAP_CODICE_LOGIN_SOGGETTI.md (C1-C7).
 * Formato: prefisso di 2 lettere dell'astrologo + numero progressivo di
 * almeno 3 cifre (es. LD001, RF120, RF1000).
 *
 * - Il prefisso e' salvato una sola volta in utenti.prefisso_codice.
 * - Il numero deriva da utenti.ultimo_numero_codice, che non torna mai
 *   indietro: il numero di un soggetto eliminato non viene riassegnato.
 * - generaCodice() va chiamata DENTRO la transazione dell'INSERT del
 *   soggetto: blocca la riga dell'astrologo (FOR UPDATE), cosi' due
 *   inserimenti simultanei non ricevono mai lo stesso numero, e se l'INSERT
 *   fallisce il rollback annulla anche l'avanzamento del contatore.
 */
class CodiceSoggetto
{
    /** Lettere maiuscole A-Z senza accenti, tutto il resto rimosso. */
    public static function normalizza(string $testo): string
    {
        $mappa = [
            'à' => 'a', 'á' => 'a', 'â' => 'a', 'ä' => 'a', 'ã' => 'a', 'å' => 'a',
            'è' => 'e', 'é' => 'e', 'ê' => 'e', 'ë' => 'e',
            'ì' => 'i', 'í' => 'i', 'î' => 'i', 'ï' => 'i',
            'ò' => 'o', 'ó' => 'o', 'ô' => 'o', 'ö' => 'o', 'õ' => 'o', 'ø' => 'o',
            'ù' => 'u', 'ú' => 'u', 'û' => 'u', 'ü' => 'u',
            'ñ' => 'n', 'ç' => 'c', 'ß' => 'ss', 'ý' => 'y', 'ÿ' => 'y',
            'À' => 'A', 'Á' => 'A', 'Â' => 'A', 'Ä' => 'A', 'Ã' => 'A', 'Å' => 'A',
            'È' => 'E', 'É' => 'E', 'Ê' => 'E', 'Ë' => 'E',
            'Ì' => 'I', 'Í' => 'I', 'Î' => 'I', 'Ï' => 'I',
            'Ò' => 'O', 'Ó' => 'O', 'Ô' => 'O', 'Ö' => 'O', 'Õ' => 'O', 'Ø' => 'O',
            'Ù' => 'U', 'Ú' => 'U', 'Û' => 'U', 'Ü' => 'U',
            'Ñ' => 'N', 'Ç' => 'C', 'Ý' => 'Y',
        ];
        $testo = strtr($testo, $mappa);
        return preg_replace('/[^A-Z]/', '', strtoupper($testo));
    }

    /**
     * Prefissi candidati in ordine di preferenza (C3): prima lettera sempre
     * l'iniziale del nome; seconda lettera: iniziale del cognome, poi 2a, 3a, ...
     * lettera del nome, poi 2a, 3a, ... del cognome, poi A-Z.
     */
    public static function candidatiPrefisso(string $nome, string $cognome): array
    {
        $n = self::normalizza($nome);
        $c = self::normalizza($cognome);
        if ($n === '') {
            $n = $c;
            $c = '';
        }
        if ($n === '') {
            return [];
        }

        $prima   = $n[0];
        $seconde = [];
        if ($c !== '') {
            $seconde[] = $c[0];
        }
        for ($i = 1; $i < strlen($n); $i++) {
            $seconde[] = $n[$i];
        }
        for ($i = 1; $i < strlen($c); $i++) {
            $seconde[] = $c[$i];
        }
        foreach (range('A', 'Z') as $lettera) {
            $seconde[] = $lettera;
        }

        $candidati = [];
        foreach ($seconde as $s) {
            $p = $prima . $s;
            if (!in_array($p, $candidati, true)) {
                $candidati[] = $p;
            }
        }
        return $candidati;
    }

    /**
     * Un prefisso e' occupato (C4) se e' gia' il prefisso di un altro utente o se
     * esiste un codice soggetto di un altro astrologo nel formato <prefisso><cifre>.
     */
    public static function prefissoOccupato(PDO $pdo, string $prefisso, ?int $utenteId): bool
    {
        $stmt = $pdo->prepare(
            "SELECT 1 FROM utenti
              WHERE prefisso_codice = ? AND id IS DISTINCT FROM ?
             UNION ALL
             SELECT 1 FROM soggetti
              WHERE codice ~ ? AND utente_id IS DISTINCT FROM ?
             LIMIT 1"
        );
        $stmt->execute([$prefisso, $utenteId, '^' . $prefisso . '[0-9]+$', $utenteId]);
        return (bool)$stmt->fetchColumn();
    }

    /** Primo prefisso libero per nome e cognome dati (C3, C4). */
    public static function calcolaPrefisso(PDO $pdo, string $nome, string $cognome, ?int $utenteId = null): string
    {
        foreach (self::candidatiPrefisso($nome, $cognome) as $p) {
            if (!self::prefissoOccupato($pdo, $p, $utenteId)) {
                return $p;
            }
        }
        throw new RuntimeException('Nessun prefisso codice disponibile.');
    }

    /**
     * Genera il prossimo CODICE per l'astrologo $utenteId e avanza il contatore.
     * Richiede una transazione gia' aperta dal chiamante.
     */
    public static function generaCodice(PDO $pdo, int $utenteId): string
    {
        if (!$pdo->inTransaction()) {
            throw new LogicException('generaCodice() richiede una transazione aperta.');
        }

        $stmt = $pdo->prepare(
            "SELECT username, nome_completo, prefisso_codice, ultimo_numero_codice
               FROM utenti WHERE id = ? FOR UPDATE"
        );
        $stmt->execute([$utenteId]);
        $utente = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$utente) {
            throw new RuntimeException('Utente non trovato.');
        }

        $prefisso = $utente['prefisso_codice'];
        if ($prefisso === null || $prefisso === '') {
            // Caso residuo (C5): utente senza prefisso. Nome = prima parola di
            // nome_completo, cognome = il resto; se vuoto si usa lo username.
            $parti   = preg_split('/\s+/', trim((string)$utente['nome_completo']), 2);
            $nome    = $parti[0] ?? '';
            $cognome = $parti[1] ?? '';
            if (self::normalizza($nome . $cognome) === '') {
                $nome    = (string)$utente['username'];
                $cognome = '';
            }
            $prefisso = self::calcolaPrefisso($pdo, $nome, $cognome, $utenteId);
            $pdo->prepare("UPDATE utenti SET prefisso_codice = ? WHERE id = ?")
                ->execute([$prefisso, $utenteId]);
        }

        // Per sicurezza si considera anche il numero piu' alto gia' presente con
        // questo prefisso (es. codici inseriti a mano prima di questa funzione).
        $stmt = $pdo->prepare(
            "SELECT COALESCE(MAX(CAST(SUBSTRING(codice FROM 3) AS INTEGER)), 0)
               FROM soggetti WHERE codice ~ ?"
        );
        $stmt->execute(['^' . $prefisso . '[0-9]+$']);
        $maxEsistente = (int)$stmt->fetchColumn();

        $numero = max((int)$utente['ultimo_numero_codice'], $maxEsistente) + 1;
        $pdo->prepare("UPDATE utenti SET ultimo_numero_codice = ? WHERE id = ?")
            ->execute([$numero, $utenteId]);

        return $prefisso . str_pad((string)$numero, 3, '0', STR_PAD_LEFT);
    }
}
