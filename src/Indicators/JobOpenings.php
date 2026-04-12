<?php

namespace DDI\Indicators;

use DDI\IndicatorResult;
use DDI\TimeSeries;

class JobOpenings
{
    /**
     * @param TimeSeries $control Total nonfarm job openings
     * @param TimeSeries[] $exposed AI-exposed sector job openings
     */
    public function compute(TimeSeries $control, array $exposed): IndicatorResult
    {
        $controlLatest = $control->latest();
        if ($controlLatest === null) {
            return new IndicatorResult(0.0, 'N/A');
        }

        $controlYoy = $control->yoy(45);
        if ($controlYoy === null) {
            return new IndicatorResult(0.0, 'N/A');
        }

        $sumLatest = 0;
        $sumYearAgo = 0;

        foreach ($exposed as $series) {
            $latest = $series->latest();
            $yearAgo = $series->yearAgo(45);
            if ($latest === null || $yearAgo === null) {
                return new IndicatorResult(0.0, 'N/A');
            }
            $sumLatest += $latest['value'];
            $sumYearAgo += $yearAgo['value'];
        }

        if ($sumYearAgo == 0) {
            return new IndicatorResult(0.0, 'N/A');
        }

        $exposedYoy = (($sumLatest - $sumYearAgo) / $sumYearAgo) * 100;
        $divergence = $controlYoy - $exposedYoy;

        $signal = 0.0;
        if ($divergence > 0) {
            if ($divergence <= 1.0) $signal = 0.1;
            elseif ($divergence <= 3.0) $signal = 0.3;
            elseif ($divergence <= 5.0) $signal = 0.5;
            elseif ($divergence <= 10.0) $signal = 0.7;
            elseif ($divergence <= 20.0) $signal = 0.9;
            else $signal = 1.0;
        }

        $detail = sprintf(
            'Control: %+.2f%%, Exposed: %+.2f%%, Gap: %+.2f pp',
            $controlYoy, $exposedYoy, $divergence
        );

        return new IndicatorResult($signal, $detail);
    }
}
