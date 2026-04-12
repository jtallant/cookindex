<?php

/**
 * DDI — Deficient Demand Index
 * Composite score from all indicators.
 *
 * Run: php ddi.php
 */

require __DIR__ . '/vendor/autoload.php';

use DDI\BlsClient;
use DDI\Composite;
use DDI\Falsification;
use DDI\FredClient;
use DDI\Indicators\ConsumerSpending;
use DDI\Indicators\FinancialContagion;
use DDI\Indicators\GhostGdp;
use DDI\Indicators\JobOpenings;
use DDI\Indicators\LaborShare;
use DDI\Indicators\M2Velocity;
use DDI\Indicators\SectorEmployment;

// Load API keys
$envFile = __DIR__ . '/.env';
if (!file_exists($envFile)) {
    die("Missing .env file. See .env.example\n");
}

$env = parse_ini_file($envFile);
$fredKey = $env['FRED_API_KEY'] ?? null;
$blsKey = $env['BLS_API_KEY'] ?? null;

if (!$fredKey) die("FRED_API_KEY not found in .env\n");
if (!$blsKey) die("BLS_API_KEY not found in .env\n");

echo "=== DDI — Deficient Demand Index ===\n";
echo "Fetching data...\n\n";

// Fetch FRED data
$fred = new FredClient($fredKey);

$fredData = $fred->fetchMany([
    'PRS85006173'          => 20,  // Labor share
    'M2V'                  => 20,  // M2 velocity
    'GDPC1'                => 20,  // Real GDP
    'W875RX1'              => 60,  // Real personal income ex-transfers
    'DRSFRMACBS'           => 20,  // Mortgage delinquency
    'DRCCLACBS'            => 20,  // Credit card delinquency
    'TDSP'                 => 20,  // Household debt service ratio
    'W270RE1A156NBEA'      => 20,  // Compensation as % of GDP
    'UNRATE'               => 60,  // Unemployment rate
    'DSPIC96'              => 60,  // Real disposable personal income
    'PSAVERT'              => 60,  // Personal saving rate
    'UMCSENT'              => 60,  // Consumer sentiment
    'CIU2015100000000I'    => 20,  // ECI: Information
    'CIU2015400000000I'    => 20,  // ECI: Professional/Scientific
    'CIU2010000000000I'    => 20,  // ECI: All Private (control)
]);

// Fetch BLS data
$bls = new BlsClient($blsKey);

$blsData = $bls->fetch([
    // JOLTS
    'JTS540099000000000JOL',
    'JTS510099000000000JOL',
    'JTS520000000000000JOL',
    'JTS000000000000000JOL',
    // CES Employment (supersectors)
    'CES5000000001',
    'CES5500000001',
    'CES6000000001',
    'CES0000000001',
    // CES Employment (AI-exposed subsectors)
    'CES6054000001',
    'CES5051200001',
    'CES5051700001',
    'CES5552000001',
    'CES6056100001',
    'CES6054180001',
    'CES6054120001',
    'CES6054110001',
    'CES6056110001',
    'CES6054190001',
]);

echo "Data loaded.\n\n";

// Compute indicators
$laborShareResult = (new LaborShare)->compute($fredData['PRS85006173']);

$jobOpeningsResult = (new JobOpenings)->compute(
    $blsData['JTS000000000000000JOL'],
    [
        $blsData['JTS540099000000000JOL'],
        $blsData['JTS510099000000000JOL'],
        $blsData['JTS520000000000000JOL'],
    ]
);

$sectorEmploymentResult = (new SectorEmployment)->compute(
    $blsData['CES0000000001'],
    [
        $blsData['CES5000000001'],
        $blsData['CES5500000001'],
        $blsData['CES6000000001'],
    ]
);

$m2vResult = (new M2Velocity)->compute($fredData['M2V']);

$ghostGdpResult = (new GhostGdp)->compute(
    $fredData['GDPC1'],
    $fredData['W875RX1'],
    $fredData['M2V'] ?? $fredData['GDPC1']
);

$consumerResult = (new ConsumerSpending)->compute(
    $fredData['GDPC1'],
    $fredData['DSPIC96'],
    $fredData['PSAVERT'],
    $fredData['UMCSENT'],
    $fredData['UNRATE']
);

