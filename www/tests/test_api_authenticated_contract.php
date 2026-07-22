<?php
declare(strict_types=1);

require_once __DIR__.'/../includes/bootstrap.php';

$baseUrl = rtrim(
    getenv('ASTRO_VAL_BASE_URL') ?: 'http://127.0.0.1',
    '/'
);

$pdo = db_connect();

$subject = $pdo->query(
    "
    SELECT
        u.id AS utente_id,
        u.username,
        u.ruolo,
        s.id AS soggetto_id,
        s.nome AS soggetto_nome,
        s.data_nascita,
        s.ora_nascita_gmt,
        s.latitudine,
        s.longitudine
    FROM utenti u
    INNER JOIN soggetti s
        ON s.utente_id = u.id
    WHERE COALESCE(u.attivo, true) = true
    ORDER BY u.id, s.id
    LIMIT 1
    "
)->fetch(PDO::FETCH_ASSOC);

if (!is_array($subject)) {
    fwrite(
        STDERR,
        "API AUTHENTICATED CONTRACT: "
        ."nessun utente con soggetto disponibile\n"
    );
    exit(1);
}

$birthDate = new DateTimeImmutable(
    (string)$subject['data_nascita']
);

$timeParts = explode(
    ':',
    (string)$subject['ora_nascita_gmt']
);

$hourGmt = (int)($timeParts[0] ?? 0)
    + ((int)($timeParts[1] ?? 0) / 60);

$sessionId = 'astrov6'.bin2hex(random_bytes(12));

session_name('PHPSESSID');
session_id($sessionId);

if (!session_start()) {
    fwrite(STDERR, "Impossibile creare la sessione di test\n");
    exit(1);
}

$_SESSION['utente_id'] = (int)$subject['utente_id'];
$_SESSION['utente_username'] = (string)$subject['username'];
$_SESSION['utente_ruolo'] = (string)$subject['ruolo'];
$_SESSION['soggetto_id'] = (int)$subject['soggetto_id'];
$_SESSION['soggetto_nome'] = (string)$subject['soggetto_nome'];

session_write_close();

$sessionSavePath = trim((string)ini_get('session.save_path'));
$sessionSavePath = $sessionSavePath !== ''
    ? $sessionSavePath
    : sys_get_temp_dir();

$sessionFile = rtrim($sessionSavePath, '/')
    .'/sess_'.$sessionId;

if (!is_file($sessionFile)) {
    fwrite(
        STDERR,
        "File sessione temporaneo non trovato: {$sessionFile}\n"
    );
    exit(1);
}

if (!chmod($sessionFile, 0666)) {
    fwrite(
        STDERR,
        "Impossibile rendere accessibile la sessione temporanea\n"
    );
    exit(1);
}

$commonQuery = [
    'g' => (int)$birthDate->format('d'),
    'm' => (int)$birthDate->format('m'),
    'a' => (int)$birthDate->format('Y'),
    'ora_gmt' => $hourGmt,
    'lat' => (float)$subject['latitudine'],
    'lon' => (float)$subject['longitudine'],
];

$currentYear = (int)date('Y');

$cases = [
    'tema_api' => [
        'url' => $baseUrl.'/api/tema_api.php?'.http_build_query(
            array_merge(
                $commonQuery,
                ['tipo' => 'natale']
            )
        ),
        'required' => [
            'pianeti',
            'case',
        ],
    ],
    'rs_api' => [
        'url' => $baseUrl.'/api/rs_api.php?'.http_build_query(
            array_merge(
                $commonQuery,
                [
                    'anno' => $currentYear,
                    'lat_rs' => (float)$subject['latitudine'],
                    'lon_rs' => (float)$subject['longitudine'],
                    'condizione' => 'Decima',
                ]
            )
        ),
        'required' => [
            'rs_gmt',
            'tema_rs',
            'valutazione',
            'previsione_annuale',
            'relazione_annuale',
            'aspetti',
            'escluso_filtro',
        ],
    ],
    'rl_api' => [
        'url' => $baseUrl.'/api/rl_api.php?'.http_build_query([
            'action' => 'lista',
            'soggetto_id' => (int)$subject['soggetto_id'],
            'anno_rs' => $currentYear,
        ]),
        'required' => [
            'ok',
            'anno_rs',
            'rs_gmt',
            'rs_fine',
            'luna_lon',
            'rl_list',
        ],
    ],
];

