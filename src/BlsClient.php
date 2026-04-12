<?php

namespace DDI;

class BlsClient
{
    private string $apiKey;

    public function __construct(string $apiKey)
    {
        $this->apiKey = $apiKey;
    }

    /**
     * @param string[] $seriesIds
     * @return array<string, TimeSeries>
     */
    public function fetch(array $seriesIds): array
    {
        $currentYear = (int) date('Y');

        $payload = json_encode([
            'seriesid' => $seriesIds,
            'startyear' => (string) ($currentYear - 2),
            'endyear' => (string) $currentYear,
            'registrationkey' => $this->apiKey,
        ]);

        $context = stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => "Content-Type: application/json\r\n",
                'content' => $payload,
            ],
        ]);

        $response = @file_get_contents('https://api.bls.gov/publicAPI/v2/timeseries/data/', false, $context);
        if ($response === false) return [];

        $parsed = json_decode($response, true);
        if (!isset($parsed['Results']['series'])) return [];

        $result = [];
        foreach ($parsed['Results']['series'] as $s) {
            $result[$s['seriesID']] = TimeSeries::fromBls($s['data']);
        }

        return $result;
    }
}
