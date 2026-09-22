<?php
declare(strict_types=1);

$root = dirname(__DIR__);
$issues = [];

$composerJsonPath = $root.'/composer.json';
$composerLockPath = $root.'/composer.lock';
$autoloadPath = $root.'/vendor/autoload.php';

foreach ([
    $composerJsonPath,
    $composerLockPath,
    $autoloadPath,
] as $requiredPath) {
    if (!is_file($requiredPath)) {
        $issues[] = "File Composer mancante: {$requiredPath}";
    }
}

$composerJson = null;
$composerLock = null;

if (is_file($composerJsonPath)) {
    try {
        $composerJson = json_decode(
            (string)file_get_contents($composerJsonPath),
            true,
            512,
            JSON_THROW_ON_ERROR
        );
    } catch (Throwable $exception) {
        $issues[] = 'composer.json non valido: '
            .$exception->getMessage();
    }
}

if (is_file($composerLockPath)) {
    try {
        $composerLock = json_decode(
            (string)file_get_contents($composerLockPath),
            true,
            512,
            JSON_THROW_ON_ERROR
        );
    } catch (Throwable $exception) {
        $issues[] = 'composer.lock non valido: '
            .$exception->getMessage();
    }
}

if (is_array($composerJson)) {
    $require = $composerJson['require'] ?? [];

    if (!is_array($require)) {
        $issues[] = 'Sezione require di composer.json non valida';
    } elseif (!array_key_exists('dompdf/dompdf', $require)) {
        $issues[] = 'Dipendenza dompdf/dompdf non dichiarata';
    }
}

$lockedPackages = [];

if (is_array($composerLock)) {
    foreach ([
        $composerLock['packages'] ?? [],
        $composerLock['packages-dev'] ?? [],
    ] as $packages) {
        if (!is_array($packages)) {
            continue;
        }

        foreach ($packages as $package) {
            if (!is_array($package)) {
                continue;
            }

            $name = (string)($package['name'] ?? '');

            if ($name !== '') {
                $lockedPackages[$name] = (string)(
                    $package['version'] ?? ''
                );
            }
        }
    }

    if (!isset($lockedPackages['dompdf/dompdf'])) {
        $issues[] = 'Dompdf assente da composer.lock';
    }

    $contentHash = (string)($composerLock['content-hash'] ?? '');

    if (
        $contentHash === ''
        || preg_match('/^[a-f0-9]{32}$/', $contentHash) !== 1
    ) {
        $issues[] = 'content-hash di composer.lock non valido';
    }
}

if (is_file($autoloadPath)) {
    require_once $autoloadPath;

    if (!class_exists(\Dompdf\Dompdf::class)) {
        $issues[] = 'Classe Dompdf non caricabile';
    }

    if (
        !class_exists(
            \Composer\InstalledVersions::class
        )
    ) {
        $issues[] = 'Composer InstalledVersions non disponibile';
    } else {
        if (
            !\Composer\InstalledVersions::isInstalled(
                'dompdf/dompdf'
            )
        ) {
            $issues[] = 'Dompdf non risulta installato';
        } else {
            $installedVersion = (string)(
                \Composer\InstalledVersions::getPrettyVersion(
                    'dompdf/dompdf'
                ) ?? ''
            );

            $lockedVersion = (string)(
                $lockedPackages['dompdf/dompdf'] ?? ''
            );

            if ($installedVersion === '') {
                $issues[] =
                    'Versione Dompdf installata non rilevata';
            }

            if (
                $lockedVersion !== ''
                && $installedVersion !== ''
                && ltrim($installedVersion, 'v')
                    !== ltrim($lockedVersion, 'v')
            ) {
                $issues[] = sprintf(
                    'Versione Dompdf incoerente: installata %s, lock %s',
                    $installedVersion,
                    $lockedVersion
                );
            }
        }
    }
}

if ($issues !== []) {
    fwrite(
        STDERR,
        "COMPOSER DEPENDENCIES FAILED\n"
        .implode("\n", $issues)
        ."\n"
    );
    exit(1);
}

echo sprintf(
    "COMPOSER DEPENDENCIES OK: Dompdf %s\n",
    \Composer\InstalledVersions::getPrettyVersion(
        'dompdf/dompdf'
    )
);
