<?php
declare(strict_types=1);

/**
 * Gestisce la quota mensile condivisa tra stampa browser ed esportazione PDF
 * dell'Annual Report.
 */
final class AnnualReportExportQuota
{
    private const FEATURE_CODE = 'annual_report_exports_max';
    private const PERIOD_TYPE = 'monthly';
    private const EXPORT_TYPES = ['browser_print', 'pdf'];

    public function __construct(
        private PDO $pdo
    ) {
    }

    /**
     * Restituisce lo stato corrente della quota senza registrare utilizzi.
     *
     * @return array{
     *     allowed: bool,
     *     unlimited: bool,
     *     limit: ?int,
     *     count: int,
     *     remaining: ?int,
     *     period_start: string
     * }
     */
    public function getStatus(int $userId, bool $isAdmin = false): array
    {
        $this->assertValidUserId($userId);

        $periodStart = $this->getCurrentPeriodStart();

        if ($isAdmin) {
            return $this->buildStatus(
                limit: null,
                count: 0,
                periodStart: $periodStart
            );
        }

        $limit = $this->getUserLimit($userId);
        $count = $this->getUsageCount($userId, $periodStart);

        return $this->buildStatus($limit, $count, $periodStart);
    }

    /**
     * Registra un utilizzo se la quota lo consente.
     *
     * La stessa idempotency key, per lo stesso utente e mese, non viene
     * conteggiata più di una volta.
     *
     * @return array{
     *     allowed: bool,
     *     consumed: bool,
     *     duplicate: bool,
     *     unlimited: bool,
     *     limit: ?int,
     *     count: int,
     *     remaining: ?int,
     *     period_start: string
     * }
     */
    public function consume(
        int $userId,
        ?int $subjectId,
        string $exportType,
        string $idempotencyKey,
        bool $isAdmin = false
    ): array {
        $this->assertValidUserId($userId);
        $this->assertValidSubjectId($subjectId);
        $this->assertValidExportType($exportType);
        $idempotencyKey = $this->normalizeIdempotencyKey($idempotencyKey);

        $periodStart = $this->getCurrentPeriodStart();

        if ($isAdmin) {
            return array_merge(
                $this->buildStatus(null, 0, $periodStart),
                [
                    'consumed' => false,
                    'duplicate' => false,
                ]
            );
        }

        $ownsTransaction = !$this->pdo->inTransaction();

        try {
            if ($ownsTransaction) {
                $this->pdo->beginTransaction();
            }

            $this->lockUser($userId);

            $limit = $this->getUserLimit($userId);
            $count = $this->getUsageCount($userId, $periodStart);

            $duplicateStmt = $this->pdo->prepare(
                "SELECT 1
                 FROM annual_report_export_usage
                 WHERE user_id = :user_id
                   AND feature_code = :feature_code
                   AND period_start = :period_start
                   AND idempotency_key = :idempotency_key
                 LIMIT 1"
            );
            $duplicateStmt->execute([
                ':user_id' => $userId,
                ':feature_code' => self::FEATURE_CODE,
                ':period_start' => $periodStart,
                ':idempotency_key' => $idempotencyKey,
            ]);

            if ($duplicateStmt->fetchColumn() !== false) {
                if ($ownsTransaction) {
                    $this->pdo->commit();
                }

                return array_merge(
                    $this->buildStatus($limit, $count, $periodStart),
                    [
                        'consumed' => false,
                        'duplicate' => true,
                    ]
                );
            }

            if ($limit !== null && $count >= $limit) {
                if ($ownsTransaction) {
                    $this->pdo->commit();
                }

                return array_merge(
                    $this->buildStatus($limit, $count, $periodStart),
                    [
                        'consumed' => false,
                        'duplicate' => false,
                    ]
                );
            }

            $insert = $this->pdo->prepare(
                "INSERT INTO annual_report_export_usage (
                    user_id,
                    subject_id,
                    feature_code,
                    export_type,
                    period_start,
                    idempotency_key
                )
                VALUES (
                    :user_id,
                    :subject_id,
                    :feature_code,
                    :export_type,
                    :period_start,
                    :idempotency_key
                )
                ON CONFLICT (
                    user_id,
                    feature_code,
                    period_start,
                    idempotency_key
                )
                DO NOTHING"
            );
            $insert->execute([
                ':user_id' => $userId,
                ':subject_id' => $subjectId,
                ':feature_code' => self::FEATURE_CODE,
                ':export_type' => $exportType,
                ':period_start' => $periodStart,
                ':idempotency_key' => $idempotencyKey,
            ]);

            $consumed = $insert->rowCount() === 1;
            $count = $this->getUsageCount($userId, $periodStart);

            if ($ownsTransaction) {
                $this->pdo->commit();
            }

            return array_merge(
                $this->buildStatus($limit, $count, $periodStart),
                [
                    'consumed' => $consumed,
                    'duplicate' => !$consumed,
                ]
            );
        } catch (Throwable $e) {
            if ($ownsTransaction && $this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            throw $e;
        }
    }

