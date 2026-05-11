<?php

namespace DDI\Indicators;

use DDI\IndicatorResult;
use DDI\TimeSeries;

class GhostGdp
{
    private const QUARTERLY_WINDOW = 4;
    private const MONTHLY_WINDOW = 12;

    /**
     * @param TimeSeries $gdp Real GDP (GDPC1, quarterly)
     * @param TimeSeries $income Real Personal Income ex-transfers (W875RX1, monthly)
     */
    public function compute(TimeSeries $gdp, TimeSeries $income): IndicatorResult
    {
        $gdpYoy = $gdp->smoothedYoyPercent(self::QUARTERLY_WINDOW);
        $incYoy = $income->smoothedYoyPercent(self::MONTHLY_WINDOW);

        if ($gdpYoy === null || $incYoy === null) {
            return new IndicatorResult(0.0, 'N/A');
        }

        $gap = $gdpYoy - $incYoy;

        $signal = 0.0;
        if ($gap > 0) {
            if ($gap < 0.5) $signal = 0.0;
            elseif ($gap <= 1.0) $signal = 0.2;
            elseif ($gap <= 1.5) $signal = 0.4;
            elseif ($gap <= 2.5) $signal = 0.7;
            else $signal = 1.0;
        }

        $detail = sprintf(
            'GDP: %+.2f%%, Income: %+.2f%%, Gap: %+.2f pp',
            $gdpYoy, $incYoy, $gap
        );

        return new IndicatorResult($signal, $detail);
    }
}
