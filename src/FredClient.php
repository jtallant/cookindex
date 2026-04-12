<?php

namespace DDI;

class FredClient
{
    private string $apiKey;

    public function __construct(string $apiKey)
    {
        $this->apiKey = $apiKey;
    }

    public function fetch(string $seriesId, int $limit = 20): ?TimeSeries
    {
        $url = sprintf(
            'https://api.stlouisfed.org/fred/series/observations?series_id=%s&api_key=%s&file_type=json&sort_order=desc&limit=%d',
            $seriesId,
            $this->apiKey,
            $limit
        );

        $response = @file_get_contents($url);
        if ($response === false) return null;

        $parsed = json_decode($response, true);
        if (!isset($parsed['observations'])) return null;

        return TimeSeries::fromFred($parsed['observations']);
    }

    /**
     * @param array<string, int> $seriesIds series ID => observation limit
     * @return array<string, TimeSeries>
     */
    public function fetchMany(array $seriesIds): array
    {
        $result = [];
        foreach ($seriesIds as $id => $limit) {
            $series = $this->fetch($id, $limit);
            if ($series !== null) {
                $result[$id] = $series;
            }
        }
        return $result;
    }
}
