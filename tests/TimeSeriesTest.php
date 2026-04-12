<?php

namespace DDI\Tests;

use DDI\TimeSeries;
use PHPUnit\Framework\TestCase;

class TimeSeriesTest extends TestCase
{
    public function testLatestReturnsNewestPoint(): void
    {
        $series = new TimeSeries([
            '2024-01-01' => 100.0,
            '2024-04-01' => 101.0,
            '2024-07-01' => 102.0,
        ]);

        $latest = $series->latest();
        $this->assertEquals(102.0, $latest['value']);
        $this->assertEquals('2024-07-01', $latest['date']);
    }

    public function testLatestReturnsNullWhenEmpty(): void
    {
        $series = new TimeSeries([]);
        $this->assertNull($series->latest());
    }

    public function testYearAgoFindsClosestPoint(): void
    {
        $series = new TimeSeries([
            '2023-07-01' => 95.0,
            '2024-01-01' => 98.0,
            '2024-07-01' => 100.0,
        ]);

        $yearAgo = $series->yearAgo();
        $this->assertEquals(95.0, $yearAgo['value']);
        $this->assertEquals('2023-07-01', $yearAgo['date']);
    }

    public function testYoyCalculatesPercentageChange(): void
    {
        $series = new TimeSeries([
            '2023-07-01' => 100.0,
            '2024-07-01' => 105.0,
        ]);

        $this->assertEqualsWithDelta(5.0, $series->yoy(), 0.001);
    }

    public function testYoyReturnsNullWithInsufficientData(): void
    {
        $series = new TimeSeries(['2024-07-01' => 100.0]);
        $this->assertNull($series->yoy());
    }

    public function testFindClosestReturnsExactMatch(): void
    {
        $series = new TimeSeries([
            '2024-01-01' => 100.0,
            '2024-04-01' => 101.0,
        ]);

        $this->assertEquals(101.0, $series->findClosest('2024-04-01'));
    }

    public function testFindClosestReturnsNearbyPoint(): void
    {
        $series = new TimeSeries([
            '2024-01-01' => 100.0,
            '2024-04-01' => 101.0,
        ]);

        $this->assertEquals(101.0, $series->findClosest('2024-03-15'));
    }

    public function testFindClosestReturnsNullWhenTooFar(): void
    {
        $series = new TimeSeries(['2024-01-01' => 100.0]);
        $this->assertNull($series->findClosest('2025-01-01'));
    }

    public function testFromFredSkipsMissingValues(): void
    {
        $observations = [
            ['date' => '2024-07-01', 'value' => '102.5'],
            ['date' => '2024-04-01', 'value' => '.'],
            ['date' => '2024-01-01', 'value' => '100.0'],
        ];

        $series = TimeSeries::fromFred($observations);
        $this->assertCount(2, $series->points());
        $this->assertEquals(102.5, $series->latest()['value']);
    }

    public function testFromBlsParsesMonthlyData(): void
    {
        $data = [
            ['year' => '2024', 'period' => 'M07', 'value' => '150000'],
            ['year' => '2024', 'period' => 'M01', 'value' => '148000'],
            ['year' => '2024', 'period' => 'M13', 'value' => '999'],  // annual, should be skipped
        ];

        $series = TimeSeries::fromBls($data);
        $this->assertCount(2, $series->points());
        $this->assertEquals(150000.0, $series->latest()['value']);
    }

    public function testHasData(): void
    {
        $this->assertTrue((new TimeSeries(['2024-01-01' => 1.0]))->hasData());
        $this->assertFalse((new TimeSeries([]))->hasData());
    }
}
