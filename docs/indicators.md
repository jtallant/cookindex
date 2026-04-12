# Indicators

Each indicator produces a signal strength from 0.0 (no evidence of the paper's thesis) to 1.0 (strong evidence). These are not sequential stages — they are independent measurements of different parts of the same feedback loop. The crisis emerges when multiple indicators reinforce each other simultaneously.

---

## Labor Share (30% weight)

**What it measures:** What fraction of national income goes to workers (wages + benefits) vs. capital owners (profits, interest, rent).

**Why it matters:** This is the paper's central variable. The entire demand-collapse model flows from here. Workers spend ~85% of their income; capital owners spend ~15%. If income shifts from labor to capital, aggregate purchasing power drops even if total income stays the same. It's been declining for decades (from ~65% to ~56%), but a sharp *acceleration* would signal the AI displacement spiral activating.

**FRED series:** `PRS85006173` (Nonfarm Business Sector: Labor Share, Index 2017=100)

**Formula:**
```
recent_rate = year-over-year change in index value
historical_rate = -0.2 index points/year (long-run average decline)
acceleration = recent_rate - historical_rate
```

We measure acceleration, not level. The slow decline has been happening for decades — what matters is whether AI is making it *faster*.

**Signal table:**

| Acceleration beyond baseline | Signal |
|------------------------------|--------|
| ≤ 0 (declining slower than historical or rising) | 0.0 |
| 0–0.2 pp/yr faster | 0.2 |
| 0.2–0.5 pp/yr faster | 0.4 |
| 0.5–1.0 pp/yr faster | 0.7 |
| > 1.0 pp/yr faster | 1.0 |

**Falsification condition (F4):** Labor share stabilizes or increases (YoY change ≥ 0). If productivity gains are flowing to workers, the thesis is weakened.

**Current reading (2025 Q4):** Index at 96.224, down 0.713 from year ago. Acceleration of -0.513 beyond baseline. Signal: **0.7**.

**Design notes:** The index is normalized to 2017=100, not a raw percentage. The actual labor share is around 56% — the index value of 96.2 means it's 96.2% of what it was in 2017. We use the index because it's what FRED provides with consistent quarterly updates.

---

## Job Openings Divergence (15% weight)

**What it measures:** Whether job openings in AI-exposed sectors are declining relative to the overall economy.

**Why it matters:** Hiring freezes show up before layoffs. If companies in tech, finance, and professional services are posting fewer openings while the broader economy holds steady, firms are choosing AI over headcount. This is the earliest displacement signal.

**BLS series (JOLTS):**
- `JTS540099000000000JOL` — Professional & Business Services
- `JTS510099000000000JOL` — Information
- `JTS520000000000000JOL` — Finance & Insurance
- `JTS000000000000000JOL` — Total Nonfarm (control)

**Formula:**
```
exposed_yoy = year-over-year change in composite AI-exposed openings
control_yoy = year-over-year change in total nonfarm openings
divergence = control_yoy - exposed_yoy  (in percentage points)
```

We use absolute percentage-point gap, not a ratio. A ratio blows up when the control rate is near zero — real data showed total nonfarm at +0.16%, which produced a 400%+ ratio from a 0.7pp gap. The pp gap is directly interpretable: "AI-exposed sectors are growing X percentage points slower than the overall economy."

**Signal table:**

| Divergence (pp) | Signal |
|-----------------|--------|
| ≤ 0 (exposed outperforming control) | 0.0 |
| 0–1 pp | 0.1 |
| 1–3 pp | 0.3 |
| 3–5 pp | 0.5 |
| 5–10 pp | 0.7 |
| 10–20 pp | 0.9 |
| > 20 pp | 1.0 |

**Falsification condition (F1):** AI-exposed sectors show faster hiring growth than total nonfarm. If divergence ≤ 0 AND sector employment divergence ≤ 0, the displacement thesis is not supported.

**Current reading (2026-02):** Control -4.97%, exposed +3.63%, divergence -8.60pp. Exposed sectors are *outperforming*. Signal: **0.0**. F1 is not met because sector employment divergence is positive.

---

## Financial Contagion (15% weight)

**What it measures:** Whether the debt system is breaking — specifically, whether mortgage delinquency and household debt service ratios are rising.

**Why it matters:** The paper's most unique contribution. When high-income workers lose income, their fixed debt obligations (mortgages, car payments) don't adjust. A 20% income drop raises prime (780+ FICO) default probability from ~2.1% to ~18.1% — nearly 9x. With $13.17T in residential mortgages exposed, this is where an income shift becomes a financial crisis.

**Two sub-signals, equally weighted:**

### Delinquency sub-signal

**FRED series:** `DRSFRMACBS` (mortgage delinquency), `DRCCLACBS` (credit card delinquency)

| Mortgage delinquency rate | Signal |
|--------------------------|--------|
| < 2.0% | 0.0 |
| 2.0–3.0% | 0.2 |
| 3.0–4.0% | 0.5 |
| 4.0–6.0% | 0.7 |
| > 6.0% | 1.0 |

Credit card delinquency is a leading indicator — it rises before mortgages. If credit card delinquency is rising faster than mortgage delinquency YoY, apply a 1.2x boost.

### Debt service sub-signal

**FRED series:** `TDSP` (household debt service ratio — payments as % of disposable income)

| TDSP | Signal |
|------|--------|
| < 10% | 0.0 |
| 10–11% | 0.2 |
| 11–12% | 0.4 |
| 12–13% | 0.6 |
| 13–14% | 0.8 |
| > 14% | 1.0 |

**Critical pattern:** If TDSP is rising AND compensation as % of GDP (`W270RE1A156NBEA`) is falling, apply a 1.5x boost. This means debt burden is growing while wages shrink — the financial plumbing is leaking.

### Unemployment context multiplier

Applied to the combined signal. The paper's thesis is about *structural* displacement of high-income workers, not cyclical recession.

| Unemployment rate | Multiplier | Rationale |
|-------------------|------------|-----------|
| < 4% | 1.5x | Stress during full employment = high-income displacement |
| 4–5% | 1.3x | Low unemployment, stress is unexpected |
| 5–6% | 1.0x | Normal range |
| > 6% | 0.7x | Broad recession explains the stress |

**Falsification conditions:** F5 (mortgage delinquency < 2.0%), F7 (TDSP not rising YoY).

**Current reading (2025 Q4):** Mortgage delinquency 1.78% (signal 0.0), TDSP 11.32% rising (signal 0.4), unemployment 4.3% (1.3x multiplier). Combined: **0.26**. But F5 is met (delinquency low).

**Design notes:** The `yearAgoValue()` function finds the closest observation to 1 year prior by date, not by offset. The compensation share series (`W270RE1A156NBEA`) is annual — an offset-based lookup (skip 4 observations) jumped 4 years back instead of 1 year, inflating the debt-comp boost. This was caught and fixed.

---

## Sector Employment Divergence (10% weight)

**What it measures:** Whether AI-exposed sectors are actually losing headcount relative to the overall economy.

**Why it matters:** Job openings show hiring *intent*; this shows actual headcount changes. If AI-exposed sectors are shedding workers while the broader economy grows, displacement is confirmed, not just anticipated.

**BLS series (CES):**
- `CES5000000001` — Information
- `CES5500000001` — Financial Activities
- `CES6000000001` — Professional & Business Services
- `CES0000000001` — Total Nonfarm (control)

**Formula:** Same divergence calculation as Job Openings — absolute percentage-point gap.

**Signal table:** Same as Job Openings Divergence.

**Falsification condition (F1):** Combined with Job Openings — if both divergences are ≤ 0, AI-exposed sectors are keeping pace.

**Current reading (2026-03):** Control +0.16%, exposed -0.53%, divergence +0.69pp. Signal: **0.1**. AI-exposed sectors are underperforming, but only slightly. However, 4 consecutive months of decline in the composite.

---

## M2 Velocity (10% weight)

**What it measures:** How many times a dollar circulates through the economy per quarter.

**Why it matters:** When income concentrates in fewer hands, money circulates less — wealthy households save a higher fraction. The paper uses velocity as a long-term macro signal of the demand channel's health. M2V fell from 2.19 in 1997 to ~1.41, though it has been rising slightly since early 2023.

**FRED series:** `M2V`

**Formula:**
```
yoy_change = latest M2V - year-ago M2V
historical_rate = -0.03/year (long-run average decline)
acceleration = yoy_change - historical_rate
```

Same approach as labor share — we measure acceleration beyond the historical trend.

**Signal table:**

| Acceleration beyond baseline | Signal |
|------------------------------|--------|
| ≤ 0 (declining slower or rising) | 0.0 |
| 0–0.02/yr faster decline | 0.2 |
| 0.02–0.05/yr faster | 0.4 |
| 0.05–0.10/yr faster | 0.7 |
| > 0.10/yr faster | 1.0 |

**Falsification condition (F3):** M2V increases or stabilizes (YoY change ≥ 0).

**Current reading (2025 Q4):** M2V at 1.409, up from 1.392 a year ago. Velocity is *rising*. Signal: **0.0**. F3 is met — this is a point against the thesis.

---

## Ghost GDP / Income Wedge (10% weight)

**What it measures:** Whether the economy is producing more than it's paying humans. The gap between Real GDP growth and Real Personal Income (excluding government transfers) growth.

**Why it matters:** This is the paper's "Abundance Paradox" — AI creates output (GDP stays up) but the income from that output flows to capital owners, not workers. The gap is "Ghost GDP" — production whose value stays in corporate treasuries rather than entering human wallets.

**FRED series:** `GDPC1` (Real GDP), `W875RX1` (Real Personal Income Excluding Current Transfer Receipts)

**Why W875RX1 and not PCE (PCEC96):**
- PCE measures what people are *spending*, which is a lagging indicator. People keep spending by draining savings and using credit cards until they can't. By the time PCE drops, the crisis is already advanced.
- W875RX1 measures what the economy is *paying humans*, excluding government transfers (stimulus, welfare). It's a leading indicator of the spending collapse that comes later.
- The original v1 compared nominal GDP against real PCE — an apples-to-oranges error that produced a false 2.88pp gap mostly from inflation.
- The v2 compared real GDP against real PCE — technically correct but PCE is a *component* of GDP (~68%), so the gap mostly measured government spending + investment + net exports, not displacement.
- The v3 (current) compares real GDP against real personal income ex-transfers. This directly measures whether production generates proportional human income.

**Formula:**
```
ghost_gdp_gap = real_gdp_yoy_growth - real_income_ex_transfers_yoy_growth
```

**Signal table:**

| GDP–Income gap | Signal |
|----------------|--------|
| < 0.5 pp | 0.0 |
| 0.5–1.0 pp | 0.2 |
| 1.0–1.5 pp | 0.4 |
| 1.5–2.5 pp | 0.7 |
| > 2.5 pp | 1.0 |

The 0.5pp floor filters normal noise. The 1.5pp mark is the "Acemoglu Zone" — where productivity clearly outstrips income growth.

**Demand erosion convergence gate:** If Ghost GDP signal ≥ 0.5 AND M2V is trending downward, the convergence bonus applies. Ghost GDP is concerning on its own; Ghost GDP plus velocity decay is systemic.

**Current reading (2025 Q4):** Real GDP +1.99%, real income +1.07%, gap +0.92pp. Signal: **0.2**. The economy is producing almost twice as fast (in growth terms) as it's paying people, but it's in the mild range.

---

## Consumer Spending Divergence (5% weight)

**What it measures:** Whether real disposable personal income is falling behind GDP growth, and whether consumers are "employed but scared."

**Why it matters:** Confirms the income→spending transmission. If GDP grows but disposable income doesn't keep up, consumers are either drawing down savings or going into debt to maintain spending — both unsustainable.

**FRED series:** `DSPIC96` (Real Disposable Personal Income), `GDPC1` (Real GDP), `PSAVERT` (Personal Saving Rate), `UMCSENT` (Consumer Sentiment), `UNRATE` (Unemployment Rate)

**Formula:**
```
gap = real_gdp_yoy_growth - real_disposable_income_yoy_growth
```

**Signal table:**

| GDP–Income gap | Signal |
|----------------|--------|
| ≤ 0 | 0.0 |
| 0–1 pp | 0.1 |
| 1–2 pp | 0.3 |
| 2–3 pp | 0.5 |
| 3–5 pp | 0.7 |
| > 5 pp | 1.0 |

### Precautionary Savings Drag (multiplier)

The paper notes that even employed workers may stop spending due to fear of automation. This multiplier detects the "employed but scared" pattern:

- Saving rate > 8% (people hoarding cash)
- Consumer sentiment < 70 (pessimistic)
- Unemployment < 5% (people have jobs)

When all three are true, apply 1.2x to the consumer spending signal.

**Falsification condition (F6):** Income grows in line with GDP (gap < 1pp).

**Current reading:** GDP +1.99%, disposable income +1.06%, gap +0.93pp. Signal: **0.1**. F6 is met. Precautionary drag not active (saving rate is 4.0% — people can't save, not won't).

**Design note:** The current data shows an interesting pattern the methodology doesn't capture: consumer sentiment at 56.6 (very pessimistic) with a saving rate of 4.0% (very low). People are scared but *can't* save. This is arguably worse than the precautionary savings pattern, but the multiplier doesn't fire because it requires high savings. This may need revisiting.
