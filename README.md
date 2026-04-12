# The Cook Index

**[CookIndex.ai](https://cookindex.ai)** — A quarterly economic index tracking whether AI-driven labor displacement is triggering a demand crisis.

The index is called **DDI (Deficient Demand Index)**, named after the research paper ["Abundant Intelligence and Deficient Demand"](https://arxiv.org/abs/2603.09209) (Chen, 2026) that it's based on. The website is CookIndex.ai.

## The Thesis

The paper argues that rapid AI capability growth could reduce labor's share of national income faster than new jobs are created. Because workers spend ~85% of their income while capital owners spend ~15%, this income shift would reduce aggregate purchasing power even as GDP continues to grow. The economy would look fine on paper while the financial system deteriorates underneath — a phenomenon the paper calls "Ghost GDP."

The crisis isn't about unemployment. It's about the **decoupling of production from consumption.** People may still have jobs, GDP may still grow, but if income shifts from labor to capital fast enough, the demand gap becomes mathematically inevitable.

## What DDI Measures

DDI tracks 7 indicators that measure different parts of the same feedback loop. These are not sequential stages — they operate simultaneously and amplify each other when active.

**Displacement** — Are firms replacing workers with AI?

- **Job Openings Divergence (15%)** — Are AI-exposed sectors (tech, finance, professional services) posting fewer jobs than the overall economy? Source: BLS JOLTS.
- **Sector Employment Divergence (10%)** — Are AI-exposed sectors actually losing headcount? Source: BLS CES.

**Income Shift** — Is income moving from workers to capital owners?

- **Labor Share of Income (30%)** — The paper's central variable. What fraction of national income goes to wages vs. profits? A sharp acceleration in its decades-long decline would signal the displacement spiral activating. Source: FRED.

**Demand Erosion** — Is consumer purchasing power declining?

- **M2 Velocity (10%)** — Is money circulating less? When income concentrates in fewer hands, velocity drops. Source: FRED.
- **Ghost GDP / Income Wedge (10%)** — Is GDP growing without paying humans proportionally? Compares Real GDP against Real Personal Income excluding government transfers. If the economy is producing more than it's paying people, the gap is "Ghost GDP." Source: FRED.
- **Consumer Spending Divergence (5%)** — Is disposable income falling behind GDP? Includes a "Precautionary Savings Drag" that detects when people are employed but scared — hoarding cash despite having jobs. Source: FRED.

**Financial Contagion** — Is the debt system breaking?

- **Financial Contagion (15%)** — Are mortgage delinquency and household debt service ratios rising? The paper models that a 20% income drop raises prime default probability from ~2.1% to ~18.1%. The signal is amplified when credit stress appears during full employment (structural, not cyclical). Source: FRED.

## The Score

DDI produces a **0–100 composite score:**

| Range | Meaning |
|-------|---------|
| 0–20 | No signal. Thesis not supported by current data. |
| 20–40 | Noise range. Some movement but within historical norms. |
| 40–60 | Emerging signal. Multiple indicators crossing thresholds. |
| 60–80 | Strong signal. Pattern consistent with the paper's predictions. |
| 80–100 | Crisis-level. Displacement spiral appears to be activating. |

A **convergence bonus** (up to 1.15x) applies when 4+ indicators fire simultaneously, reflecting the paper's argument that the feedback loop becomes self-sustaining only when multiple channels activate at once.

## Falsification

The index tracks **falsification conditions** — outcomes that would disprove the crisis thesis. If AI-exposed sectors show faster hiring growth, if M2 velocity rises, if labor share stabilizes, the tool says so. DDI is designed to be honest about when the evidence says the thesis is wrong.

## Data Sources

All core data comes from two free government APIs:

| Source | What it provides | API Key |
|--------|-----------------|---------|
| [FRED](https://fred.stlouisfed.org/docs/api/api_key.html) | Labor share, M2 velocity, GDP, personal income, delinquency rates, debt service ratios, consumer sentiment, ECI | Free registration |
| [BLS](https://www.bls.gov/developers/home.htm) | Job openings by sector (JOLTS), employment by sector and subsector (CES) | Free registration |

Two optional indicators (SaaS Net Revenue Retention, AI capability benchmarks) can be manually entered but are not required.

## Running It

Requires PHP 8.x with `file_get_contents` URL support enabled.

1. Copy `.env.example` to `.env` and add your API keys:
   ```
   FRED_API_KEY=your_key_here
   BLS_API_KEY=your_key_here
   ```

2. Install dependencies:
   ```bash
   composer install
   ```

3. Run the composite index:
   ```bash
   php ddi.php
   ```

## Updating the Homepage

The homepage (`index.html`) is a static file. To update it with fresh data:

1. Run `php ddi.php` to pull the latest data from FRED and BLS
2. Provide the output to an AI assistant with the prompt: "Update `index.html` with this data. Replace the score, indicator signals, indicator detail lines, falsification results, AI-exposure detail, and workforce composition with the new values. Do not change the explanatory text or page structure."

## Project Structure

```
├── index.html                       # Static homepage (CookIndex.ai)
├── ddi.php                          # Composite index — fetches data, computes score
├── src/
│   ├── TimeSeries.php               # Value object for time series data
│   ├── IndicatorResult.php          # Value object for indicator output
│   ├── FredClient.php               # FRED API client
│   ├── BlsClient.php                # BLS API client
│   ├── Composite.php                # Weighted score + convergence
│   ├── Falsification.php            # Falsification condition checks
│   └── Indicators/
│       ├── LaborShare.php           # Workers' share of income
│       ├── JobOpenings.php          # AI-sector hiring gap
│       ├── SectorEmployment.php     # AI-sector job losses
│       ├── M2Velocity.php           # Money circulation velocity
│       ├── GhostGdp.php            # GDP vs personal income gap
│       ├── ConsumerSpending.php     # Consumer spending gap
│       └── FinancialContagion.php   # Debt stress
├── tests/                           # PHPUnit tests
├── docs/
│   ├── overview.md                  # Project overview
│   ├── indicators.md               # Detailed rationale for each indicator
│   ├── data-sources.md             # All FRED/BLS series and fetch strategies
│   ├── index-methodology.md        # Weighting, normalization, convergence
│   └── paper.html                  # Source paper (local copy)
├── composer.json
├── phpunit.xml
└── phpstan.neon
```

## Background

This project was built to make the paper's predictions testable in real-time. The paper provides explicit thresholds and falsification conditions but no monitoring tool. DDI fills that gap.

The paper's key finding: **"Crisis depth is a policy variable, not a technological inevitability."** Immediate fiscal response stabilizes labor share above 40%; without policy, it collapses. The index exists to detect whether the crisis is emerging early enough for policy to respond.

## Research

- [Abundant Intelligence and Deficient Demand](https://arxiv.org/abs/2603.09209) — The paper this index is based on
- [FRED Economic Data](https://fred.stlouisfed.org/) — Federal Reserve Bank of St. Louis
- [BLS Public Data API](https://www.bls.gov/developers/) — Bureau of Labor Statistics

## License

MIT