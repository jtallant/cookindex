<?php

namespace DDI\Tests\Indicators;

use DDI\Indicators\GhostGdp;
use DDI\TimeSeries;
use PHPUnit\Framework\TestCase;

class GhostGdpTest extends TestCase
{
    private GhostGdp $indicator;

    protected function setUp(): void
    {
        $this->indicator = new GhostGdp;
    }

    public function testNoSignalWhenIncomeKeepsPaceWithGdp(): void
    {
        $gdp = new TimeSeries([
            '2023-07-01' => 20000.0,
            '2024-07-01' => 20400.0, // +2%
        ]);
        $income = new TimeSeries([
            '2023-07-01' => 15000.0,
            '2024-07-01' => 15300.0, // +2%
        ]);
        $ref = new TimeSeries([
            '2023-07-01' => 1.4,
            '2024-07-01' => 1.41,
        ]);

        $result = $this->indicator->compute($gdp, $income, $ref);
        $this->assertEquals(0.0, $result->signal);
    }

    public function testNoSignalWhenGapBelowHalfPoint(): void
    {
        $gdp = new TimeSeries([
            '2023-07-01' => 20000.0,
            '2024-07-01' => 20400.0, // +2%
        ]);
        $income = new TimeSeries([
            '2023-07-01' => 15000.0,
            '2024-07-01' => 15270.0, // +1.8%
        ]);
        $ref = new TimeSeries([
            '2023-07-01' => 1.4,
            '2024-07-01' => 1.41,
        ]);

        // Gap: 2% - 1.8% = 0.2 pp < 0.5 => signal 0.0
        $result = $this->indicator->compute($gdp, $income, $ref);
        $this->assertEquals(0.0, $result->signal);
    }

    public function testWeakSignalWithModerateGap(): void
    {
        $gdp = new TimeSeries([
            '2023-07-01' => 20000.0,
            '2024-07-01' => 20400.0, // +2%
        ]);
        $income = new TimeSeries([
            '2023-07-01' => 15000.0,
            '2024-07-01' => 15150.0, // +1%
        ]);
        $ref = new TimeSeries([
            '2023-07-01' => 1.4,
            '2024-07-01' => 1.41,
        ]);

        // Gap: 2% - 1% = 1.0 pp => signal 0.2
        $result = $this->indicator->compute($gdp, $income, $ref);
        $this->assertEquals(0.2, $result->signal);
    }

    public function testStrongSignalWithLargeGap(): void
    {
        $gdp = new TimeSeries([
            '2023-07-01' => 20000.0,
            '2024-07-01' => 20600.0, // +3%
        ]);
        $income = new TimeSeries([
            '2023-07-01' => 15000.0,
            '2024-07-01' => 15075.0, // +0.5%
        ]);
        $ref = new TimeSeries([
            '2023-07-01' => 1.4,
            '2024-07-01' => 1.41,
        ]);

        // Gap: 3% - 0.5% = 2.5 pp => signal 0.7
        $result = $this->indicator->compute($gdp, $income, $ref);
        $this->assertEquals(0.7, $result->signal);
    }

    public function testCriticalSignalWithSevereGap(): void
    {
        $gdp = new TimeSeries([
            '2023-07-01' => 20000.0,
            '2024-07-01' => 20800.0, // +4%
        ]);
        $income = new TimeSeries([
            '2023-07-01' => 15000.0,
            '2024-07-01' => 15000.0, // 0%
        ]);
        $ref = new TimeSeries([
            '2023-07-01' => 1.4,
            '2024-07-01' => 1.41,
        ]);

        // Gap: 4% - 0% = 4.0 pp > 2.5 => signal 1.0
        $result = $this->indicator->compute($gdp, $income, $ref);
        $this->assertEquals(1.0, $result->signal);
    }

    public function testReturnsNAWithInsufficientData(): void
    {
        $gdp = new TimeSeries(['2024-07-01' => 20000.0]);
        $income = new TimeSeries(['2024-07-01' => 15000.0]);
        $ref = new TimeSeries(['2024-07-01' => 1.41]);

        $result = $this->indicator->compute($gdp, $income, $ref);
        $this->assertEquals(0.0, $result->signal);
        $this->assertEquals('N/A', $result->detail);
    }
}