try {
    foreach ($cases as $caseId => $case) {
        $context = stream_context_create([
            'http' => [
                'method' => 'GET',
                'ignore_errors' => true,
                'timeout' => 120,
                'header' => [
                    'Accept: application/json',
                    'Cookie: PHPSESSID='.$sessionId,
                    'User-Agent: Astro-Val-V6-Hardening',
                ],
            ],
        ]);

        $response = file_get_contents(
            $case['url'],
            false,
            $context
        );

        if ($response === false) {
            fwrite(
                STDERR,
                "{$caseId}: risposta HTTP non disponibile\n"
            );
            exit(1);
        }

        $headers = $http_response_header ?? [];
        $status = null;
        $contentType = '';

        foreach ($headers as $header) {
            if (
                preg_match(
                    '~^HTTP/\S+\s+([0-9]{3})~i',
                    $header,
                    $matches
                ) === 1
            ) {
                $status = (int)$matches[1];
            }

            if (stripos($header, 'Content-Type:') === 0) {
                $contentType = trim(
                    substr($header, strlen('Content-Type:'))
                );
            }
        }

        if ($status !== 200) {
            fwrite(
                STDERR,
                "{$caseId}: status HTTP inatteso "
                .var_export($status, true)
                ."\n"
            );
            exit(1);
        }

        $hasJsonContentType = stripos(
            $contentType,
            'application/json'
        ) !== false;

        $legacyTemaContentType = (
            $caseId === 'tema_api'
            && stripos($contentType, 'text/html') !== false
        );

        if (!$hasJsonContentType && !$legacyTemaContentType) {
            fwrite(
                STDERR,
                "{$caseId}: Content-Type inatteso: "
                .$contentType
                ."\n"
            );
            exit(1);
        }

        foreach ([
            'Warning:',
            'Notice:',
            'Deprecated:',
            'Fatal error:',
            'Stack trace:',
        ] as $forbidden) {
            if (str_contains($response, $forbidden)) {
                fwrite(
                    STDERR,
                    "{$caseId}: diagnostica PHP esposta\n"
                );
                exit(1);
            }
        }

        if (!mb_check_encoding($response, 'UTF-8')) {
            fwrite(
                STDERR,
                "{$caseId}: risposta non UTF-8\n"
            );
            exit(1);
        }

        try {
            $decoded = json_decode(
                $response,
                true,
                512,
                JSON_THROW_ON_ERROR
            );
        } catch (JsonException $exception) {
            fwrite(
                STDERR,
                "{$caseId}: JSON non valido: "
                .$exception->getMessage()
                ."\n"
            );
            exit(1);
        }

        if (!is_array($decoded)) {
            fwrite(
                STDERR,
                "{$caseId}: risposta JSON non strutturata\n"
            );
            exit(1);
        }

        if (isset($decoded['errore'])) {
            fwrite(
                STDERR,
                "{$caseId}: errore API: "
                .(string)$decoded['errore']
                ."\n"
            );
            exit(1);
        }

        foreach ($case['required'] as $requiredKey) {
            if (!array_key_exists($requiredKey, $decoded)) {
                fwrite(
                    STDERR,
                    "{$caseId}: campo mancante {$requiredKey}\n"
                );
                exit(1);
            }
        }

        if (
            $caseId === 'rs_api'
            && (
                !is_array($decoded['relazione_annuale'])
                || count(
                    $decoded['relazione_annuale']['sections'] ?? []
                ) !== 12
            )
        ) {
            fwrite(
                STDERR,
                "rs_api: relazione_annuale non valida\n"
            );
            exit(1);
        }

        if (
            $caseId === 'rl_api'
            && ($decoded['ok'] ?? false) !== true
        ) {
            fwrite(
                STDERR,
                "rl_api: flag ok non valido\n"
            );
            exit(1);
        }

        echo sprintf(
            "%s AUTHENTICATED CONTRACT OK: HTTP %d\n",
            strtoupper($caseId),
            $status
        );
    }
} finally {
    if (isset($sessionFile) && is_file($sessionFile)) {
        @unlink($sessionFile);
    }
}

echo "API AUTHENTICATED CONTRACT OK\n";
