<?php

namespace DDI\Tests;

use DDI\Composite;
use DDI\IndicatorResult;
use PHPUnit\Framework\TestCase;

class CompositeTest extends TestCase
{
    private Composite $composite;

    protected function setUp(): void
    {
        $this->composite = new Composite;
    }

    public function testZeroScoreWhenAllIndicatorsZero(): void
    {
        $indicators = [
            ['name' => 'A', 'weight' => 0.50, 'result' => new IndicatorResult(0.0, '')],
            ['name' => 'B', 'weight' => 0.50, 'result' => new IndicatorResult(0.0, '')],
        ];

        $result = $this->composite->compute($indicators);
        $this->assertEquals(0.0, $result['score']);
        $this->assertEquals(0, $result['activeCount']);
    }

    public function testWeightedScoreCalculation(): void
    {
        $indicators = [
            ['name' => 'A', 'weight' => 0.60, 'result' => new IndicatorResult(1.0, '')],
            ['name' => 'B', 'weight' => 0.40, 'result' => new IndicatorResult(0.5, '')],
        ];

        $result = $this->composite->compute($indicators);
        // (1.0 * 0.60 + 0.5 * 0.40) / 1.0 * 100 = 80.0
        $this->assertEqualsWithDelta(80.0, $result['score'], 0.01);
    }

    public function testConvergenceBonusAppliesAtFourActive(): void
    {
        $indicators = [
            ['name' => 'A', 'weight' => 0.25, 'result' => new IndicatorResult(0.5, '')],
            ['name' => 'B', 'weight' => 0.25, 'result' => new IndicatorResult(0.5, '')],
            ['name' => 'C', 'weight' => 0.25, 'result' => new IndicatorResult(0.5, '')],
            ['name' => 'D', 'weight' => 0.25, 'result' => new IndicatorResult(0.5, '')],
        ];

        $result = $this->composite->compute($indicators);
        $this->assertGreaterThan(1.0, $result['convergenceMultiplier']);
        $this->assertEquals(4, $result['activeCount']);
    }

    public function testNoConvergenceBonusBelowFour(): void
    {
        $indicators = [
            ['name' => 'A', 'weight' => 0.50, 'result' => new IndicatorResult(0.5, '')],
            ['name' => 'B', 'weight' => 0.50, 'result' => new IndicatorResult(0.1, '')],
        ];

        $result = $this->composite->compute($indicators);
        $this->assertEquals(1.0, $result['convergenceMultiplier']);
    }

    public function testScoreCapsAt100(): void
    {
        $indicators = [];
        for ($i = 0; $i < 7; $i++) {
            $indicators[] = ['name' => "I{$i}", 'weight' => 1/7, 'result' => new IndicatorResult(1.0, '')];
        }

        $result = $this->composite->compute($indicators);
        $this->assertLessThanOrEqual(100.0, $result['score']);
    }

    public function testInterpretationRanges(): void
    {
        $low = $this->composite->compute([
            ['name' => 'A', 'weight' => 1.0, 'result' => new IndicatorResult(0.1, '')],
        ]);
        $this->assertStringContainsString('No signal', $low['interpretation']);

        $mid = $this->composite->compute([
            ['name' => 'A', 'weight' => 1.0, 'result' => new IndicatorResult(0.3, '')],
        ]);
        $this->assertStringContainsString('Noise range', $mid['interpretation']);
    }
}