$contagionResult = (new FinancialContagion)->compute(
    $fredData['DRSFRMACBS'],
    $fredData['DRCCLACBS'],
    $fredData['TDSP'],
    $fredData['W270RE1A156NBEA'],
    $fredData['UNRATE']
);

// Composite score
$indicators = [
    ['name' => 'Labor Share',                 'weight' => 0.30, 'result' => $laborShareResult],
    ['name' => 'Job Openings Divergence',     'weight' => 0.15, 'result' => $jobOpeningsResult],
    ['name' => 'Financial Contagion',         'weight' => 0.15, 'result' => $contagionResult],
    ['name' => 'Sector Employment Divergence','weight' => 0.10, 'result' => $sectorEmploymentResult],
    ['name' => 'M2 Velocity',                'weight' => 0.10, 'result' => $m2vResult],
    ['name' => 'Ghost GDP (Income Wedge)',    'weight' => 0.10, 'result' => $ghostGdpResult],
    ['name' => 'Consumer Spending Divergence','weight' => 0.05, 'result' => $consumerResult],
];

$composite = (new Composite)->compute($indicators);

// Falsification
$falsification = (new Falsification)->compute(
    $jobOpeningsResult,
    $sectorEmploymentResult,
    $fredData['M2V'],
    $fredData['PRS85006173'],
    $fredData['DRSFRMACBS'],
    $fredData['DSPIC96'],
    $fredData['GDPC1'],
    $fredData['TDSP']
);

$falsifiedCount = count(array_filter($falsification, fn($f) => $f['met']));

// Output
echo "╔══════════════════════════════════════════════════╗\n";
echo sprintf("║  DDI SCORE:  %-5.1f / 100                         ║\n", $composite['score']);
echo sprintf("║  %s%s║\n", $composite['interpretation'], str_repeat(' ', max(0, 48 - strlen($composite['interpretation']))));
echo "╚══════════════════════════════════════════════════╝\n\n";

echo "INDICATORS\n";
echo "──────────────────────────────────────────────────────────────────────\n";
echo sprintf("  %-35s %6s %6s  %s\n", 'Indicator', 'Weight', 'Signal', 'Detail');
echo "──────────────────────────────────────────────────────────────────────\n";

foreach ($indicators as $ind) {
    $filled = (int) round($ind['result']->signal * 10);
    $bar = str_repeat('█', $filled) . str_repeat('░', 10 - $filled);
    echo sprintf("  %-35s %5.0f%%  %s %.1f  %s\n",
        $ind['name'],
        $ind['weight'] * 100,
        $bar,
        $ind['result']->signal,
        $ind['result']->detail
    );
}

echo "\n";

echo "CONVERGENCE\n";
echo "──────────────────────────────────────────────────\n";
echo sprintf("  Active indicators (signal >= 0.3): %d of %d\n", $composite['activeCount'], count($indicators));
echo sprintf("  Convergence bonus: %s\n",
    $composite['convergenceMultiplier'] > 1.0
        ? sprintf('ACTIVE — %.2fx multiplier applied', $composite['convergenceMultiplier'])
        : 'Inactive — need 4+ indicators above 0.3');

// Stage 3 convergence gate
$m2vLatest = $fredData['M2V']->latest();
$m2vYearAgo = $fredData['M2V']->yearAgo();
$m2vYoyChange = ($m2vLatest && $m2vYearAgo) ? $m2vLatest['value'] - $m2vYearAgo['value'] : 0;
$stage3Gate = ($ghostGdpResult->signal >= 0.5 && $m2vYoyChange < 0);

if ($stage3Gate) {
    echo "  Demand erosion gate: ACTIVE — Ghost GDP high + M2V declining\n";
}

// Dangerous pattern: labor share dropping + financial contagion rising + displacement quiet
$dangerousPattern = ($laborShareResult->signal >= 0.5 && $contagionResult->signal >= 0.5
    && $jobOpeningsResult->signal < 0.3 && $sectorEmploymentResult->signal < 0.3);

if ($dangerousPattern) {
    echo "\n";
    echo "  WARNING: Labor share dropping + financial contagion rising\n";
    echo "  while displacement looks normal. Hidden crisis pattern.\n";
}

echo "\n";

echo "FALSIFICATION\n";
echo "──────────────────────────────────────────────────\n";
foreach ($falsification as $f) {
    echo sprintf("  %-42s %s\n", $f['label'], $f['met'] ? 'MET' : '—');
}
echo "\n";

