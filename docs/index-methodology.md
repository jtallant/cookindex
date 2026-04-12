# Index Methodology

## What the Index Represents

The DDI score is a 0–100 composite that answers one question: **how much evidence exists right now that the AI displacement → demand collapse scenario is underway?**

- **0–20:** No signal. Thesis is not supported by current data.
- **20–40:** Noise range. Some indicators moving but within historical norms.
- **40–60:** Emerging signal. Multiple indicators crossing thresholds simultaneously.
- **60–80:** Strong signal. Clear pattern consistent with the paper's predictions.
- **80–100:** Crisis-level. Displacement spiral appears to be activating across channels.

The index is not a prediction. It's a dashboard reading — like a check engine light, not a fortune teller.

---

## The Feedback Loop

The paper models these mechanisms as **simultaneous feedback loops**, not sequential stages. Displacement, income shift, demand erosion, and financial contagion all operate in parallel and amplify each other through a feedback coefficient (β = 0.30): a 10% drop in demand causes a 3% acceleration in AI adoption, which further reduces demand.

The 7 indicators measure different facets of this loop:

```
DISPLACEMENT — Are firms replacing workers with AI?
  ├── Job Openings in AI-Exposed Sectors (NAICS 51, 52, 54)
  └── AI-Exposed Sector Employment

INCOME SHIFT — Is income moving from labor to capital?
  └── Labor Share of Income  ← THE CENTRAL VARIABLE

DEMAND EROSION — Is consumer purchasing power declining?
  ├── M2 Velocity (monetary circulation — long-term macro signal)
  ├── Ghost GDP / Income Wedge (direct decoupling measurement)
  └── Consumer Spending Divergence + Precautionary Savings Drag

FINANCIAL CONTAGION — Is credit stress emerging from the debt-income mismatch?
  └── Delinquency + Household Debt Service Ratio
```

Any of these can be active independently. The crisis emerges when they reinforce each other — which is what the convergence bonus captures.

**Why Labor Share is weighted highest, not Displacement:** You can have high displacement but if new jobs are created (reinstatement), the crisis never happens. However, if labor share drops significantly while productivity stays high, the demand gap is mathematically guaranteed. Labor share is the "check engine" light; displacement is the speedometer. The paper's crisis is defined not by job loss but by the financial system's inability to handle the income shift.

---

## NAICS Sector Mapping

The "AI-exposed vs. control" comparison uses NAICS supersectors:

### AI-Exposed Sectors
| NAICS | Sector | Why Exposed |
|-------|--------|-------------|
| 51 | Information | Software, data processing, media — direct AI substitution targets |
| 52 | Finance & Insurance | Analysis, underwriting, trading — high cognitive task share |
| 54 | Professional, Scientific & Technical Services | Legal, consulting, accounting, engineering — knowledge work |
| 55 | Management of Companies | Corporate overhead — administrative AI exposure |

### Control Sectors (less AI-exposed)
| NAICS | Sector | Why Control |
|-------|--------|-------------|
| 23 | Construction | Physical labor, less automatable near-term |
| 62 | Health Care & Social Assistance | Regulatory barriers, physical presence required |
| 72 | Accommodation & Food Services | In-person service delivery |
| 44-45 | Retail Trade | Mixed, but physical presence component |

### Composite Comparison
The divergence calculation compares the weighted average of AI-exposed sectors (51+52+54) against Total Nonfarm as the broadest control. We don't use only the control sectors above because Total Nonfarm already includes everything — the divergence naturally emerges when AI-exposed sectors underperform the whole.

---

## Weighting

| Indicator | Weight | Rationale |
|-----------|--------|-----------|
| Labor Share | **30%** | Central variable. If this breaks, the system breaks — demand gap becomes mathematically inevitable. |
| Job Openings Divergence (AI-exposed vs. total) | 15% | Earliest signal, tests displacement trigger |
| Financial Contagion (Delinquency + Debt Service) | **15%** | Paper's most unique contribution — the debt-income mismatch |
| Sector Employment Divergence (AI-exposed vs. total) | 10% | Confirms displacement with actual job losses |
| M2 Velocity | 10% | Monetary circulation — long-term macro signal of demand channel health |
| Ghost GDP / Income Wedge | 10% | Direct measurement of production decoupling from human income |
| Consumer Spending Divergence | 5% | Confirms income→spending transmission, includes Precautionary Savings Drag |
| Optional: SaaS NRR | 5% | Intermediation collapse (when available) |
| Optional: AI Benchmarks | 0% | Context only — sets pace, not scored |

