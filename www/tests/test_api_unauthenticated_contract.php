<?php
declare(strict_types=1);

$baseUrl = rtrim(
    getenv('ASTRO_VAL_BASE_URL') ?: 'http://127.0.0.1',
    '/'
);

$cases = [
    'tema_api' => [
        'url' => $baseUrl.'/api/tema_api.php?tipo=natale',
        'expected_status' => [401, 403],
        'expects_json' => null,
    ],
    'rs_api' => [
        'url' => $baseUrl.'/api/rs_api.php',
        'expected_status' => [401, 403],
        'expects_json' => null,
    ],
    'rl_api' => [
        'url' => $baseUrl.'/api/rl_api.php',
        'expected_status' => [401, 403],
        'expects_json' => null,
    ],
    'stampa_pdf_api' => [
        'url' => $baseUrl.'/api/stampa_pdf_api.php',
        'expected_status' => [401, 403],
        'expects_json' => null,
    ],
];

foreach ($cases as $caseId => $case) {
    $headers = [];
    $body = '';

    $context = stream_context_create([
        'http' => [
            'method' => 'GET',
            'ignore_errors' => true,
            'timeout' => 10,
            'header' => [
                'Accept: application/json',
                'User-Agent: Astro-Val-V6-Hardening',
            ],
        ],
    ]);

    set_error_handler(
        static function (
            int $severity,
            string $message
        ) use ($caseId): never {
            throw new RuntimeException(
                "{$caseId}: richiesta HTTP fallita: {$message}"
            );
        }
    );

    try {
        $response = file_get_contents(
            $case['url'],
            false,
            $context
        );
    } finally {
        restore_error_handler();
    }

    if ($response === false) {
        fwrite(
            STDERR,
            "{$caseId}: risposta HTTP non disponibile\n"
        );
        exit(1);
    }

    $body = $response;
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

        if (
            stripos($header, 'Content-Type:') === 0
        ) {
            $contentType = trim(
                substr($header, strlen('Content-Type:'))
            );
        }
    }

    if (!in_array(
        $status,
        $case['expected_status'],
        true
    )) {
        fwrite(
            STDERR,
            sprintf(
                "%s: status HTTP inatteso %s\n",
                $caseId,
                var_export($status, true)
            )
        );
        exit(1);
    }

    foreach ([
        'Warning:',
        'Notice:',
        'Deprecated:',
        'Fatal error:',
        'Stack trace:',
        '#0 ',
    ] as $forbidden) {
        if (str_contains($body, $forbidden)) {
            fwrite(
                STDERR,
                "{$caseId}: diagnostica PHP esposta nel body\n"
            );
            exit(1);
        }
    }

    $message = '';

    if (
        stripos($contentType, 'application/json') !== false
    ) {
        try {
            $decoded = json_decode(
                $body,
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

        $message = implode(
            ' ',
            array_map(
                static fn(mixed $value): string =>
                    is_scalar($value)
                        ? (string)$value
                        : '',
                $decoded
            )
        );
    } elseif (
        stripos($contentType, 'text/plain') !== false
        || stripos($contentType, 'text/html') !== false
        || $contentType === ''
    ) {
        $message = strip_tags($body);
    } else {
        fwrite(
            STDERR,
            "{$caseId}: Content-Type non previsto: "
            .$contentType
            ."\n"
        );
        exit(1);
    }

    $normalizedMessage = mb_strtolower(
        trim($message)
    );

    if (
        !str_contains($normalizedMessage, 'autentic')
        && !str_contains($normalizedMessage, 'accesso')
        && !str_contains($normalizedMessage, 'login')
    ) {
        fwrite(
            STDERR,
            "{$caseId}: risposta autenticazione non coerente: "
            .trim($message)
            ."\n"
        );
        exit(1);
    }

    echo sprintf(
        "%s UNAUTHENTICATED CONTRACT OK: HTTP %d\n",
        strtoupper($caseId),
        $status
    );
}

echo "API UNAUTHENTICATED CONTRACT OK\n";
