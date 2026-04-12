<?php

namespace DDI\Tests\Indicators;

use DDI\Indicators\SectorEmployment;
use DDI\TimeSeries;
use PHPUnit\Framework\TestCase;

class SectorEmploymentTest extends TestCase
{
    private SectorEmployment $indicator;

    protected function setUp(): void
    {
        $this->indicator = new SectorEmployment;
    }

    public function testNoSignalWhenExposedGrowsFasterThanControl(): void
    {
        $control = new TimeSeries([
            '2023-07-01' => 10000.0,
            '2024-07-01' => 10100.0, // +1%
        ]);
        $exposed = [new TimeSeries([
            '2023-07-01' => 5000.0,
            '2024-07-01' => 5150.0, // +3%
        ])];

        $result = $this->indicator->compute($control, $exposed);
        $this->assertEquals(0.0, $result->signal);
    }

    public function testSignalWhenExposedShrinks(): void
    {
        $control = new TimeSeries([
            '2023-07-01' => 10000.0,
            '2024-07-01' => 10200.0, // +2%
        ]);
        $exposed = [new TimeSeries([
            '2023-07-01' => 5000.0,
            '2024-07-01' => 4900.0, // -2%
        ])];

        // Gap: 2% - (-2%) = 4.0 pp => signal 0.5
        $result = $this->indicator->compute($control, $exposed);
        $this->assertEqualsWithDelta(0.5, $result->signal, 0.01);
    }

    public function testReturnsNAWithInsufficientData(): void
    {
        $control = new TimeSeries(['2024-07-01' => 10000.0]);
        $exposed = [new TimeSeries(['2024-07-01' => 5000.0])];

        $result = $this->indicator->compute($control, $exposed);
        $this->assertEquals(0.0, $result->signal);
        $this->assertEquals('N/A', $result->detail);
    }
}