When optional indicators are not entered, their weight is redistributed proportionally to the other indicators. AI benchmarks are tracked for context but don't contribute to the score — they're an input to the model, not an output.

**Key change from v2:** Ghost GDP moved from being a multiplier on M2V to its own independent indicator (10%). M2V reduced from 15% to 10%, Consumer Spending Divergence from 10% to 5%. The rationale: M2V is an *indirect consequence* of the demand erosion the paper describes, but the income wedge is the *direct measurement* of it. Real data showed M2V rising for 8 quarters while GDP outpaced personal income — the old multiplier design produced a 0.0 reading despite clear evidence of production-income decoupling. Ghost GDP deserves to fire independently.

**Demand erosion convergence gate:** If Ghost GDP signal is high AND M2V begins trending downward in the same quarter, trigger the Convergence Bonus (1.15x) for all demand erosion indicators. Ghost GDP is a problem on its own; Ghost GDP *plus* velocity decay is a systemic crisis.

---

## Normalizing Each Indicator to 0–1

Each indicator produces a **signal strength** from 0 (no evidence of thesis) to 1 (strong evidence of thesis). The method depends on the indicator type.

### Divergence Indicators (Job Openings, Sector Employment)

These compare AI-exposed sectors against Total Nonfarm. The signal is the *divergence* — AI-exposed declining relative to control.

```
divergence = control_yoy_change - exposed_yoy_change  (in percentage points)
```

Where `yoy_change` is year-over-year percentage change. We use the absolute percentage-point gap, not a ratio, because a ratio blows up when the control rate is near zero (e.g., total nonfarm growing 0.16% produces a 400%+ ratio from a 0.7pp gap). The pp gap is directly interpretable: "AI-exposed sectors are growing X percentage points slower than the overall economy."

| Divergence (pp) | Signal |
|-----------|--------|
| ≤ 0 (exposed growing faster than control) | 0.0 |
| 0–1pp | 0.1 |
| 1–3pp | 0.3 |
| 3–5pp | 0.5 |
| 5–10pp | 0.7 |
| 10–20pp | 0.9 |
| > 20pp | 1.0 |

For **velocity of divergence** — if the gap is widening quarter over quarter (not just present but accelerating), apply a 1.2x multiplier (capped at 1.0).

### Trend Indicators (Labor Share, M2 Velocity)

These have a known historical trend. The signal is whether the *rate of decline is accelerating* beyond the historical baseline.

```
recent_rate = rate of change over last 4 quarters
historical_rate = average rate of change over last 20 years
acceleration = recent_rate - historical_rate
```

For labor share (historical decline ~0.2pp/year):

| Acceleration | Signal |
|-------------|--------|
| ≤ 0 (declining slower than historical or rising) | 0.0 |
| 0–0.2pp/yr faster | 0.2 |
| 0.2–0.5pp/yr faster | 0.4 |
| 0.5–1.0pp/yr faster | 0.7 |
| > 1.0pp/yr faster | 1.0 |

For M2V (historical decline ~0.03/year):

| Acceleration | Signal |
|-------------|--------|
| ≤ 0 (declining slower or rising) | 0.0 |
| 0–0.02/yr faster | 0.2 |
| 0.02–0.05/yr faster | 0.4 |
| 0.05–0.10/yr faster | 0.7 |
| > 0.10/yr faster | 1.0 |

### Ghost GDP / Income Wedge (independent indicator)

Ghost GDP measures whether the economy is generating human income proportional to its growth. The comparison is between Real GDP (`GDPC1`) and Real Personal Income Excluding Current Transfer Receipts (`W875RX1`).

