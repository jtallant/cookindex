<?php

namespace DDI\Indicators;

use DDI\IndicatorResult;
use DDI\TimeSeries;

class LaborShare
{
    private const HISTORICAL_RATE = -0.2;

    public function compute(TimeSeries $laborShare): IndicatorResult
    {
        $latest = $laborShare->latest();
        $yearAgo = $laborShare->yearAgo();

        if ($latest === null || $yearAgo === null) {
            return new IndicatorResult(0.0, 'N/A');
        }

        $yoyChange = $latest['value'] - $yearAgo['value'];
        $acceleration = $yoyChange - self::HISTORICAL_RATE;

        $signal = 0.0;
        if ($acceleration < 0) {
            $excess = abs($acceleration);
            if ($excess <= 0.2) $signal = 0.2;
            elseif ($excess <= 0.5) $signal = 0.4;
            elseif ($excess <= 1.0) $signal = 0.7;
            else $signal = 1.0;
        }

        $detail = sprintf(
            '%.3f → %.3f (YoY: %+.3f, accel: %+.3f beyond baseline)',
            $yearAgo['value'], $latest['value'], $yoyChange, $acceleration
        );

        return new IndicatorResult($signal, $detail);
    }
}
