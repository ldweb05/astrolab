<?php
declare(strict_types=1);

$issues = [];

if (PHP_VERSION_ID < 80300) {
    $issues[] = sprintf(
        'PHP 8.3 richiesto, versione rilevata: %s',
        PHP_VERSION
    );
}

foreach ([
    'FFI',
    'PDO',
    'pdo_pgsql',
    'pgsql',
    'json',
    'mbstring',
] as $extension) {
    if (!extension_loaded($extension)) {
        $issues[] = "Estensione PHP mancante: {$extension}";
    }
}

$ffiEnabled = strtolower(
    trim((string)ini_get('ffi.enable'))
);

if (!in_array($ffiEnabled, ['1', 'true', 'on', 'preload'], true)) {
    $issues[] = sprintf(
        'FFI non abilitata: ffi.enable=%s',
        $ffiEnabled === '' ? '(vuoto)' : $ffiEnabled
    );
}

$libswe = '/usr/local/lib/libswe.so';

if (!is_file($libswe)) {
    $issues[] = "Swiss Ephemeris mancante: {$libswe}";
} elseif (!is_readable($libswe)) {
    $issues[] = "Swiss Ephemeris non leggibile: {$libswe}";
}

$autoload = __DIR__.'/../vendor/autoload.php';

if (!is_file($autoload)) {
    $issues[] = 'Composer autoload mancante';
} else {
    require_once $autoload;

    if (!class_exists('Dompdf\\Dompdf')) {
        $issues[] = 'Dompdf non disponibile';
    }
}

try {
    $ffi = FFI::cdef(
        'const char *swe_version(char *);',
        $libswe
    );

    $buffer = $ffi->new('char[256]');
    $versionResult = $ffi->swe_version($buffer);

    $version = is_string($versionResult)
        ? $versionResult
        : FFI::string($versionResult);

    if (trim($version) === '') {
        $issues[] = 'Versione Swiss Ephemeris non rilevata';
    }
} catch (Throwable $exception) {
    $issues[] = 'Caricamento libswe via FFI fallito: '
        .$exception->getMessage();
}

if ($issues !== []) {
    fwrite(
        STDERR,
        "RUNTIME ENVIRONMENT FAILED
"
        .implode("
", $issues)
        ."
"
    );
    exit(1);
}

echo sprintf(
    "RUNTIME ENVIRONMENT OK: PHP %s, FFI, PostgreSQL, libswe, Dompdf
",
    PHP_VERSION
);
