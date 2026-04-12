<?php

namespace DDI\Tests\Indicators;

use DDI\Indicators\FinancialContagion;
use DDI\TimeSeries;
use PHPUnit\Framework\TestCase;

class FinancialContagionTest extends TestCase
{
    private FinancialContagion $indicator;

    protected function setUp(): void
    {
        $this->indicator = new FinancialContagion;
    }

    private function makeSeries(float $from, float $to): TimeSeries
    {
        return new TimeSeries([
            '2023-07-01' => $from,
            '2024-07-01' => $to,
        ]);
    }

    public function testNoSignalWhenDelinquencyLowAndDebtServiceLow(): void
    {
        $mortgage = $this->makeSeries(1.5, 1.5);      // below 2.0
        $cc = $this->makeSeries(2.0, 2.0);
        $debtService = $this->makeSeries(9.0, 9.5);   // below 10.0
        $comp = $this->makeSeries(60.0, 60.0);
        $unemployment = $this->makeSeries(6.0, 6.0);   // × 1.0

        $result = $this->indicator->compute($mortgage, $cc, $debtService, $comp, $unemployment);
        $this->assertEquals(0.0, $result->signal);
    }

    public function testMortgageDelinquencyDrivesSignal(): void
    {
        $mortgage = $this->makeSeries(2.5, 3.0);      // 3.0 => mortgageSignal 0.2
        $cc = $this->makeSeries(2.0, 2.0);             // no cc boost
        $debtService = $this->makeSeries(9.0, 9.5);   // below 10.0 => 0.0
        $comp = $this->makeSeries(60.0, 60.0);
        $unemployment = $this->makeSeries(6.0, 6.0);   // × 1.0

        // raw = (0.2 * 0.5) + (0.0 * 0.5) = 0.1, × 1.0 = 0.1
        $result = $this->indicator->compute($mortgage, $cc, $debtService, $comp, $unemployment);
        $this->assertEqualsWithDelta(0.1, $result->signal, 0.01);
    }

    public function testDebtServiceDrivesSignal(): void
    {
        $mortgage = $this->makeSeries(1.5, 1.5);      // below 2.0 => 0.0
        $cc = $this->makeSeries(2.0, 2.0);
        $debtService = $this->makeSeries(11.0, 11.5);  // 11.5 => 0.4 (falls in <= 12.0 bucket)
        $comp = $this->makeSeries(60.0, 60.0);
        $unemployment = $this->makeSeries(6.0, 6.0);   // × 1.0

        // raw = (0.0 * 0.5) + (0.4 * 0.5) = 0.2, × 1.0 = 0.2
        $result = $this->indicator->compute($mortgage, $cc, $debtService, $comp, $unemployment);
        $this->assertEqualsWithDelta(0.2, $result->signal, 0.01);
    }

    public function testLowUnemploymentAmplifies(): void
    {
        $mortgage = $this->makeSeries(3.0, 3.5);      // 3.5 => 0.5
        $cc = $this->makeSeries(2.0, 2.0);
        $debtService = $this->makeSeries(9.0, 9.5);   // below 10.0 => 0.0
        $comp = $this->makeSeries(60.0, 60.0);
        $unemployment = $this->makeSeries(3.5, 3.5);   // < 4.0 => × 1.5

        // raw = (0.5 * 0.5) + (0.0 * 0.5) = 0.25, × 1.5 = 0.375
        $result = $this->indicator->compute($mortgage, $cc, $debtService, $comp, $unemployment);
        $this->assertEqualsWithDelta(0.375, $result->signal, 0.01);
    }

    public function testHighUnemploymentDampens(): void
    {
        $mortgage = $this->makeSeries(3.0, 3.5);      // 3.5 => 0.5
        $cc = $this->makeSeries(2.0, 2.0);
        $debtService = $this->makeSeries(9.0, 9.5);
        $comp = $this->makeSeries(60.0, 60.0);
        $unemployment = $this->makeSeries(7.0, 7.0);   // > 6.0 => × 0.7

        // raw = 0.25, × 0.7 = 0.175
        $result = $this->indicator->compute($mortgage, $cc, $debtService, $comp, $unemployment);
        $this->assertEqualsWithDelta(0.175, $result->signal, 0.01);
    }

    public function testCreditCardBoostApplies(): void
    {
        $mortgage = $this->makeSeries(2.5, 3.0);      // 3.0 => 0.2
        $cc = $this->makeSeries(2.0, 3.0);             // cc change +1.0
        // mortgage change: +0.5 — cc rising faster => 1.2x boost
        $debtService = $this->makeSeries(9.0, 9.5);
        $comp = $this->makeSeries(60.0, 60.0);
        $unemployment = $this->makeSeries(6.0, 6.0);   // × 1.0

        // mortgageSignal 0.2 × ccBoost 1.2 = 0.24
        // raw = (0.24 * 0.5) + (0.0 * 0.5) = 0.12
        $result = $this->indicator->compute($mortgage, $cc, $debtService, $comp, $unemployment);
        $this->assertEqualsWithDelta(0.12, $result->signal, 0.01);
    }

    public function testDebtCompBoostWhenDebtRisesAndCompFalls(): void
    {
        $mortgage = $this->makeSeries(1.5, 1.5);      // below 2.0 => 0.0
        $cc = $this->makeSeries(2.0, 2.0);
        $debtService = $this->makeSeries(10.5, 11.5);  // rising, 11.5 => 0.4 (falls in <= 12.0 bucket)
        $comp = $this->makeSeries(62.0, 60.0);          // falling
        $unemployment = $this->makeSeries(6.0, 6.0);   // × 1.0

        // debtService 0.4 × 1.5 boost = 0.6
        // raw = (0.0 * 0.5) + (0.6 * 0.5) = 0.3
        $result = $this->indicator->compute($mortgage, $cc, $debtService, $comp, $unemployment);
        $this->assertEqualsWithDelta(0.3, $result->signal, 0.01);
    }
}