    private function getUserLimit(int $userId): ?int
    {
        $stmt = $this->pdo->prepare(
            "SELECT
                pl.limit_value,
                pl.period_type
             FROM utenti u
             JOIN piano_limiti pl ON pl.plan_id = u.plan_id
             WHERE u.id = :user_id
               AND pl.feature_code = :feature_code
               AND pl.enabled = TRUE
             LIMIT 1"
        );
        $stmt->execute([
            ':user_id' => $userId,
            ':feature_code' => self::FEATURE_CODE,
        ]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row === false) {
            throw new RuntimeException(
                'Configurazione della quota Annual Report non disponibile.'
            );
        }

        if (($row['period_type'] ?? null) !== self::PERIOD_TYPE) {
            throw new RuntimeException(
                'Periodicità della quota Annual Report non valida.'
            );
        }

        return $row['limit_value'] === null
            ? null
            : (int)$row['limit_value'];
    }

    private function getUsageCount(int $userId, string $periodStart): int
    {
        $stmt = $this->pdo->prepare(
            "SELECT COUNT(*)
             FROM annual_report_export_usage
             WHERE user_id = :user_id
               AND feature_code = :feature_code
               AND period_start = :period_start"
        );
        $stmt->execute([
            ':user_id' => $userId,
            ':feature_code' => self::FEATURE_CODE,
            ':period_start' => $periodStart,
        ]);

        return (int)$stmt->fetchColumn();
    }

    private function lockUser(int $userId): void
    {
        $stmt = $this->pdo->prepare(
            "SELECT id
             FROM utenti
             WHERE id = :user_id
             FOR UPDATE"
        );
        $stmt->execute([':user_id' => $userId]);

        if ($stmt->fetchColumn() === false) {
            throw new RuntimeException('Utente non trovato.');
        }
    }

    private function getCurrentPeriodStart(): string
    {
        $value = $this->pdo
            ->query("SELECT date_trunc('month', CURRENT_DATE)::date")
            ->fetchColumn();

        if (!is_string($value) || $value === '') {
            throw new RuntimeException(
                'Impossibile determinare il periodo corrente della quota.'
            );
        }

        return $value;
    }

    /**
     * @return array{
     *     allowed: bool,
     *     unlimited: bool,
     *     limit: ?int,
     *     count: int,
     *     remaining: ?int,
     *     period_start: string
     * }
     */
    private function buildStatus(
        ?int $limit,
        int $count,
        string $periodStart
    ): array {
        $unlimited = $limit === null;
        $remaining = $unlimited
            ? null
            : max(0, $limit - $count);

        return [
            'allowed' => $unlimited || $count < $limit,
            'unlimited' => $unlimited,
            'limit' => $limit,
            'count' => $count,
            'remaining' => $remaining,
            'period_start' => $periodStart,
        ];
    }

    private function normalizeIdempotencyKey(string $idempotencyKey): string
    {
        $idempotencyKey = trim($idempotencyKey);

        if ($idempotencyKey === '') {
            throw new InvalidArgumentException(
                'La chiave di idempotenza è obbligatoria.'
            );
        }

        if (strlen($idempotencyKey) > 100) {
            throw new InvalidArgumentException(
                'La chiave di idempotenza non può superare 100 caratteri.'
            );
        }

        return $idempotencyKey;
    }

    private function assertValidExportType(string $exportType): void
    {
        if (!in_array($exportType, self::EXPORT_TYPES, true)) {
            throw new InvalidArgumentException(
                'Tipo di esportazione Annual Report non valido.'
            );
        }
    }

    private function assertValidUserId(int $userId): void
    {
        if ($userId <= 0) {
            throw new InvalidArgumentException('ID utente non valido.');
        }
    }

    private function assertValidSubjectId(?int $subjectId): void
    {
        if ($subjectId !== null && $subjectId <= 0) {
            throw new InvalidArgumentException('ID soggetto non valido.');
        }
    }
}
