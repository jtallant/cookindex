# Data Sources

All core indicators are fetched automatically via free government APIs (FRED, BLS). Two optional enrichment indicators (SaaS NRR, AI benchmarks) can be manually entered when available but are not required for the index to function.

---

## Tier 1 — Leading Indicators

These are the earliest signals. Changes here show up before layoffs, before earnings misses, before the macro data catches up.

---

### 1. Job Openings in AI-Exposed Sectors

**Why it matters:** Hiring freezes are the canary. Companies stop posting roles before they start cutting them. A sustained decline in openings for professional/business services, information, and finance — the sectors most exposed to AI substitution — is the first visible sign that firms are choosing AI over headcount.

**Sources (all automated via API):**

| Series | Description | API |
|--------|-------------|-----|
| `JTS540099000000000JOL` | JOLTS job openings — Professional & Business Services | BLS |
| `JTS510099000000000JOL` | JOLTS job openings — Information | BLS |
| `JTS520099000000000JOL` | JOLTS job openings — Finance & Insurance | BLS |
| `JTS000000000000000JOL` | JOLTS job openings — Total Nonfarm (control) | BLS |
| `LNS14000006` | Unemployment rate — Management & Professional occupations | BLS |

**How to fetch:** BLS Public Data API v2 (free, API key recommended for higher rate limits).
```
POST https://api.bls.gov/publicAPI/v2/timeseries/data/
Body: { "seriesid": ["JTS540099000000000JOL", ...], "startyear": "2020", "endyear": "2026", "registrationkey": "YOUR_KEY" }
```

**What to track:**
- Month-over-month change in AI-exposed sector openings vs. total nonfarm (control)
- Divergence: AI-exposed declining while total holds steady or grows
- Management/professional unemployment rate trending up

**Threshold:** AI-exposed sector openings decline 10%+ year-over-year while total nonfarm openings are flat or positive, sustained for 3+ months.

**Falsification:** AI-exposed sectors show *faster* hiring growth than total nonfarm through 2028.

---

### 2. M2 Money Velocity

**Why it matters:** Velocity measures how many times a dollar circulates through the economy. When income concentrates in fewer hands (capital owners vs. wage earners), money circulates less — wealthy households save a higher fraction. M2V has fallen from 2.19 in 1997 to ~1.41, though it has been rising slightly since early 2023. A renewed decline would confirm the demand channel weakening.

**Sources (all automated via API):**

| Series | Description | API |
|--------|-------------|-----|
| `M2V` | Velocity of M2 Money Stock | FRED |
| `A067RO1Q156NBEA` | Personal income as % of GDP | FRED |

**How to fetch:** FRED API (free, requires API key registration at fred.stlouisfed.org).
```
GET https://api.stlouisfed.org/fred/series/observations?series_id=M2V&api_key=YOUR_KEY&file_type=json
```

**What to track:**
- Quarterly M2V reading and trend direction
- Personal income as share of GDP — declining share confirms income concentration

**Threshold:** Decline accelerating beyond historical baseline of ~0.03/year.

**Falsification:** M2V increases or stabilizes while GDP grows — money is circulating, the demand channel is intact.

---

### 3. Ghost GDP (Income Wedge)

