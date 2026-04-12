<?php

namespace DDI\Tests\Indicators;

use DDI\Indicators\JobOpenings;
use DDI\TimeSeries;
use PHPUnit\Framework\TestCase;

class JobOpeningsTest extends TestCase
{
    private JobOpenings $indicator;

    protected function setUp(): void
    {
        $this->indicator = new JobOpenings;
    }

    public function testNoSignalWhenExposedOutpacesControl(): void
    {
        $control = new TimeSeries([
            '2023-07-01' => 1000.0,
            '2024-07-01' => 1020.0, // +2%
        ]);
        $exposed = [new TimeSeries([
            '2023-07-01' => 500.0,
            '2024-07-01' => 525.0, // +5%
        ])];

        $result = $this->indicator->compute($control, $exposed);
        $this->assertEquals(0.0, $result->signal);
    }

    public function testSmallSignalWhenControlLeadsSlightly(): void
    {
        $control = new TimeSeries([
            '2023-07-01' => 1000.0,
            '2024-07-01' => 1020.0, // +2%
        ]);
        $exposed = [new TimeSeries([
            '2023-07-01' => 500.0,
            '2024-07-01' => 505.0, // +1%
        ])];

        // Gap: 2% - 1% = 1.0 pp => signal 0.1
        $result = $this->indicator->compute($control, $exposed);
        $this->assertEquals(0.1, $result->signal);
    }

    public function testModerateSignalWithLargerGap(): void
    {
        $control = new TimeSeries([
            '2023-07-01' => 1000.0,
            '2024-07-01' => 1050.0, // +5%
        ]);
        $exposed = [new TimeSeries([
            '2023-07-01' => 500.0,
            '2024-07-01' => 500.0, // 0%
        ])];

        // Gap: 5% - 0% = 5.0 pp => signal 0.5
        $result = $this->indicator->compute($control, $exposed);
        $this->assertEquals(0.5, $result->signal);
    }

    public function testAggregatesMultipleExposedSeries(): void
    {
        $control = new TimeSeries([
            '2023-07-01' => 1000.0,
            '2024-07-01' => 1050.0, // +5%
        ]);
        $exposedA = new TimeSeries([
            '2023-07-01' => 300.0,
            '2024-07-01' => 300.0, // 0%
        ]);
        $exposedB = new TimeSeries([
            '2023-07-01' => 200.0,
            '2024-07-01' => 200.0, // 0%
        ]);

        // Combined exposed: 500 -> 500, 0% YoY. Gap: 5pp => signal 0.5
        $result = $this->indicator->compute($control, [$exposedA, $exposedB]);
        $this->assertEquals(0.5, $result->signal);
    }

    public function testReturnsNAWithInsufficientData(): void
    {
        $control = new TimeSeries(['2024-07-01' => 1000.0]);
        $exposed = [new TimeSeries(['2024-07-01' => 500.0])];

        $result = $this->indicator->compute($control, $exposed);
        $this->assertEquals(0.0, $result->signal);
        $this->assertEquals('N/A', $result->detail);
    }
}
