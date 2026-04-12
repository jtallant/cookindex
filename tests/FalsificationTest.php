<?php

namespace DDI\Tests;

use DDI\Falsification;
use DDI\IndicatorResult;
use DDI\TimeSeries;
use PHPUnit\Framework\TestCase;

class FalsificationTest extends TestCase
{
    private Falsification $falsification;

    protected function setUp(): void
    {
        $this->falsification = new Falsification;
    }

    private function makeSeries(float $from, float $to): TimeSeries
    {
        return new TimeSeries([
            '2023-07-01' => $from,
            '2024-07-01' => $to,
        ]);
    }

    public function testHiringKeepingPaceMetWhenBothZero(): void
    {
        $result = $this->falsification->compute(
            new IndicatorResult(0.0, ''),
            new IndicatorResult(0.0, ''),
            $this->makeSeries(1.40, 1.41),
            $this->makeSeries(97.0, 97.1),
            $this->makeSeries(1.5, 1.5),
            $this->makeSeries(15000.0, 15300.0),
            $this->makeSeries(20000.0, 20400.0),
            $this->makeSeries(9.5, 9.5),
        );

        $this->assertTrue($result[0]['met']); // AI-exposed hiring keeping pace
    }

    public function testHiringKeepingPaceNotMetWhenDisplacement(): void
    {
        $result = $this->falsification->compute(
            new IndicatorResult(0.5, ''),  // job openings showing signal
            new IndicatorResult(0.0, ''),
            $this->makeSeries(1.40, 1.41),
            $this->makeSeries(97.0, 97.1),
            $this->makeSeries(1.5, 1.5),
            $this->makeSeries(15000.0, 15300.0),
            $this->makeSeries(20000.0, 20400.0),
            $this->makeSeries(9.5, 9.5),
        );

        $this->assertFalse($result[0]['met']);
    }

    public function testM2VelocityMetWhenRising(): void
    {
        $result = $this->falsification->compute(
            new IndicatorResult(0.0, ''),
            new IndicatorResult(0.0, ''),
            $this->makeSeries(1.40, 1.45),  // rising
            $this->makeSeries(97.0, 97.1),
            $this->makeSeries(1.5, 1.5),
            $this->makeSeries(15000.0, 15300.0),
            $this->makeSeries(20000.0, 20400.0),
            $this->makeSeries(9.5, 9.5),
        );

        $this->assertTrue($result[1]['met']); // M2 velocity rising or stable
    }

    public function testM2VelocityNotMetWhenFalling(): void
    {
        $result = $this->falsification->compute(
            new IndicatorResult(0.0, ''),
            new IndicatorResult(0.0, ''),
            $this->makeSeries(1.45, 1.40),  // falling
            $this->makeSeries(97.0, 97.1),
            $this->makeSeries(1.5, 1.5),
            $this->makeSeries(15000.0, 15300.0),
            $this->makeSeries(20000.0, 20400.0),
            $this->makeSeries(9.5, 9.5),
        );

        $this->assertFalse($result[1]['met']);
    }

    public function testLaborShareMetWhenRising(): void
    {
        $result = $this->falsification->compute(
            new IndicatorResult(0.0, ''),
            new IndicatorResult(0.0, ''),
            $this->makeSeries(1.40, 1.41),
            $this->makeSeries(97.0, 97.5),  // rising
            $this->makeSeries(1.5, 1.5),
            $this->makeSeries(15000.0, 15300.0),
            $this->makeSeries(20000.0, 20400.0),
            $this->makeSeries(9.5, 9.5),
        );

        $this->assertTrue($result[2]['met']); // Labor share stable or rising
    }

    public function testLaborShareNotMetWhenFalling(): void
    {
        $result = $this->falsification->compute(
            new IndicatorResult(0.0, ''),
            new IndicatorResult(0.0, ''),
            $this->makeSeries(1.40, 1.41),
            $this->makeSeries(97.0, 96.5),  // falling
            $this->makeSeries(1.5, 1.5),
            $this->makeSeries(15000.0, 15300.0),
            $this->makeSeries(20000.0, 20400.0),
            $this->makeSeries(9.5, 9.5),
        );

        $this->assertFalse($result[2]['met']);
    }

