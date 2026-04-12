<?php

namespace DDI\Tests\Indicators;

use DDI\Indicators\M2Velocity;
use DDI\TimeSeries;
use PHPUnit\Framework\TestCase;

class M2VelocityTest extends TestCase
{
    private M2Velocity $indicator;

    protected function setUp(): void
    {
        $this->indicator = new M2Velocity;
    }

    public function testNoSignalWhenVelocityRising(): void
    {
        $series = new TimeSeries([
            '2023-07-01' => 1.40,
            '2024-07-01' => 1.45,
        ]);

        $result = $this->indicator->compute($series);
        $this->assertEquals(0.0, $result->signal);
    }

    public function testNoSignalWhenDecliningAtHistoricalRate(): void
    {
        // Historical rate is -0.03. Declining at -0.02 (slower) = no signal.
        $series = new TimeSeries([
            '2023-07-01' => 1.40,
            '2024-07-01' => 1.38,
        ]);

        $result = $this->indicator->compute($series);
        $this->assertEquals(0.0, $result->signal);
    }

    public function testWeakSignalWhenDecliningFasterThanBaseline(): void
    {
        // Change: -0.05, historical: -0.03, acceleration: -0.02 => signal 0.2
        $series = new TimeSeries([
            '2023-07-01' => 1.40,
            '2024-07-01' => 1.35,
        ]);

        $result = $this->indicator->compute($series);
        $this->assertEquals(0.2, $result->signal);
    }

    public function testStrongSignalWithLargeDrop(): void
    {
        // Change: -0.13, historical: -0.03, acceleration: -0.10 => signal 0.7
        $series = new TimeSeries([
            '2023-07-01' => 1.40,
            '2024-07-01' => 1.27,
        ]);

        $result = $this->indicator->compute($series);
        $this->assertEquals(0.7, $result->signal);
    }

    public function testCriticalSignalWithSevereCollapse(): void
    {
        // Change: -0.20, historical: -0.03, acceleration: -0.17 => signal 1.0
        $series = new TimeSeries([
            '2023-07-01' => 1.40,
            '2024-07-01' => 1.20,
        ]);

        $result = $this->indicator->compute($series);
        $this->assertEquals(1.0, $result->signal);
    }

    public function testReturnsNAWithInsufficientData(): void
    {
        $series = new TimeSeries(['2024-07-01' => 1.40]);

        $result = $this->indicator->compute($series);
        $this->assertEquals(0.0, $result->signal);
        $this->assertEquals('N/A', $result->detail);
    }
}
