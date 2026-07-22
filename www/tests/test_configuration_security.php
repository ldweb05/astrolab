<?php
declare(strict_types=1);

$applicationRoot = dirname(__DIR__);
$repositoryRoot = dirname($applicationRoot);
$issues = [];

$gitAvailable = false;

exec(
    'git -C '.escapeshellarg($repositoryRoot)
    .' rev-parse --is-inside-work-tree 2>/dev/null',
    $gitOutput,
    $gitStatus
);

if (
    $gitStatus === 0
    && trim(implode("\n", $gitOutput)) === 'true'
) {
    $gitAvailable = true;
}

if ($gitAvailable) {
    $trackedFiles = [];

    exec(
        'git -C '.escapeshellarg($repositoryRoot).' ls-files',
        $trackedFiles,
        $trackedStatus
    );

    if ($trackedStatus !== 0) {
        $issues[] = 'Impossibile leggere i file tracciati da Git';
    } else {
        foreach ($trackedFiles as $trackedFile) {
            $basename = basename($trackedFile);

            if (
                $basename === '.env'
                || str_starts_with($basename, '.env.bak')
                || str_ends_with($basename, '.dump')
                || preg_match(
                    '/(?:backup|bkup).*\.sql$/iu',
                    $basename
                ) === 1
            ) {
                $issues[] =
                    "File sensibile tracciato da Git: {$trackedFile}";
            }
        }
    }

    $examplePath = $repositoryRoot.'/.env.example';

    if (!is_file($examplePath)) {
        $issues[] = '.env.example mancante';
    } else {
        $example = file_get_contents($examplePath);

        if ($example === false) {
            $issues[] = '.env.example non leggibile';
        } else {
            $lines = preg_split('/\R/u', $example) ?: [];

            foreach ($lines as $lineNumber => $line) {
                $line = trim($line);

                if (
                    $line === ''
                    || str_starts_with($line, '#')
                    || !str_contains($line, '=')
                ) {
                    continue;
                }

                [$key, $value] = array_pad(
                    explode('=', $line, 2),
                    2,
                    ''
                );

                $key = strtoupper(trim($key));
                $value = trim(trim($value), "\"'");

                if (
                    preg_match(
                        '/(?:PASS|PASSWORD|SECRET|TOKEN|API_KEY|PRIVATE_KEY)/u',
                        $key
                    ) !== 1
                ) {
                    continue;
                }

                if (!in_array(
                    strtolower($value),
                    [
                        '',
                        'changeme',
                        'change-me',
                        'example',
                        'placeholder',
                        'your-value-here',
                    ],
                    true
                )) {
                    $issues[] = sprintf(
                        '.env.example:%d possibile segreto reale in %s',
                        $lineNumber + 1,
                        $key
                    );
                }
            }
        }
    }
}

$runtimeRoots = [
    $applicationRoot.'/api',
    $applicationRoot.'/includes',
];

$credentialPatterns = [
    '~postgres(?:ql)?://[^\s"\']+:[^\s"\']+@~iu',
    '~\b(?:DB_PASSWORD|POSTGRES_PASSWORD)\s*=\s*["\'][^"\']+["\']~iu',
    '~\bpassword\s*=>\s*["\'][^"\']+["\']~iu',
];

foreach ($runtimeRoots as $runtimeRoot) {
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator(
            $runtimeRoot,
            FilesystemIterator::SKIP_DOTS
        )
    );

    foreach ($iterator as $file) {
        if (
            !$file->isFile()
            || strtolower($file->getExtension()) !== 'php'
        ) {
            continue;
        }

        $source = file_get_contents($file->getPathname());

        if ($source === false) {
            $issues[] =
                $file->getPathname().': file non leggibile';
            continue;
        }

        $executableText = '';

        foreach (token_get_all($source) as $token) {
            if (is_array($token)) {
                if (
                    $token[0] === T_COMMENT
                    || $token[0] === T_DOC_COMMENT
                ) {
                    continue;
                }

                $executableText .= $token[1].' ';
                continue;
            }

            $executableText .= $token.' ';
        }

        foreach ($credentialPatterns as $pattern) {
            if (preg_match($pattern, $executableText) === 1) {
                $issues[] = sprintf(
                    '%s contiene una possibile credenziale codificata',
                    $file->getPathname()
                );
            }
        }
    }
}

$bootstrapPath = $applicationRoot.'/includes/bootstrap.php';
$bootstrap = is_file($bootstrapPath)
    ? file_get_contents($bootstrapPath)
    : false;

if ($bootstrap === false) {
    $issues[] = 'bootstrap.php non leggibile';
} elseif (
    !str_contains($bootstrap, 'getenv(')
    && !str_contains($bootstrap, '$_ENV')
) {
    $issues[] =
        'bootstrap.php non sembra utilizzare variabili d’ambiente';
}

if ($issues !== []) {
    fwrite(
        STDERR,
        "CONFIGURATION SECURITY FAILED\n"
        .implode("\n", $issues)
        ."\n"
    );
    exit(1);
}

echo $gitAvailable
    ? "CONFIGURATION SECURITY OK\n"
    : "CONFIGURATION SECURITY OK — REPOSITORY CHECKS DELEGATED TO HOST\n";
