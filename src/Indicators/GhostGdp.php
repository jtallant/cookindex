<?php

namespace DDI\Indicators;

use DDI\IndicatorResult;
use DDI\TimeSeries;

class GhostGdp
{
    /**
     * @param TimeSeries $gdp Real GDP (GDPC1)
     * @param TimeSeries $income Real Personal Income ex-transfers (W875RX1)
     * @param TimeSeries $referenceQuarter Series to align quarterly dates against (typically M2V)
     */
    public function compute(TimeSeries $gdp, TimeSeries $income, TimeSeries $referenceQuarter): IndicatorResult
    {
        $latestDate = $referenceQuarter->latest()['date'] ?? null;
        $yearAgoDate = $referenceQuarter->yearAgo()['date'] ?? null;

        if ($latestDate === null || $yearAgoDate === null) {
            return new IndicatorResult(0.0, 'N/A');
        }

        $gdpLatest = $gdp->findClosest($latestDate);
        $gdpYearAgo = $gdp->findClosest($yearAgoDate);
        $incLatest = $income->findClosest($latestDate);
        $incYearAgo = $income->findClosest($yearAgoDate);

        if (!$gdpLatest || !$gdpYearAgo || !$incLatest || !$incYearAgo) {
            return new IndicatorResult(0.0, 'N/A');
        }

        $gdpYoy = (($gdpLatest - $gdpYearAgo) / $gdpYearAgo) * 100;
        $incYoy = (($incLatest - $incYearAgo) / $incYearAgo) * 100;
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
