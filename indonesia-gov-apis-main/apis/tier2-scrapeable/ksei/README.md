# KSEI — Securities Ownership & Investor Statistics

**Agency:** Kustodian Sentral Efek Indonesia (Indonesian Central Securities Depository)
**Portal:** https://www.ksei.co.id
**Statistics:** https://www.ksei.co.id/publikasi/statistik
**API type:** ⚠️ HTML + PDF/XLSX monthly downloads (no individual position data)

## Overview

KSEI is Indonesia's central securities depository. Public data includes aggregate investor statistics by province, sub-registry type, and product (shares, bonds, mutual funds). Individual position data is private — only aggregates are published.

## Scrape Monthly Statistics

```python
import requests
from bs4 import BeautifulSoup
import pandas as pd
from io import BytesIO

session = requests.Session()
session.headers["User-Agent"] = "Mozilla/5.0"

# Get the statistics page to find download links
resp = session.get("https://www.ksei.co.id/publikasi/statistik", timeout=30)
soup = BeautifulSoup(resp.text, "html.parser")

# Find Excel download links
for link in soup.select("a[href*='.xlsx'], a[href*='.xls']"):
    name = link.text.strip()
    url = link.get("href", "")
    if url and "statistik" in url.lower():
        print(f"{name}: {url}")
```

## Download and Parse Stats File

```python
# Direct download of investor count by province
stats_url = "https://www.ksei.co.id/files/statistik/investor-statistics-2025-01.xlsx"
resp = session.get(stats_url, timeout=30)
df = pd.read_excel(BytesIO(resp.content), sheet_name=0)
print(df.head(10))
```

## Available Aggregates

| Dataset | Description |
|---------|-------------|
| Investor count by province | Number of SID holders per province |
| Investor count by sub-registry | Per broker/bank breakdown |
| AKSes statistics | Account access and activity |
| SBN holders | Government bond investor count |
| Reksadana | Mutual fund investor statistics |

## SID (Single Investor Identification)

Each investor in Indonesia has a unique SID. KSEI publishes total SID counts:

```python
# Parse SID growth over time from annual reports
resp = session.get("https://www.ksei.co.id/publikasi/laporan-tahunan", timeout=30)
soup = BeautifulSoup(resp.text, "html.parser")
annual_reports = [(a.text.strip(), a["href"]) for a in soup.select("a[href*='annual']")]
```

## Gotchas

1. **Aggregate only** — individual investor positions are never published
2. **Monthly lag** — statistics published ~2-3 weeks after month end
3. **Excel format changes** — column layouts shift periodically
4. **Sub-registry = broker** — "sub-registry" is the term for broker in KSEI context
5. **AKSes** — KSEI's investor self-service portal (separate from public stats)
6. **See also** — `tier6-financial/ksei-investor` for investor registry stats breakdown
