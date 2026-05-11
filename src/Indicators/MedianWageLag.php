<?php

namespace DDI\Indicators;

use DDI\IndicatorResult;
use DDI\TimeSeries;

class MedianWageLag
{
    private const QUARTERLY_WINDOW = 4;
    private const MONTHLY_WINDOW = 12;

    /**
     * @param TimeSeries $medianWages BLS LES1252882800 — quarterly, constant 1982-84 dollars
     * @param TimeSeries $aggregateIncome FRED W875RX1 — monthly, real personal income ex-transfers
     */
    public function compute(TimeSeries $medianWages, TimeSeries $aggregateIncome): IndicatorResult
    {
        $medianYoy = $medianWages->smoothedYoyPercent(self::QUARTERLY_WINDOW);
        $aggregateYoy = $aggregateIncome->smoothedYoyPercent(self::MONTHLY_WINDOW);

        if ($medianYoy === null || $aggregateYoy === null) {
            return new IndicatorResult(0.0, 'N/A');
        }

        $gap = $aggregateYoy - $medianYoy;

        $signal = 0.0;
        if ($gap > 0) {
            if ($gap < 0.5) $signal = 0.0;
            elseif ($gap <= 1.0) $signal = 0.2;
            elseif ($gap <= 1.5) $signal = 0.4;
            elseif ($gap <= 2.5) $signal = 0.7;
            else $signal = 1.0;
        }

        $detail = sprintf(
            'Aggregate: %+.2f%%, Median wage: %+.2f%%, Gap: %+.2f pp',
            $aggregateYoy, $medianYoy, $gap
        );

        return new IndicatorResult($signal, $detail);
    }
}