**Why it matters:** Ghost GDP measures whether the economy is generating human income proportional to its growth. If AI takes a job, GDP stays stable (the AI is producing), but personal income drops (the human isn't being paid). The difference is output whose "wages" (profits) stay in corporate treasuries rather than entering human wallets.

We use Real Personal Income Excluding Transfer Receipts (`W875RX1`) rather than Personal Consumption Expenditures (`PCEC96`) because PCE is a lagging indicator — people keep spending by draining savings and using credit cards until they can't. Income is the leading signal that tells you the dry-up is coming. Excluding transfers strips out government stimulus/welfare so we measure only what the economy pays humans for their labor and investments.

This indicator was originally a multiplier on M2V, but real data showed that velocity can rise (due to high interest rates, infrastructure spending) while income decoupling is already underway. It fires independently now.

**Sources (all automated via API):**

| Series | Description | API |
|--------|-------------|-----|
| `GDPC1` | Real Gross Domestic Product (chained 2017 dollars) | FRED |
| `W875RX1` | Real Personal Income Excluding Current Transfer Receipts (chained 2017 dollars) | FRED |

**How to fetch:** FRED API — same endpoint, different series IDs.
```
GET https://api.stlouisfed.org/fred/series/observations?series_id=W875RX1&api_key=YOUR_KEY&file_type=json
```

**What to track:**
- Year-over-year growth rate of real GDP vs. real personal income (ex-transfers)
- The gap between them (GDP growth minus income growth)
- Whether the gap is widening or narrowing over time

**Threshold:** Gap 0.5–1.5pp = warning (the "Acemoglu Zone" — productivity outstripping wage growth). Gap exceeding 1.5pp = active Ghost GDP. Gap exceeding 2.5pp = critical.

**Falsification:** Personal income (ex-transfers) growth keeps pace with GDP growth (gap < 0.5pp).

---

## Tier 2 — Confirmation Signals

These take longer to show up in the data. They confirm that the mechanisms described in the paper are actually operating at scale.

---

### 4. Labor Share of Income

**Why it matters:** This is the single most important number in the paper. Labor share measures what fraction of national income goes to workers vs. capital owners. The paper's entire demand-collapse model flows from this: if labor share drops, consumption drops, because workers spend ~85% of income while capital owners spend ~15%. It's been declining for decades (from ~65% to ~56%), but a sharp acceleration would signal the AI displacement spiral activating.

**Sources (all automated via API):**

| Series | Description | API |
|--------|-------------|-----|
| `PRS85006173` | Nonfarm Business Sector: Labor Share | FRED |
| `A939RC0Q052SBEA` | Corporate Profits as % of GDP | FRED |
| `W270RE1A156NBEA` | Compensation of Employees as % of GDP | FRED |

**How to fetch:** FRED API.
```
GET https://api.stlouisfed.org/fred/series/observations?series_id=PRS85006173&api_key=YOUR_KEY&file_type=json
```

**What to track:**
- Quarterly labor share reading
- Rate of change — is the decline accelerating beyond the historical ~0.2pp/year trend?
- Corporate profits share rising as mirror confirmation
- Compare against the paper's modeled scenarios (gA = 0.05, 0.20, 0.40)

**Threshold:** Drop below 54% would be a new floor. Decline rate exceeding 0.5 percentage points per year signals acceleration beyond historical trend.

**Falsification:** Labor share stabilizes or increases, meaning productivity gains are flowing to workers.

---

### 5. Consumer Spending & Income Divergence

**Why it matters:** The paper's "consumption concentration amplifier" — the top 20% of earners account for ~59% of aggregate consumption. While we can't get quintile-level breakdowns from an API, we can track aggregate personal consumption vs. personal income. If consumption grows slower than GDP, or personal income diverges from corporate income, the demand erosion is showing up.

**Sources (all automated via API):**

| Series | Description | API |
|--------|-------------|-----|
| `PCE` | Personal Consumption Expenditures | FRED |
| `PCEC96` | Real PCE (chained 2017 dollars) | FRED |
| `PI` | Personal Income | FRED |
| `DSPIC96` | Real Disposable Personal Income | FRED |
| `CP` | Corporate Profits After Tax | FRED |
| `PSAVERT` | Personal Saving Rate | FRED |
| `PCEDG` | PCE: Durable Goods | FRED |
| `UMCSENT` | University of Michigan: Consumer Sentiment | FRED |
| `UNRATE` | Civilian Unemployment Rate | FRED |

**How to fetch:** FRED API — same endpoint, different series IDs.

**What to track:**
- Real GDP growth vs. real PCE growth — divergence = Ghost GDP (production without buyers)
- Real disposable income growth vs. GDP growth — divergence = income not reaching consumers
- Personal consumption growth vs. corporate profit growth — widening gap = concentration
- Durable goods spending — first category to drop when consumers feel squeezed
- **Precautionary Savings Drag:** Personal saving rate above 8% AND consumer sentiment below 70 AND unemployment below 5% = "employed but scared" dynamic where people hoard cash despite having jobs

**Threshold:** Real disposable income growth trails GDP growth by 2+ percentage points for 2+ consecutive quarters. Corporate profits as % of GDP exceeds 13% (historical high).

**Falsification:** Personal income and consumption grow in line with GDP.

---

### 6. Financial Contagion (Delinquency + Debt Service)

**Why it matters:** The paper's most unique contribution is the debt-income mismatch channel. When high-income workers lose income, their mortgage defaults spike nonlinearly — a 20% income reduction raises prime (780+ FICO) default probability from ~2.1% to ~18.1%. But delinquency is a lagging signal. The debt service ratio is a leading one: when income drops but fixed debt payments stay the same, the ratio climbs before defaults materialize. Tracking both gives early and late confirmation of the credit transmission channel.

**Sources (all automated via API):**

| Series | Description | API |
|--------|-------------|-----|
| `DRSFRMACBS` | Delinquency Rate on Single-Family Residential Mortgages | FRED |
| `DRCCLACBS` | Delinquency Rate on Credit Card Loans | FRED |
| `DRCLACBS` | Delinquency Rate on Consumer Loans | FRED |
| `TDSP` | Household Debt Service Payments as % of Disposable Income | FRED |
| `MDSP` | Mortgage Debt Service Payments as % of Disposable Income | FRED |
| `CDSP` | Consumer Debt Service Payments as % of Disposable Income | FRED |
| `W270RE1A156NBEA` | Compensation of Employees as % of GDP | FRED |

**How to fetch:** FRED API.
```
GET https://api.stlouisfed.org/fred/series/observations?series_id=TDSP&api_key=YOUR_KEY&file_type=json
```

**What to track:**
- Mortgage delinquency rate trend — watch for uptick from current lows
- Credit card delinquency — often leads mortgage stress, faster signal
- **Debt service ratio vs. wage growth** — if TDSP is rising while compensation share (W270RE1A156NBEA) is falling, the financial plumbing is leaking. This is the paper's debt-mismatch in one comparison.
- **Unemployment context** — delinquency rising while unemployment stays below 5% is the "high-income displacement" signal. During a broad recession (unemployment > 6%), delinquency stress is expected and less diagnostic.

**Threshold:** TDSP exceeding 12% while compensation share declines. Mortgage delinquency exceeds 3% while unemployment stays below 5%.

**Falsification:** Delinquency stays at or below historical base rates. TDSP stable or declining.

---

### 7. AI-Exposed Sector Employment

**Why it matters:** Direct measurement of whether AI-exposed sectors are actually shedding jobs. The paper predicts that professional services, information/tech, and finance will see employment declines before other sectors. This is the most direct test of the displacement hypothesis.

**Sources (all automated via API):**

| Series | Description | API |
|--------|-------------|-----|
| `CES5000000001` | Employment — Information sector | BLS |
| `CES5500000001` | Employment — Financial Activities | BLS |
| `CES6000000001` | Employment — Professional & Business Services | BLS |
| `CES0000000001` | Employment — Total Nonfarm (control) | BLS |
| `CES6054000001` | Employment — Computer Systems Design | BLS |

**How to fetch:** BLS API v2 — same endpoint as JOLTS, different series IDs.

**What to track:**
- Month-over-month employment change in AI-exposed sectors vs. total nonfarm
- Computer systems design specifically — most directly AI-exposed subsector
- Divergence pattern: AI-exposed flat/declining while total grows

**Threshold:** AI-exposed sectors show negative employment growth for 3+ consecutive months while total nonfarm is positive.

**Falsification:** AI-exposed sectors show employment growth at or above total nonfarm rates through 2028.

---

## Optional Enrichment — Manual Entry

These two indicators add valuable context but require manual data entry since no public API exists. The index works without them.

---

### E1. SaaS Net Revenue Retention (NRR)

**Why it matters:** When businesses replace seat-based SaaS with AI tools, NRR drops below 100%. This is the intermediation collapse signal.

**Source:** Bessemer Cloud Index (bvp.com/cloud-index), KeyBanc SaaS Survey, public earnings reports.

**How to enter:** Paste the median industry NRR value once per quarter.

**Threshold:** Below 110% = pressure building. Below 100% = active contraction.

**Falsification:** NRR stays above 110% with seat-based pricing intact through 2028.

---

### E2. AI Capability Benchmarks

**Why it matters:** The paper's entire model hinges on AI capability growth rate (gA). If benchmarks plateau, the slow scenario wins. If they accelerate, the fast scenario activates.

**Source:** Epoch AI (epoch.ai), METR (metr.org), benchmark leaderboards.

**How to enter:** Record a qualitative assessment (plateau / steady / accelerating) or a composite benchmark score once per quarter.

**Threshold:** Sustained year-over-year improvement exceeding 20% on major benchmarks. Enterprise deployment above 20%.

**Falsification:** Benchmarks plateau or improve at <5% per year.

---

## NAICS Sector Mapping

The "AI-exposed vs. control" divergence comparison is built on NAICS supersectors:

**AI-Exposed:** Information (51), Finance & Insurance (52), Professional/Scientific/Technical Services (54)
**Control:** Total Nonfarm (broadest baseline — divergence naturally emerges when AI-exposed sectors underperform the whole)

Both BLS JOLTS (job openings) and CES (employment) data use these same NAICS supersector codes, so the series IDs map directly.

---

## API Summary

| API | Series Count | Key Required? | Rate Limit | Update Frequency |
|-----|-------------|---------------|------------|------------------|
| FRED | 19 series | Yes (free) | 120 req/min | Quarterly (most), Monthly (some) |
| BLS v2 | 10 series | Yes (free) | 500 req/day | Monthly |
| Manual | 2 values | N/A | N/A | Quarterly (optional) |

**Total automated series: 29.** Two API keys (both free) cover everything. The entire data fetch can run as a single cron job — one FRED call for 19 series, one BLS call for 10 series, done.

### FRED API Key
Register at https://fred.stlouisfed.org/docs/api/api_key.html

### BLS API Key
Register at https://www.bls.gov/developers/home.htm
