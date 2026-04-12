<?php

namespace DDI\Indicators;

use DDI\IndicatorResult;
use DDI\TimeSeries;

class ConsumerSpending
{
    public function compute(
        TimeSeries $gdp,
        TimeSeries $disposableIncome,
        TimeSeries $savingRate,
        TimeSeries $sentiment,
        TimeSeries $unemployment,
    ): IndicatorResult {
        $incYoy = $disposableIncome->yoy();
        $gdpYoy = $gdp->yoy();

        if ($incYoy === null || $gdpYoy === null) {
            return new IndicatorResult(0.0, 'N/A');
        }

        $gap = $gdpYoy - $incYoy;

        $signal = 0.0;
        if ($gap > 0) {
            if ($gap <= 1.0) $signal = 0.1;
            elseif ($gap <= 2.0) $signal = 0.3;
            elseif ($gap <= 3.0) $signal = 0.5;
            elseif ($gap <= 5.0) $signal = 0.7;
            else $signal = 1.0;
        }

        // Precautionary savings drag
        $savRate = $savingRate->latest()['value'] ?? null;
        $sent = $sentiment->latest()['value'] ?? null;
        $uRate = $unemployment->latest()['value'] ?? null;

        if ($savRate > 8.0 && $sent < 70.0 && $uRate < 5.0) {
            $signal = min(1.0, $signal * 1.2);
        }

        $detail = sprintf(
            'GDP: %+.2f%%, Disp. Income: %+.2f%%, Gap: %+.2f pp',
            $gdpYoy, $incYoy, $gap
        );

        return new IndicatorResult($signal, $detail);
    }
}
