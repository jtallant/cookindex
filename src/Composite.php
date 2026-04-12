<?php

namespace DDI;

class Composite
{
    /**
     * @param array<array{name: string, weight: float, result: IndicatorResult}> $indicators
     * @return array{score: float, interpretation: string, activeCount: int, convergenceMultiplier: float}
     */
    public function compute(array $indicators): array
    {
        $score = 0;
        $totalWeight = 0;
        $activeCount = 0;

        foreach ($indicators as $ind) {
            $score += $ind['result']->signal * $ind['weight'];
            $totalWeight += $ind['weight'];
            if ($ind['result']->signal >= 0.3) $activeCount++;
        }

        $ddi = ($totalWeight > 0) ? ($score / $totalWeight) * 100 : 0;

        $convergenceMultiplier = 1.0;
        if ($activeCount >= 4) {
            $convergenceMultiplier = 1.0 + (0.05 * ($activeCount - 3));
            $ddi = min(100, $ddi * $convergenceMultiplier);
        }

        $interpretation = match (true) {
            $ddi < 20 => 'No signal — thesis not supported by current data',
            $ddi < 40 => 'Noise range — some movement but within historical norms',
            $ddi < 60 => 'Emerging signal — multiple indicators crossing thresholds',
            $ddi < 80 => 'Strong signal — pattern consistent with paper\'s predictions',
            default   => 'Crisis-level — displacement spiral appears to be activating',
        };

        return [
            'score' => $ddi,
            'interpretation' => $interpretation,
            'activeCount' => $activeCount,
            'convergenceMultiplier' => $convergenceMultiplier,
        ];
    }
}
