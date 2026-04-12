<?php

namespace DDI\Tests\Indicators;

use DDI\Indicators\LaborShare;
use DDI\TimeSeries;
use PHPUnit\Framework\TestCase;

class LaborShareTest extends TestCase
{
    private LaborShare $indicator;

    protected function setUp(): void
    {
        $this->indicator = new LaborShare;
    }

    public function testNoSignalWhenDecliningSlowerThanBaseline(): void
    {
        // Historical rate is -0.2/year. Declining slower = no signal.
        $series = new TimeSeries([
            '2023-07-01' => 97.0,
            '2024-07-01' => 96.9,  // -0.1, slower than -0.2 baseline
        ]);

        $result = $this->indicator->compute($series);
        $this->assertEquals(0.0, $result->signal);
    }

    public function testWeakSignalWhenSlightlyFasterThanBaseline(): void
    {
        // -0.3 change = -0.1 acceleration beyond -0.2 baseline
        $series = new TimeSeries([
            '2023-07-01' => 97.0,
            '2024-07-01' => 96.7,
        ]);

        $result = $this->indicator->compute($series);
        $this->assertEquals(0.2, $result->signal);
    }

    public function testStrongSignalWhenAcceleratingSignificantly(): void
    {
        // -1.0 change = -0.8 acceleration beyond baseline
        $series = new TimeSeries([
            '2023-07-01' => 97.0,
            '2024-07-01' => 96.0,
        ]);

        $result = $this->indicator->compute($series);
        $this->assertEquals(0.7, $result->signal);
    }

    public function testCriticalSignalWhenDroppingFast(): void
    {
        // -1.5 change = -1.3 acceleration beyond baseline
        $series = new TimeSeries([
            '2023-07-01' => 97.0,
            '2024-07-01' => 95.5,
        ]);

        $result = $this->indicator->compute($series);
        $this->assertEquals(1.0, $result->signal);
    }

    public function testNoSignalWhenRising(): void
    {
        $series = new TimeSeries([
            '2023-07-01' => 97.0,
            '2024-07-01' => 97.5,
        ]);

        $result = $this->indicator->compute($series);
        $this->assertEquals(0.0, $result->signal);
    }

    public function testReturnsNAWithInsufficientData(): void
    {
        $series = new TimeSeries(['2024-07-01' => 97.0]);

        $result = $this->indicator->compute($series);
        $this->assertEquals(0.0, $result->signal);
        $this->assertEquals('N/A', $result->detail);
    }
}
