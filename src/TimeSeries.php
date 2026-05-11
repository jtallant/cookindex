<?php

namespace DDI;

class TimeSeries
{
    /** @var array<string, float> date => value, sorted ascending */
    private array $points;

    /**
     * @param array<string, float> $points date => value pairs
     */
    public function __construct(array $points)
    {
        ksort($points);
        $this->points = $points;
    }

    /**
     * Build from FRED API observations array.
     *
     * @param array<int, array{date: string, value: string}> $observations
     */
    public static function fromFred(array $observations): self
    {
        $points = [];
        foreach ($observations as $obs) {
            if ($obs['value'] !== '.') {
                $points[$obs['date']] = (float) $obs['value'];
            }
        }
        return new self($points);
    }

    /**
     * Build from BLS API data array.
     *
     * @param array<int, array{year: string, period: string, value: string}> $data
     */
    public static function fromBls(array $data): self
    {
        $points = [];
        foreach ($data as $d) {
            if ($d['value'] === '-' || $d['value'] === '') continue;

            $period = $d['period'];
            if (str_starts_with($period, 'M')) {
                $month = (int) substr($period, 1);
                if ($month < 1 || $month > 12) continue;
            } elseif (str_starts_with($period, 'Q')) {
                $quarter = (int) substr($period, 1);
                if ($quarter < 1 || $quarter > 4) continue;
                $month = ($quarter - 1) * 3 + 1;
            } else {
                continue;
            }

            $date = sprintf('%s-%02d-01', $d['year'], $month);
            $points[$date] = (float) $d['value'];
        }
        return new self($points);
    }

    /**
     * @return array{value: float, date: string}|null
     */
    public function latest(): ?array
    {
        if (empty($this->points)) return null;

        $dates = array_keys($this->points);
        $date = end($dates);
        return ['value' => $this->points[$date], 'date' => $date];
    }

    /**
     * @param int $toleranceDays Max days from the 1-year-ago target to accept a match.
     *                           FRED (quarterly/annual) needs 180. BLS (monthly) needs 45.
     * @return array{value: float, date: string}|null
     */
    public function yearAgo(int $toleranceDays = 180): ?array
    {
        $latest = $this->latest();
        if ($latest === null) return null;

        $targetTime = strtotime($latest['date'] . ' -1 year');
        $best = null;
        $bestDiff = PHP_INT_MAX;

        foreach ($this->points as $date => $value) {
            if ($date === $latest['date']) continue;
            $diff = abs(strtotime($date) - $targetTime);
            if ($diff < $bestDiff) {
                $bestDiff = $diff;
                $best = ['value' => $value, 'date' => $date];
            }
        }

        if ($best && $bestDiff < $toleranceDays * 86400) {
            return $best;
        }
        return null;
    }

    public function yoy(int $toleranceDays = 180): ?float
    {
        $latest = $this->latest();
        $yearAgo = $this->yearAgo($toleranceDays);
        if ($latest === null || $yearAgo === null || $yearAgo['value'] == 0) return null;
        return (($latest['value'] - $yearAgo['value']) / $yearAgo['value']) * 100;
    }

    /**
     * Average of the last $n observations.
     */
    public function trailingAverage(int $n): ?float
    {
        if ($n < 1 || count($this->points) < $n) return null;
        $tail = array_slice(array_values($this->points), -$n);
        return array_sum($tail) / $n;
    }

    /**
     * Smoothed YoY: average of the last $n observations vs. the average
     * of the $n observations before that. Returns absolute difference
     * (in series units), not a percent. Use this when single-point YoY
     * is too sensitive to a noisy base period.
     */
    public function smoothedYoyDelta(int $n = 4): ?float
    {
        if (count($this->points) < $n * 2) return null;
        $values = array_values($this->points);
        $recent = array_slice($values, -$n);
        $prior = array_slice($values, -($n * 2), $n);
        return (array_sum($recent) / $n) - (array_sum($prior) / $n);
    }

    /**
     * Smoothed YoY as a percent change of the trailing $n-window average
     * vs. the prior $n-window average. Same shape as smoothedYoyDelta but
     * expressed as a percentage so series in different units can be compared.
     */
    public function smoothedYoyPercent(int $n = 4): ?float
    {
        if (count($this->points) < $n * 2) return null;
        $values = array_values($this->points);
        $recent = array_sum(array_slice($values, -$n)) / $n;
        $prior = array_sum(array_slice($values, -($n * 2), $n)) / $n;
        if ($prior == 0) return null;
        return (($recent - $prior) / $prior) * 100;
    }

    public function findClosest(string $targetDate): ?float
    {
        if (isset($this->points[$targetDate])) {
            return $this->points[$targetDate];
        }

        $target = strtotime($targetDate);
        $best = null;
        $bestDiff = PHP_INT_MAX;

        foreach ($this->points as $date => $value) {
            $diff = abs(strtotime($date) - $target);
            if ($diff < $bestDiff) {
                $bestDiff = $diff;
                $best = $value;
            }
        }

        return ($best !== null && $bestDiff < 90 * 86400) ? $best : null;
    }

    public function hasData(): bool
    {
        return !empty($this->points);
    }

    /**
     * @return array<string, float>
     */
    public function points(): array
    {
        return $this->points;
    }
}
