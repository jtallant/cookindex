# YoY smoothing — why we average windows instead of comparing single points

This note records why the indicators compare trailing-window averages instead of "latest observation vs. observation from one year ago."

## The trigger

Between the Q1 2026 publication (data through Q4 2025) and the next data pull (data through Q1 2026), the composite score jumped from **29.9 to 46.7** — a 17-point move in one quarter. That's larger than anything the underlying economy actually did.

About 10 of those 17 points came from a single artifact: the Q1 2025 Labor Share observation was an outlier high, and using it as the year-ago base inflated the YoY signal.

## What the data actually looked like

Labor Share (FRED `PRS85006173`) recent observations:

| Quarter   | Value  |
|-----------|--------|
| 2024-Q4   | 96.940 |
| 2025-Q1   | **97.871** ← unusually high |
| 2025-Q2   | 96.723 |
| 2025-Q3   | 95.999 |
| 2025-Q4   | 96.307 |
| 2026-Q1   | 95.707 |

When the Q1 2026 site was published, the indicator compared 2025-Q4 to 2024-Q4: 96.31 − 96.94 = **−0.63**. Signal: 0.7.

When the next pull rolled the window forward, it compared 2026-Q1 to 2025-Q1: 95.71 − 97.87 = **−2.16**. Signal: 1.0 (crosses the >1.0 threshold).

The trajectory of the series barely changed. The base period changed.

## Is YoY the right framing for the thesis?

Yes. The paper ("Abundant Intelligence and Deficient Demand") is about a structural shift — AI permanently changing the labor/capital share split, demand bleeding out over multiple years. That's a long-arc phenomenon, not a cyclical one. YoY is the right granularity:

- Absorbs seasonality (some FRED series aren't fully seasonally adjusted).
- Matches the paper's framing of multi-quarter drift, not month-to-month blips.
- QoQ on quarterly data would be far too noisy to read.

The framing is right. The implementation was wrong.

## What "YoY" should actually mean

Single-point YoY ("latest value vs. value one year ago") is fragile because either endpoint can be a noisy quarter. The standard analyst convention is:

> **Trailing 4-quarter average vs. the 4 quarters before that.** (Or trailing 12-month for monthly series.)

This is what the BLS, BEA, and the Fed publish in "underlying trend" estimates. It's still YoY in spirit — it's still saying "compared to a year ago" — but it absorbs single-quarter outliers like Q1 2025.

For Labor Share on this data:

| Method                         | Q1 2026 reading |
|--------------------------------|-----------------|
| Single-point YoY               | 95.707 vs 97.871 = **−2.16** |
| Trailing 4Q avg vs prior 4Q avg | 96.18 vs 97.02 = **−0.83** |

Both are "year over year." The smoothed version is what you'd report in plain English when you say "labor share is down about 0.8 points year over year."

## The threshold-cliff issue

Even with the right YoY method, the signal scoring has step thresholds:

```
|accel| <= 0.2 → 0.2
|accel| <= 0.5 → 0.4
|accel| <= 1.0 → 0.7
|accel|  > 1.0 → 1.0
```

Labor Share is 30% of the composite weight. Crossing one threshold moves the signal by ~0.3, which is ~9 composite points. Two readings on either side of a threshold can produce a misleading "big move" even when the underlying number barely changed.

Not changing this yet, but flagging: a continuous interpolation between thresholds (or a smoother piecewise-linear curve) would prevent step-function score jumps. Worth revisiting if the index ever shows another sharp move that's hard to defend.

## What changed in the code

- `src/TimeSeries.php`: added `trailingAverage(int $n)` and `smoothedYoyDelta(int $n = 4)`. The latter returns recent-window average minus prior-window average, in the series' own units.
- `src/Indicators/LaborShare.php`: switched from `latest()`/`yearAgo()` to `smoothedYoyDelta(4)`. The historical baseline (`-0.2`) and the threshold buckets are unchanged — only the input is smoothed. Detail string now reports `4Q-avg YoY`.

## Score impact

Same data pull, single-point vs. smoothed:

| Version                       | Composite | Labor Share signal |
|-------------------------------|-----------|---------------------|
| Published (Q1 2026, data through Q4 2025) | 29.9 | 0.7 |
| Single-point YoY (latest pull)            | 46.7 | 1.0 |
| 4Q-smoothed YoY (latest pull)             | 37.3 | 0.7 |

The +7 from published is a real move — Ghost GDP and Consumer Spending widened genuinely in Q1 2026. The other ~10 was the Q1 2025 base-period spike masquerading as a trend break.

## Other indicators to consider

Labor Share was switched first. Ghost GDP and Consumer Spending were switched later when the same fragility showed up in the Q2 2026 reading, just in the opposite direction.

- **Ghost GDP / Consumer Spending** — switched. The 2025-Q1 GDP observation was a soft quarter (Real GDP dipped from Q4 2024). When the YoY window rolled onto Q1 2026 as the latest reading, the weak 2025-Q1 base inflated the YoY change. Single-point GDP YoY came in at +2.66%; trailing 4Q-avg vs prior 4Q-avg was +2.27% — same direction, less noise. The Ghost GDP signal dropped from 0.7 to 0.4 after smoothing; Consumer Spending dropped from 0.5 to 0.1. Composite went 35.4 → 30.4 on the same data pull.
- **M2 Velocity** (`M2Velocity.php`) — quarterly, low noise, not switched. Probably fine but worth revisiting if it ever signals strongly.

The pattern across both Labor Share and Ghost GDP: it's not always a *spike* that distorts YoY; sometimes it's a *trough* in the base period. Either way, single-point comparison amplifies it. Smoothed comparison absorbs it without losing the underlying trend.

## Score history with the methodology shifts

| Stage | Composite | Notes |
|---|---|---|
| Q1 2026 published | 29.9 | Single-point YoY everywhere |
| Q2 2026 first fetch (no fixes) | 46.7 | Q1 2025 Labor Share spike inflating |
| + smoothed Labor Share | 37.3 | Spike absorbed |
| + Median Wage Lag added at 5% | 35.4 | New 0.0-signal indicator dilutes slightly |
| + smoothed Ghost GDP and Consumer Spending | **30.4** | Q1 2025 GDP trough absorbed |

The +5.5 jump that initially seemed alarming turned out to be ~+0.5 once methodology was applied consistently across all four "wedge" indicators (Labor Share, Ghost GDP, Consumer Spending, plus the new Median Wage Lag for distribution context).

## Rule of thumb going forward

If a single quarter's data move makes the composite jump more than the underlying economic story justifies, look at the year-ago base period before assuming the new reading is wrong. The base period is half the YoY calculation and gets less attention than the latest observation.
