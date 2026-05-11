# Median Wage Lag — distinguishing concentration from broad slowdown

This note explains why we're adding a new indicator that compares median wages to aggregate personal income, and how it's wired up.

## The problem

The existing indicators measure aggregate flows: total GDP, total personal income, total disposable income. Aggregates are useful but they hide distribution.

Imagine a bar with ten people in it. The bartender tells you the average income in the room is $1M. That's the aggregate. It could mean:

- Ten people each earning $1M (everyone's doing great), or
- One person earning $10M and nine earning nothing (one rich guy, nine broke people).

Same total. Same average. Completely different stories.

Right now, when we report "personal income grew 0.6%" we're describing the size of the total pile, not how it's split. We can't tell whether everyone got a small raise (boring, stable) or a few high earners pulled the average up while the rest of the workforce stagnated (the thesis pattern).

## Why this matters specifically for the AI displacement thesis

The paper's mechanism is: AI replaces middle-class white-collar workers, displaced workers either become unemployed or take pay cuts, the saved wages flow to corporate profits and capital owners. The fingerprint is asymmetric:

- **Top of the distribution:** still doing fine. Owners, executives, capital-income earners benefit from rising profits. Their wages keep growing or accelerate.
- **Middle of the distribution:** this is where the displacement bites. Median wages stagnate or fall.
- **Aggregate:** can keep growing because the top is pulling the average up.

Without a median measurement, this looks identical to a generic broad slowdown. With one, we can tell them apart.

## What we're adding

A new indicator: **Median Wage Lag**. It compares the year-over-year growth of median real weekly earnings (the typical worker's paycheck) against the year-over-year growth of aggregate personal income excluding transfers (everyone's paychecks combined).

If aggregate grows faster than the median, the gap is the concentration signal. The wider the gap, the more growth is piling up at the top relative to the middle.

## Data source

**BLS series `LES1252882800`** — Median usual weekly real earnings, full-time wage and salary workers, 16 years and older, seasonally adjusted, in constant 1982–84 dollars (already inflation-adjusted, no manual deflation needed).

We're using BLS directly because:
- We already have a `BlsClient` — no new infrastructure.
- BLS is the original source. (FRED's `LES1252881600Q` is just a republished mirror of this series.)
- Quarterly cadence, fits the index update rhythm.

The aggregate side (`W875RX1`, Real Personal Income ex-transfers) is already pulled from FRED for Ghost GDP. We reuse it.

## A wrinkle: Q4 2025 is missing

BLS notes: "Data for Q4 2025 were not produced. Data for October 2025 were not collected due to the federal government shutdown." The series returns the period with value `"-"`.

This requires a small fix in the BLS parser: `TimeSeries::fromBls()` must filter `"-"` values the same way the FRED parser already filters `"."`. Without that fix, `(float) "-"` becomes `0.0` and corrupts the smoothing window.

## Methodology

Same smoothed YoY approach we just applied to Labor Share — average over 4 trailing quarters vs. the 4 quarters before that. With Q4 2025 missing, the recent window is built from Q1 2025, Q2 2025, Q3 2025, Q1 2026 (4 available quarters spanning ~5 quarters of time). The prior window is Q1 2024 – Q4 2024.

Compute YoY % for both series using the smoothed averages. Gap = aggregate YoY% − median YoY%.

## Threshold design

Aggregate income has been growing somewhat faster than median wages for decades — that's the long-running inequality trend. A small positive gap is "normal" baseline inequality; we're trying to detect *acceleration* beyond it.

Initial thresholds, mirroring Ghost GDP's structure:

| Gap (pp) | Signal |
|---|---|
| < 0.5    | 0.0 |
| 0.5–1.0  | 0.2 |
| 1.0–1.5  | 0.4 |
| 1.5–2.5  | 0.7 |
| > 2.5    | 1.0 |

These are calibration estimates, not paper-derived. Same caveat as the existing methodology note on the public site: the score is directional, the precise thresholds aren't load-bearing. If the indicator fires often on noise, we'll tighten them.

## Composite weight

Starting at **5%**. Reasons to start small:

- The signal overlaps with Ghost GDP and Consumer Spending Divergence. All three measure variants of "growth not reaching workers." Adding a fourth at full weight would over-index on that theme.
- The thresholds are unvalidated. Better to underweight a new signal than discover it dominates the composite for the wrong reasons.
- `Composite::compute()` normalizes by total weight, so the existing 0.95 sum just becomes 1.00 with this addition — no rebalancing needed for the math to work out.

Worth revisiting the weight allocation across all "wedge" indicators (Ghost GDP, Consumer Spending, Median Wage Lag) once we have a few quarters of side-by-side data. They may want to be consolidated or re-weighted as a group.

## Implementation steps

1. `src/TimeSeries.php` — fix `fromBls()` to filter `"-"` values; add `smoothedYoyPercent(int $n)` helper.
2. `src/Indicators/MedianWageLag.php` — new file. Computes smoothed YoY% on both series, scores the gap.
3. `ddi.php` — add `LES1252882800` to the BLS series list, instantiate the indicator, register it in the composite array with 5% weight.

## What this does and doesn't tell us

**Does:** flag whether aggregate income growth is broad-based or concentrated. If Median Wage Lag lights up alongside Ghost GDP, it's evidence the gap reflects distribution, not just overall slowdown.

**Doesn't:** tell us *which* deciles are pulling away. Median is one cut point. To see whether the top 10% specifically is the source of the concentration, we'd need decile breakdowns from the Atlanta Fed Wage Growth Tracker or BLS CPS microdata. That's a possible follow-up if this signal proves informative.
