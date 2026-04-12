<?php

namespace DDI;

class Falsification
{
    /**
     * @return array<array{label: string, met: bool}>
     */
    public function compute(
        IndicatorResult $jobOpenings,
        IndicatorResult $sectorEmployment,
        TimeSeries $m2v,
        TimeSeries $laborShare,
        TimeSeries $mortgageDelinquency,
        TimeSeries $disposableIncome,
        TimeSeries $gdp,
        TimeSeries $debtService,
    ): array {
        // F1: AI-exposed hiring keeping pace
        $f1 = ($jobOpenings->signal == 0.0 && $sectorEmployment->signal == 0.0);

        // F3: M2V rising or stable
        $m2vLatest = $m2v->latest();
        $m2vYearAgo = $m2v->yearAgo();
        $m2vChange = ($m2vLatest && $m2vYearAgo)
            ? $m2vLatest['value'] - $m2vYearAgo['value']
            : 0;
        $f3 = ($m2vChange >= 0);

        // F4: Labor share stable or rising
        $lsLatest = $laborShare->latest();
        $lsYearAgo = $laborShare->yearAgo();
        $lsChange = ($lsLatest && $lsYearAgo)
            ? $lsLatest['value'] - $lsYearAgo['value']
            : 0;
        $f4 = ($lsChange >= 0);

        // F5: Delinquency below base rates
        $mortgageVal = $mortgageDelinquency->latest()['value'] ?? null;
        $f5 = ($mortgageVal !== null && $mortgageVal < 2.0);

        // F6: Income grows in line with GDP
        $incYoy = $disposableIncome->yoy();
        $gdpYoy = $gdp->yoy();
        $incGdpGap = ($incYoy !== null && $gdpYoy !== null) ? $gdpYoy - $incYoy : 0;
        $f6 = ($incGdpGap < 1.0);

        // F7: Debt service stable or declining
        $tdspLatest = $debtService->latest();
        $tdspYearAgo = $debtService->yearAgo();
        $tdspChange = ($tdspLatest && $tdspYearAgo)
            ? $tdspLatest['value'] - $tdspYearAgo['value']
            : 0;
        $f7 = ($tdspChange <= 0);

        return [
            ['label' => 'AI-exposed hiring keeping pace', 'met' => $f1],
            ['label' => 'M2 velocity rising or stable', 'met' => $f3],
            ['label' => 'Labor share stable or rising', 'met' => $f4],
            ['label' => 'Delinquency below base rates', 'met' => $f5],
            ['label' => 'Income grows in line with GDP', 'met' => $f6],
            ['label' => 'Debt service stable or declining', 'met' => $f7],
        ];
    }
}
