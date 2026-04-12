<?php

namespace DDI\Tests\Indicators;

use DDI\Indicators\ConsumerSpending;
use DDI\TimeSeries;
use PHPUnit\Framework\TestCase;

class ConsumerSpendingTest extends TestCase
{
    private ConsumerSpending $indicator;

    protected function setUp(): void
    {
        $this->indicator = new ConsumerSpending;
    }

    private function makeSeries(float $from, float $to): TimeSeries
    {
        return new TimeSeries([
            '2023-07-01' => $from,
            '2024-07-01' => $to,
        ]);
    }

    public function testNoSignalWhenIncomeKeepsPace(): void
    {
        $gdp = $this->makeSeries(20000.0, 20400.0);       // +2%
        $income = $this->makeSeries(15000.0, 15300.0);     // +2%
        $saving = $this->makeSeries(5.0, 5.0);
        $sentiment = $this->makeSeries(80.0, 80.0);
        $unemployment = $this->makeSeries(4.0, 4.0);

        $result = $this->indicator->compute($gdp, $income, $saving, $sentiment, $unemployment);
        $this->assertEquals(0.0, $result->signal);
    }

    public function testSmallSignalWithSmallGap(): void
    {
        $gdp = $this->makeSeries(20000.0, 20400.0);       // +2%
        $income = $this->makeSeries(15000.0, 15150.0);     // +1%
        $saving = $this->makeSeries(5.0, 5.0);
        $sentiment = $this->makeSeries(80.0, 80.0);
        $unemployment = $this->makeSeries(4.0, 4.0);

        // Gap: 1.0 pp => signal 0.1
        $result = $this->indicator->compute($gdp, $income, $saving, $sentiment, $unemployment);
        $this->assertEquals(0.1, $result->signal);
    }

    public function testModerateSignalWithLargerGap(): void
    {
        $gdp = $this->makeSeries(20000.0, 20600.0);       // +3%
        $income = $this->makeSeries(15000.0, 15150.0);     // +1%
        $saving = $this->makeSeries(5.0, 5.0);
        $sentiment = $this->makeSeries(80.0, 80.0);
        $unemployment = $this->makeSeries(4.0, 4.0);

        // Gap: 2.0 pp => signal 0.3
        $result = $this->indicator->compute($gdp, $income, $saving, $sentiment, $unemployment);
        $this->assertEquals(0.3, $result->signal);
    }

    public function testPrecautionarySavingsDragAmplifies(): void
    {
        $gdp = $this->makeSeries(20000.0, 20400.0);       // +2%
        $income = $this->makeSeries(15000.0, 15150.0);     // +1%
        $saving = $this->makeSeries(9.0, 9.0);             // > 8.0
        $sentiment = $this->makeSeries(60.0, 60.0);        // < 70.0
        $unemployment = $this->makeSeries(4.0, 4.0);       // < 5.0

        // Gap: 1.0 pp => base signal 0.1, then × 1.2 = 0.12
        $result = $this->indicator->compute($gdp, $income, $saving, $sentiment, $unemployment);
        $this->assertEqualsWithDelta(0.12, $result->signal, 0.001);
    }

    public function testPrecautionarySavingsNotTriggeredWhenUnemploymentHigh(): void
    {
        $gdp = $this->makeSeries(20000.0, 20400.0);       // +2%
        $income = $this->makeSeries(15000.0, 15150.0);     // +1%
        $saving = $this->makeSeries(9.0, 9.0);             // > 8.0
        $sentiment = $this->makeSeries(60.0, 60.0);        // < 70.0
        $unemployment = $this->makeSeries(6.0, 6.0);       // >= 5.0, no drag

        // Gap: 1.0 pp => signal 0.1, no amplification
        $result = $this->indicator->compute($gdp, $income, $saving, $sentiment, $unemployment);
        $this->assertEquals(0.1, $result->signal);
    }

    public function testReturnsNAWithInsufficientData(): void
    {
        $gdp = new TimeSeries(['2024-07-01' => 20000.0]);
        $income = new TimeSeries(['2024-07-01' => 15000.0]);
        $saving = new TimeSeries(['2024-07-01' => 5.0]);
        $sentiment = new TimeSeries(['2024-07-01' => 80.0]);
        $unemployment = new TimeSeries(['2024-07-01' => 4.0]);

        $result = $this->indicator->compute($gdp, $income, $saving, $sentiment, $unemployment);
        $this->assertEquals(0.0, $result->signal);
        $this->assertEquals('N/A', $result->detail);
    }
}
