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
            $month = (int) str_replace('M', '', $d['period']);
            if ($month < 1 || $month > 12) continue;
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