Why `W875RX1` and not PCE (`PCEC96`):
- **PCE is a lagging indicator.** People keep spending by draining savings and using credit cards until they can't. By the time PCE drops, the crisis is already advanced.
- **W875RX1 strips out government transfers** (stimulus, welfare), measuring only what the economy pays humans for their labor and investments.
- **It captures the displacement mechanism directly.** If AI takes a job, GDP stays stable (the AI is producing), but W875RX1 drops (the human isn't being paid). The difference is "Ghost GDP" — output produced by machines whose "wages" (profits) stay in corporate treasuries rather than entering human wallets.

```
ghost_gdp_gap = real_gdp_yoy_growth - real_personal_income_ex_transfers_yoy_growth
```

| GDP–Income Gap | Signal |
|----------------|--------|
| < 0.5pp (income keeping pace with GDP) | 0.0 |
| 0.5–1.0pp | 0.2 |
| 1.0–1.5pp | 0.4 |
| 1.5–2.5pp | 0.7 |
| > 2.5pp | 1.0 |

The 0.5pp floor filters out normal economic noise. The 1.5pp threshold (the "Acemoglu Zone") is where productivity is clearly outstripping wage growth and the displacement mechanism is active.

**Important:** Even when the old PCE-based gap shows no signal (people still spending), the income wedge can fire early — detecting that consumption is being sustained by savings drawdown or credit, not by income. This makes it a leading indicator for Stage 4 (Financial Contagion) as well.

### Precautionary Savings Drag

New derived signal within Stage 3. The paper mentions that even employed workers may stop spending due to fear of future automation, accelerating the demand crisis before actual job losses.

```
precarity = high_savings AND low_confidence AND low_unemployment
```

Specifically:
- Personal Saving Rate (`PSAVERT`) above 8% (historical average ~7%)
- Consumer Sentiment (`UMCSENT`) below 70 (pessimistic range)
- Unemployment Rate (`UNRATE`) below 5% (people have jobs but aren't spending)

When all three conditions are met, apply a **1.2x multiplier** to the Consumer Spending Divergence signal. This captures the "employed but scared" dynamic — people hoarding cash despite having income, which drags demand even before displacement shows up in employment data.

### Financial Contagion (Delinquency + Debt Service)

Two sub-signals, equally weighted within this category:

**Delinquency sub-signal:**

| Mortgage Delinquency Rate | Signal |
|--------------------------|--------|
| < 2.0% | 0.0 |
| 2.0–3.0% | 0.2 |
| 3.0–4.0% | 0.5 |
| 4.0–6.0% | 0.7 |
| > 6.0% | 1.0 |

Credit card delinquency (`DRCCLACBS`) is a leading signal — if it's rising faster than mortgage delinquency, boost the delinquency sub-signal by 1.2x.

**Debt service sub-signal:**

The Household Debt Service Ratio (`TDSP`) measures debt payments as a percentage of disposable income. When income drops but debt stays fixed, this ratio spikes.

| TDSP | Signal |
|------|--------|
| < 10% | 0.0 |
| 10–11% | 0.2 |
| 11–12% | 0.4 |
| 12–13% | 0.6 |
| 13–14% | 0.8 |
| > 14% | 1.0 |

**The critical pattern:** Debt service rising *while wages stagnate* is much more dangerous than debt service rising because people are borrowing to buy houses in a strong economy. Compare `TDSP` trend against `W270RE1A156NBEA` (compensation as % of GDP). If debt service is rising AND compensation share is falling, apply a 1.5x multiplier to the financial contagion signal (capped at 1.0).

**Combined financial contagion signal:**
```
financial_contagion = (delinquency_signal * 0.5) + (debt_service_signal * 0.5)
```

### Unemployment Context Multiplier

Across **all** threshold indicators (delinquency, debt service, consumer spending), apply a context multiplier based on unemployment:

| Unemployment Rate | Multiplier | Rationale |
|-------------------|------------|-----------|
| < 4% | 1.5x | Stress appearing despite full employment = high-income displacement |
| 4–5% | 1.3x | Low unemployment, stress shouldn't be here |
| 5–6% | 1.0x | Normal range, no adjustment |
| > 6% | 0.7x | Broad recession explains the stress — not specifically AI displacement |

This is the most important contextual adjustment. The paper's thesis is specifically about *structural* displacement of high-income workers, not cyclical recession. If delinquency rises during a broad recession, that's expected and doesn't support the AI thesis. If it rises while unemployment is 3.8%, something unusual is happening.

### Optional: SaaS NRR

| Median NRR | Signal |
|-----------|--------|
| > 115% | 0.0 |
| 110–115% | 0.2 |
| 105–110% | 0.4 |
| 100–105% | 0.6 |
| 95–100% | 0.8 |
| < 95% | 1.0 |

---

## Computing the Composite Score

```
score = 0
total_weight = 0

for each indicator:
    signal = compute_signal(indicator)  // 0.0 to 1.0 (after multipliers, capped)
    score += signal * indicator.weight
    total_weight += indicator.weight

// Normalize so the max possible score is 100
// (handles missing optional indicators by redistributing weight)
ddi = (score / total_weight) * 100
```

### Convergence Bonus

The paper posits that the economy can handle one or two of these signals in isolation — the feedback loop becomes self-sustaining only when multiple channels activate simultaneously. If 4+ of the 6 core indicators have signal ≥ 0.3, apply a convergence bonus:

```
active_count = count of core indicators with signal ≥ 0.3

if active_count >= 4:
    convergence_multiplier = 1.0 + (0.05 * (active_count - 3))
    // 4 active = 1.05x, 5 active = 1.10x, 6 active = 1.15x
    ddi = min(100, ddi * convergence_multiplier)
```

This is deliberately small — it nudges the score up when multiple channels activate, without overwhelming the signal.

### The Most Dangerous Pattern

The single most alarming configuration is **Stage 2 (Labor Share) dropping while Stage 4 (Financial Contagion) rises, even if Stage 1 (Displacement) looks relatively normal.** This would mean:
- Income is shifting from labor to capital (labor share declining)
- Credit stress is building (delinquency + debt service rising)
- But unemployment looks fine — because displaced workers are taking lower-wage gig work to survive

This is the "hidden crisis" scenario. People still technically have jobs, GDP still looks fine, but the financial plumbing is leaking. When the tool detects this specific pattern (labor share signal ≥ 0.5 AND financial contagion signal ≥ 0.5 AND displacement signal < 0.3), it should flag it as a **structural warning** separate from the composite score.

---

## Falsification Score

Separately from the DDI score, track a **falsification count**: how many of the paper's falsification conditions are currently met?

| # | Condition | Data Check |
|---|-----------|------------|
| F1 | AI-exposed occupations show faster hiring growth than controls | Job openings divergence ≤ 0 AND sector employment divergence ≤ 0 |
| F2 | SaaS NRR stays above 110% | Manual entry (when available) |
| F3 | M2 velocity increases or stabilizes | M2V year-over-year change ≥ 0 |
| F4 | Labor share stabilizes or increases | Labor share year-over-year change ≥ 0 |
| F5 | Delinquency stays at/below historical base rates | Mortgage delinquency < 2.0% |
| F6 | Personal income grows in line with GDP | Income-GDP gap < 1pp |
| F7 | Debt service ratio stable or declining | TDSP year-over-year change ≤ 0 |

Display as: **"X of 7 falsification conditions met"**

- 0–1 met: Thesis is consistent with data
- 2–3 met: Mixed signals, thesis is weakened
- 4–5 met: Thesis is not well supported
- 6–7 met: Thesis is falsified — the displacement scenario is likely wrong or very slow

---

## What This Doesn't Capture

Be honest about the limits:

- **No quintile-level data.** The paper predicts top-heavy impact on high earners. Our API data is aggregate. If displacement hits lower-wage workers instead, our signals would still fire but the mechanism would be different from what the paper describes.
- **No metro-level granularity.** National mortgage delinquency is a blunt instrument. The paper specifically predicts tech-metro stress — we can't see that without manual data.
- **No AI capability scoring.** We track the *effects* of AI capability growth but not the capability itself. If someone enters AI benchmark data manually, it adds context but not score.
- **No new job creation tracking.** The paper's reinstatement mechanism (new job categories absorbing displaced workers) is a key falsification condition. O*NET or LinkedIn data could track this but neither has a free API. This is a gap — if new high-wage job titles are appearing as fast as old ones disappear, the thesis is being falsified and we can't see it automatically.
- **Lag.** Government data is released with 1–3 month delays. These are not real-time signals.

---

## Updating the Thresholds

The signal tables above are initial estimates based on historical ranges. After 4 quarters of data collection, review whether the thresholds make sense:

- Are signals producing meaningful differentiation, or are they all stuck at 0 or all stuck at 1?
- Does the composite score match a reasonable qualitative assessment?
- Are the weights producing a score that's informative, or is one indicator dominating?
- Is the unemployment context multiplier too aggressive or too mild?
- Does the Precautionary Savings Drag fire too often or not enough?

The thresholds should be calibrated, not set in stone. Document any changes in a changelog.
