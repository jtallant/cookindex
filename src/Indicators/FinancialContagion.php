<?php

namespace DDI\Indicators;

use DDI\IndicatorResult;
use DDI\TimeSeries;

class FinancialContagion
{
    public function compute(
        TimeSeries $mortgageDelinquency,
        TimeSeries $creditCardDelinquency,
        TimeSeries $debtService,
        TimeSeries $compensation,
        TimeSeries $unemployment,
    ): IndicatorResult {
        // Delinquency sub-signal
        $mortgage = $mortgageDelinquency->latest();
        $mortgageSignal = 0.0;
        if ($mortgage !== null) {
            $v = $mortgage['value'];
            if ($v < 2.0) $mortgageSignal = 0.0;
            elseif ($v <= 3.0) $mortgageSignal = 0.2;
            elseif ($v <= 4.0) $mortgageSignal = 0.5;
            elseif ($v <= 6.0) $mortgageSignal = 0.7;
            else $mortgageSignal = 1.0;
        }

        // Credit card leading indicator
        $ccBoost = 1.0;
        $ccLatest = $creditCardDelinquency->latest();
        $ccYearAgo = $creditCardDelinquency->yearAgo();
        $mortYearAgo = $mortgageDelinquency->yearAgo();

        if ($ccLatest && $ccYearAgo && $mortgage && $mortYearAgo) {
            $ccChange = $ccLatest['value'] - $ccYearAgo['value'];
            $mortChange = $mortgage['value'] - $mortYearAgo['value'];
            if ($ccChange > $mortChange) $ccBoost = 1.2;
        }

        $delinquencySignal = min(1.0, $mortgageSignal * $ccBoost);

        // Debt service sub-signal
        $tdsp = $debtService->latest();
        $debtServiceSignal = 0.0;
        if ($tdsp !== null) {
            $v = $tdsp['value'];
            if ($v < 10.0) $debtServiceSignal = 0.0;
            elseif ($v <= 11.0) $debtServiceSignal = 0.2;
            elseif ($v <= 12.0) $debtServiceSignal = 0.4;
            elseif ($v <= 13.0) $debtServiceSignal = 0.6;
            elseif ($v <= 14.0) $debtServiceSignal = 0.8;
            else $debtServiceSignal = 1.0;
        }

        // Debt-comp boost
        $tdspYearAgo = $debtService->yearAgo();
        $compLatest = $compensation->latest();
        $compYearAgo = $compensation->yearAgo();

        if ($tdsp && $tdspYearAgo && $compLatest && $compYearAgo) {
            if ($tdsp['value'] > $tdspYearAgo['value'] && $compLatest['value'] < $compYearAgo['value']) {
                $debtServiceSignal = min(1.0, $debtServiceSignal * 1.5);
            }
        }

        // Combined
        $rawContagion = ($delinquencySignal * 0.5) + ($debtServiceSignal * 0.5);

        // Unemployment context multiplier
        $uRate = $unemployment->latest()['value'] ?? null;
        $uMultiplier = 1.0;
        if ($uRate !== null) {
            if ($uRate < 4.0) $uMultiplier = 1.5;
            elseif ($uRate <= 5.0) $uMultiplier = 1.3;
            elseif ($uRate <= 6.0) $uMultiplier = 1.0;
            else $uMultiplier = 0.7;
        }

        $signal = min(1.0, $rawContagion * $uMultiplier);

        $detail = sprintf(
            'Delinq: %.2f, Debt Svc: %.2f, Unemp: %.1f%% (×%.1f)',
            $delinquencySignal, $debtServiceSignal, $uRate ?? 0, $uMultiplier
        );

        return new IndicatorResult($signal, $detail);
    }
}