    public function testDelinquencyMetWhenBelowBaseRate(): void
    {
        $result = $this->falsification->compute(
            new IndicatorResult(0.0, ''),
            new IndicatorResult(0.0, ''),
            $this->makeSeries(1.40, 1.41),
            $this->makeSeries(97.0, 97.1),
            $this->makeSeries(1.5, 1.8),  // below 2.0
            $this->makeSeries(15000.0, 15300.0),
            $this->makeSeries(20000.0, 20400.0),
            $this->makeSeries(9.5, 9.5),
        );

        $this->assertTrue($result[3]['met']); // Delinquency below base rates
    }

    public function testDelinquencyNotMetWhenAboveBaseRate(): void
    {
        $result = $this->falsification->compute(
            new IndicatorResult(0.0, ''),
            new IndicatorResult(0.0, ''),
            $this->makeSeries(1.40, 1.41),
            $this->makeSeries(97.0, 97.1),
            $this->makeSeries(1.5, 2.5),  // above 2.0
            $this->makeSeries(15000.0, 15300.0),
            $this->makeSeries(20000.0, 20400.0),
            $this->makeSeries(9.5, 9.5),
        );

        $this->assertFalse($result[3]['met']);
    }

    public function testIncomeGdpMetWhenGapSmall(): void
    {
        $result = $this->falsification->compute(
            new IndicatorResult(0.0, ''),
            new IndicatorResult(0.0, ''),
            $this->makeSeries(1.40, 1.41),
            $this->makeSeries(97.0, 97.1),
            $this->makeSeries(1.5, 1.5),
            $this->makeSeries(15000.0, 15300.0),  // +2%
            $this->makeSeries(20000.0, 20400.0),  // +2%
            $this->makeSeries(9.5, 9.5),
        );

        $this->assertTrue($result[4]['met']); // Income grows in line with GDP
    }

    public function testDebtServiceMetWhenDeclining(): void
    {
        $result = $this->falsification->compute(
            new IndicatorResult(0.0, ''),
            new IndicatorResult(0.0, ''),
            $this->makeSeries(1.40, 1.41),
            $this->makeSeries(97.0, 97.1),
            $this->makeSeries(1.5, 1.5),
            $this->makeSeries(15000.0, 15300.0),
            $this->makeSeries(20000.0, 20400.0),
            $this->makeSeries(10.0, 9.5),  // declining
        );

        $this->assertTrue($result[5]['met']); // Debt service stable or declining
    }

    public function testDebtServiceNotMetWhenRising(): void
    {
        $result = $this->falsification->compute(
            new IndicatorResult(0.0, ''),
            new IndicatorResult(0.0, ''),
            $this->makeSeries(1.40, 1.41),
            $this->makeSeries(97.0, 97.1),
            $this->makeSeries(1.5, 1.5),
            $this->makeSeries(15000.0, 15300.0),
            $this->makeSeries(20000.0, 20400.0),
            $this->makeSeries(9.5, 10.0),  // rising
        );

        $this->assertFalse($result[5]['met']);
    }

    public function testAllConditionsMetInHealthyEconomy(): void
    {
        $result = $this->falsification->compute(
            new IndicatorResult(0.0, ''),     // no displacement
            new IndicatorResult(0.0, ''),     // no displacement
            $this->makeSeries(1.40, 1.45),    // M2V rising
            $this->makeSeries(97.0, 97.5),    // labor share rising
            $this->makeSeries(1.5, 1.5),      // delinquency low
            $this->makeSeries(15000.0, 15300.0), // income +2%
            $this->makeSeries(20000.0, 20400.0), // GDP +2%
            $this->makeSeries(10.0, 9.8),     // debt service declining
        );

        $metCount = count(array_filter($result, fn($f) => $f['met']));
        $this->assertEquals(6, $metCount);
    }
}
