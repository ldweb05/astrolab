<?php
declare(strict_types=1);

$root = dirname(__DIR__);
$manifestPath = __DIR__.'/fixtures/rule_engine_freeze.json';

$manifestRaw = file_get_contents($manifestPath);

if ($manifestRaw === false) {
    fwrite(STDERR, "Manifest FREEZE non leggibile\n");
    exit(1);
}

try {
    $manifest = json_decode(
        $manifestRaw,
        true,
        512,
        JSON_THROW_ON_ERROR
    );
} catch (JsonException $exception) {
    fwrite(
        STDERR,
        "Manifest FREEZE JSON non valido: "
        .$exception->getMessage()
        ."\n"
    );
    exit(1);
}

if (($manifest['status'] ?? null) !== 'FREEZE') {
    fwrite(STDERR, "Stato FREEZE non valido\n");
    exit(1);
}

if (($manifest['baseline_commit'] ?? null) !== '0bc53d0') {
    fwrite(STDERR, "Commit baseline FREEZE non valido\n");
    exit(1);
}

$expectedFiles = $manifest['files'] ?? null;

if (!is_array($expectedFiles) || $expectedFiles === []) {
    fwrite(STDERR, "File baseline FREEZE mancanti\n");
    exit(1);
}

$rulesDirectory = $root.'/includes/forecast/rules';
$runtimeRuleFiles = glob($rulesDirectory.'/Rule*.php') ?: [];
sort($runtimeRuleFiles, SORT_STRING);

if (count($runtimeRuleFiles) !== 120) {
    fwrite(
        STDERR,
        "Numero file Rule modificato: "
        .count($runtimeRuleFiles)
        ."\n"
    );
    exit(1);
}

$runtimeRuleClasses = [];

foreach ($runtimeRuleFiles as $ruleFile) {
    $filename = basename($ruleFile);

    if (
        preg_match(
            '/^(Rule[0-9]{4}_[A-Za-z0-9_]+)\.php$/',
            $filename,
            $matches
        ) !== 1
    ) {
        fwrite(
            STDERR,
            "Nome file Rule non valido: {$filename}\n"
        );
        exit(1);
    }

    $runtimeRuleClasses[] = $matches[1];
}

if (
    ($runtimeRuleClasses[0] ?? null)
    !== ($manifest['first_rule_class'] ?? null)
) {
    fwrite(STDERR, "Prima Rule differente dalla baseline\n");
    exit(1);
}

if (
    ($runtimeRuleClasses[count($runtimeRuleClasses) - 1] ?? null)
    !== ($manifest['last_rule_class'] ?? null)
) {
    fwrite(STDERR, "Ultima Rule differente dalla baseline\n");
    exit(1);
}

foreach ($expectedFiles as $relativePath => $expectedHash) {
    $absolutePath = $root.'/'.$relativePath;

    if (!is_file($absolutePath)) {
        fwrite(
            STDERR,
            "File Rule Engine mancante: {$relativePath}\n"
        );
        exit(1);
    }

    $actualHash = hash_file('sha256', $absolutePath);

    if (
        !is_string($actualHash)
        || !hash_equals((string)$expectedHash, $actualHash)
    ) {
        fwrite(
            STDERR,
            "FREEZE violato: {$relativePath}\n"
        );
        exit(1);
    }
}

require_once $root.'/includes/forecast/RuleRegistry.php';

$registeredRules = RuleRegistry::all();

if (count($registeredRules) !== 120) {
    fwrite(
        STDERR,
        "Registry modificato: "
        .count($registeredRules)
        ." Rule registrate\n"
    );
    exit(1);
}

if (
    count($registeredRules)
    !== (int)($manifest['registered_rules'] ?? 0)
) {
    fwrite(
        STDERR,
        "Registry non coerente con la baseline FREEZE\n"
    );
    exit(1);
}

$firstRule = $registeredRules[0] ?? null;
$lastRule = $registeredRules[count($registeredRules) - 1] ?? null;

if (
    !is_object($firstRule)
    || get_class($firstRule)
        !== ($manifest['first_rule_class'] ?? null)
) {
    fwrite(STDERR, "Prima Rule runtime non valida\n");
    exit(1);
}

if (
    !is_object($lastRule)
    || get_class($lastRule)
        !== ($manifest['last_rule_class'] ?? null)
) {
    fwrite(STDERR, "Ultima Rule runtime non valida\n");
    exit(1);
}

echo sprintf(
    "RULE ENGINE FREEZE OK: %d Rule, %d file protetti\n",
    count($registeredRules),
    count($expectedFiles)
);
