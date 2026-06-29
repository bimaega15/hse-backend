<?php
// app/Support/KpiScoring.php

namespace App\Support;

/**
 * KPI scoring engine.
 *
 * Scoring direction:
 *  - lagging_indicator : the SMALLER the realisasi, the better the nilai.
 *    The rumus bands are matched against the absolute realisasi value.
 *  - leading_indicator : the CLOSER the realisasi is to the target, the better.
 *    The rumus bands are matched against the % pencapaian (realisasi / target * 100).
 *
 * Default bands live in database/data/kpi_rumus.json so they can be tweaked and
 * re-applied to the project without touching code.
 */
class KpiScoring
{
    private static ?array $config = null;

    public static function config(): array
    {
        if (self::$config === null) {
            $path = database_path('data/kpi_rumus.json');
            $json = is_file($path) ? json_decode(file_get_contents($path), true) : null;
            self::$config = is_array($json) ? $json : self::fallback();
        }
        return self::$config;
    }

    private static function fallback(): array
    {
        return [
            'bands'  => ['sangat baik', 'baik', 'cukup', 'kurang', 'kurang baik'],
            'scores' => ['sangat baik' => 5, 'baik' => 4, 'cukup' => 3, 'kurang' => 2, 'kurang baik' => 1],
            'rumus'  => [
                ['category' => 'lagging_indicator', 'results' => [
                    ['sangat baik' => 0], ['baik' => '1-4'], ['cukup' => '5-9'], ['kurang' => '10-19'], ['kurang baik' => '>=20'],
                ]],
                ['category' => 'leading_indicator', 'results' => [
                    ['sangat baik' => '90-100'], ['baik' => '75-89'], ['cukup' => '60-74'], ['kurang' => '40-59'], ['kurang baik' => '0-39'],
                ]],
            ],
        ];
    }

    /** Full default rumus array (both categories). */
    public static function defaultRumus(): array
    {
        return self::config()['rumus'] ?? [];
    }

    /** The single {category, results} entry for a given category key. */
    public static function rumusFor(string $categoryKey): array
    {
        foreach (self::defaultRumus() as $entry) {
            if (($entry['category'] ?? null) === $categoryKey) {
                return $entry;
            }
        }
        return ['category' => $categoryKey, 'results' => []];
    }

    public static function bands(): array
    {
        return self::config()['bands'] ?? [];
    }

    public static function bandScore(?string $band): int
    {
        return (int) (self::config()['scores'][$band] ?? 0);
    }

    /**
     * Derive the lagging/leading key from a free-text category name.
     */
    public static function keyFromName(?string $name): string
    {
        $name = strtolower((string) $name);
        if (str_contains($name, 'lagging')) {
            return 'lagging_indicator';
        }
        return 'leading_indicator';
    }

    /**
     * Compute % pencapaian.
     */
    public static function percentage(string $categoryKey, float $target, ?float $realisasi): ?float
    {
        if ($realisasi === null) {
            return null;
        }

        if ($categoryKey === 'lagging_indicator') {
            // Smaller is better — full achievement when at/under threshold.
            if ($target <= 0) {
                return $realisasi <= 0 ? 100.0 : 0.0;
            }
            return $realisasi <= $target ? 100.0 : round($target / $realisasi * 100, 1);
        }

        // leading — closer to target is better.
        if ($target <= 0) {
            return 0.0;
        }
        return round($realisasi / $target * 100, 1);
    }

    /**
     * Resolve the nilai band label for a detail.
     */
    public static function nilai(string $categoryKey, float $target, ?float $realisasi, array $results): ?string
    {
        if ($realisasi === null) {
            return null;
        }

        if ($categoryKey === 'lagging_indicator') {
            $value = (float) $realisasi; // band on absolute value
        } else {
            $pct = self::percentage($categoryKey, $target, $realisasi) ?? 0;
            $value = min($pct, 100); // cap at 100 for band matching
        }

        return self::matchBand($value, $results);
    }

    /**
     * Overall band from an average % pencapaian (always %-based).
     */
    public static function overallBand(?float $averagePercent): ?string
    {
        if ($averagePercent === null) {
            return null;
        }
        $results = self::rumusFor('leading_indicator')['results'] ?? [];
        return self::matchBand(min($averagePercent, 100), $results);
    }

    /**
     * Match a numeric value to a band given the results array
     * (ordered best -> worst). Falls back to the worst band.
     */
    public static function matchBand(float $value, array $results): ?string
    {
        $value = round($value); // integer matching keeps contiguous ranges gapless
        $last = null;

        foreach ($results as $row) {
            foreach ($row as $band => $spec) {
                $last = $band;
                if (self::predicate($spec)($value)) {
                    return $band;
                }
            }
        }

        return $last;
    }

    /**
     * Turn a band spec (number, "a-b", ">=n", ">n", "<=n", "<n") into a predicate.
     */
    private static function predicate($spec): callable
    {
        if (is_int($spec) || is_float($spec)) {
            $n = (float) $spec;
            return fn($v) => $v == $n;
        }

        $s = trim((string) $spec);

        if (preg_match('/^(-?\d+(?:\.\d+)?)\s*-\s*(-?\d+(?:\.\d+)?)$/', $s, $m)) {
            $lo = (float) $m[1];
            $hi = (float) $m[2];
            return fn($v) => $v >= $lo && $v <= $hi;
        }
        if (preg_match('/^>=\s*(-?\d+(?:\.\d+)?)$/', $s, $m)) {
            $n = (float) $m[1];
            return fn($v) => $v >= $n;
        }
        if (preg_match('/^<=\s*(-?\d+(?:\.\d+)?)$/', $s, $m)) {
            $n = (float) $m[1];
            return fn($v) => $v <= $n;
        }
        if (preg_match('/^>\s*(-?\d+(?:\.\d+)?)$/', $s, $m)) {
            $n = (float) $m[1];
            return fn($v) => $v > $n;
        }
        if (preg_match('/^<\s*(-?\d+(?:\.\d+)?)$/', $s, $m)) {
            $n = (float) $m[1];
            return fn($v) => $v < $n;
        }
        if (is_numeric($s)) {
            $n = (float) $s;
            return fn($v) => $v == $n;
        }

        return fn($v) => false;
    }
}
