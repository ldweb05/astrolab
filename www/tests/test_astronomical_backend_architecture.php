<?php
declare(strict_types=1);

$runtimeRoots = [
    __DIR__.'/../api',
    __DIR__.'/../includes',
];

$forbiddenFunctions = [
    'shell_exec',
    'exec',
    'system',
    'passthru',
    'proc_open',
    'popen',
];

$forbiddenBackendPatterns = [
    '/\bswetest\b/iu',
    '/\bpython(?:2|3)?\b/iu',
];

$issues = [];

foreach ($runtimeRoots as $root) {
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator(
            $root,
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

        $path = $file->getPathname();
        $source = file_get_contents($path);

        if ($source === false) {
            $issues[] = "{$path}: file non leggibile";
            continue;
        }

        $tokens = token_get_all($source);
        $executableText = '';
        $tokenCount = count($tokens);

        for ($index = 0; $index < $tokenCount; $index++) {
            $token = $tokens[$index];

            if (is_array($token)) {
                [$tokenId, $tokenText] = $token;

                if (
                    $tokenId === T_COMMENT
                    || $tokenId === T_DOC_COMMENT
                    || $tokenId === T_WHITESPACE
                ) {
                    continue;
                }

                $executableText .= $tokenText.' ';

                if ($tokenId !== T_STRING) {
                    continue;
                }

                $functionName = strtolower($tokenText);

                if (!in_array(
                    $functionName,
                    $forbiddenFunctions,
                    true
                )) {
                    continue;
                }

                $nextIndex = $index + 1;

                while ($nextIndex < $tokenCount) {
                    $nextToken = $tokens[$nextIndex];

                    if (
                        is_array($nextToken)
                        && $nextToken[0] === T_WHITESPACE
                    ) {
                        $nextIndex++;
                        continue;
                    }

                    break;
                }

                if (
                    ($tokens[$nextIndex] ?? null) === '('
                ) {
                    $issues[] = sprintf(
                        '%s:%d chiamata vietata a %s()',
                        $path,
                        $token[2],
                        $functionName
                    );
                }

                continue;
            }

            $executableText .= $token.' ';
        }

        foreach ($forbiddenBackendPatterns as $pattern) {
            if (preg_match($pattern, $executableText) === 1) {
                $issues[] = sprintf(
                    '%s contiene un riferimento runtime vietato: %s',
                    $path,
                    $pattern
                );
            }
        }
    }
}

$sweCalcPath = __DIR__.'/../includes/SweCalc.php';
$sweCalc = file_get_contents($sweCalcPath);

if ($sweCalc === false) {
    $issues[] = 'SweCalc.php non leggibile';
} else {
    foreach ([
        'private ?FFI $sweFfi',
        'FFI::cdef',
        '/usr/local/lib/libswe.so',
        'swe_calc_ut',
    ] as $required) {
        if (!str_contains($sweCalc, $required)) {
            $issues[] =
                "SweCalc.php: contratto FFI mancante: {$required}";
        }
    }

    if (
        !str_contains($sweCalc, 'swe_houses_ex')
        && !str_contains($sweCalc, 'swe_houses')
    ) {
        $issues[] =
            'SweCalc.php: contratto FFI case mancante '
            .'(swe_houses_ex o swe_houses)';
    }
}

if ($issues !== []) {
    fwrite(
        STDERR,
        "ASTRONOMICAL BACKEND ARCHITECTURE FAILED\n"
        .implode("\n", $issues)
        ."\n"
    );
    exit(1);
}

echo "ASTRONOMICAL BACKEND ARCHITECTURE OK\n";