echo sprintf("  %d of %d falsification conditions met — ", $falsifiedCount, count($falsification));
echo match (true) {
    $falsifiedCount <= 1 => "thesis is consistent with data\n",
    $falsifiedCount <= 3 => "mixed signals, thesis weakened\n",
    $falsifiedCount <= 5 => "thesis not well supported\n",
    default              => "thesis is falsified\n",
};

// AI-Exposure Detail (context, not scored)
$subsectors = [
    'CES5051200001' => 'Software Publishers',
    'CES5051700001' => 'Telecommunications',
    'CES6054180001' => 'Advertising & PR',
    'CES6054000001' => 'Computer Systems Design',
    'CES5552000001' => 'Insurance Carriers',
    'CES6056100001' => 'Employment Services',
    'CES6054120001' => 'Accounting',
    'CES6054110001' => 'Legal Services',
    'CES6056110001' => 'Office Admin',
    'CES6054190001' => 'Market Research',
];

$hasSubsectorData = false;
foreach ($subsectors as $id => $label) {
    if (isset($blsData[$id]) && $blsData[$id]->hasData()) {
        $hasSubsectorData = true;
        break;
    }
}

if ($hasSubsectorData) {
    echo "\n";
    echo "AI-EXPOSURE DETAIL (not scored — subsector context)\n";
    echo "──────────────────────────────────────────────────\n";

    foreach ($subsectors as $id => $label) {
        if (!isset($blsData[$id]) || !$blsData[$id]->hasData()) continue;

        $latest = $blsData[$id]->latest();
        $yoy = $blsData[$id]->yoy();

        $flag = '';
        if ($yoy !== null && $yoy < -2.0) $flag = '  ← declining';

        echo sprintf("  %-30s %8sK", $label, number_format($latest['value'], 1));
        if ($yoy !== null) {
            echo sprintf("  (YoY: %+.1f%%)", $yoy);
        }
        echo $flag . "\n";
    }
}

// Workforce Composition (ECI vs Employment)
$eciSectors = [
    ['eci' => 'CIU2015100000000I', 'emp' => 'CES5000000001', 'label' => 'Information'],
    ['eci' => 'CIU2015400000000I', 'emp' => 'CES6000000001', 'label' => 'Professional/Scientific'],
    ['eci' => 'CIU2010000000000I', 'emp' => 'CES0000000001', 'label' => 'All Private (control)'],
];

$hasEci = false;
foreach ($eciSectors as $s) {
    if (isset($fredData[$s['eci']])) { $hasEci = true; break; }
}

if ($hasEci) {
    echo "\n";
    echo "WORKFORCE COMPOSITION (not scored — ECI vs employment)\n";
    echo "──────────────────────────────────────────────────────────────────────\n";
    echo sprintf("  %-25s %10s %10s  %s\n", 'Sector', 'ECI YoY', 'Emp YoY', 'Pattern');
    echo "──────────────────────────────────────────────────────────────────────\n";

    foreach ($eciSectors as $s) {
        if (!isset($fredData[$s['eci']])) continue;

        $eciYoy = $fredData[$s['eci']]->yoy();

        $empYoy = null;
        if (isset($blsData[$s['emp']]) && $blsData[$s['emp']]->hasData()) {
            $empYoy = $blsData[$s['emp']]->yoy();
        }

        $pattern = '';
        if ($eciYoy !== null && $empYoy !== null) {
            if ($eciYoy > 0 && $empYoy > 0) {
                $pattern = 'Healthy growth';
            } elseif ($eciYoy > 0 && $empYoy <= 0) {
                $pattern = 'Consolidation (early displacement)';
            } elseif ($eciYoy <= 0 && $empYoy <= 0) {
                $pattern = 'Commoditization (late displacement)';
            } else {
                $pattern = 'Expansion into cheaper roles';
            }
        }

        echo sprintf("  %-25s %9s %9s  %s\n",
            $s['label'],
            $eciYoy !== null ? sprintf('%+.1f%%', $eciYoy) : 'N/A',
            $empYoy !== null ? sprintf('%+.1f%%', $empYoy) : 'N/A',
            $pattern
        );
    }

    echo "\n";
    echo "  ECI up + Employment down = fewer people, each paid more (seniors kept, juniors cut)\n";
    echo "  ECI down + Employment down = fewer people, each paid less (seniors replaced by cheaper staff)\n";
}
