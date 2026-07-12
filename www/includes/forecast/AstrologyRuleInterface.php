<?php
declare(strict_types=1);

interface AstrologyRuleInterface
{
    public function apply(
        array $planetConditions,
        EvidenceEngine $engine
    ): void;
}
