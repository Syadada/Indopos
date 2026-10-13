# Portal APBN Kemenkeu — State Budget Data

**Agency:** Kementerian Keuangan (Ministry of Finance)
**Portal:** https://data-apbn.kemenkeu.go.id
**DJPB Portal:** https://djpb.kemenkeu.go.id
**MONEV:** https://monev.anggaran.kemenkeu.go.id
**API type:** ✅ CSV/XLSX downloads + web scraping

## Overview

APBN (Anggaran Pendapatan dan Belanja Negara) revenue and expenditure data published monthly. Covers ministry-level breakdowns, transfers to regions, debt, and financing.

## Download Budget Execution Data

```python
import requests
import pandas as pd
from io import BytesIO

# Scrape index page for current download links
from bs4 import BeautifulSoup

resp = requests.get("https://djpb.kemenkeu.go.id/portal/id/data/apbn-realisasi.html", timeout=30)
soup = BeautifulSoup(resp.text, "html.parser")

for link in soup.select("a[href*='.xlsx'], a[href*='.xls']"):
    url = link["href"]
    name = link.text.strip()
    print(f"{name}: {url}")
    # Download:
    # df = pd.read_excel(requests.get(url).content)
```

## Parse Monthly Excel Report

```python
import openpyxl

wb = openpyxl.load_workbook("apbn-realisasi-jan-2025.xlsx")
for sheet_name in wb.sheetnames:
    ws = wb[sheet_name]
    print(f"Sheet: {sheet_name}")
    for row in ws.iter_rows(min_row=3, max_row=20, values_only=True):
        if row[0]:
            print(row)
```

## Data Categories

| Category | Description |
|----------|-------------|
| Pendapatan Negara | Revenue: taxes, PNBP, grants |
| Belanja Pemerintah Pusat | Central expenditure by ministry |
| Transfer ke Daerah | Regional transfers (DAU, DAK) |
| Pembiayaan Anggaran | Debt issuance and repayment |

## Ministry Codes (sample)

| Code | Ministry |
|------|----------|
| 001 | MPR |
| 004 | BPK |
| 012 | Kemenkumham |
| 015 | Kemenkeu |
| 023 | Kemenaker |
| 024 | Kemenkes |

## Gotchas

1. **Download URLs change each period** — always scrape the index page, not hardcode URLs
2. **Excel format varies by year** — sheet names and column layouts change annually
3. **Data lag** — monthly reports published 2-3 weeks after month end
4. **MONEV** (`monev.anggaran.kemenkeu.go.id`) has sub-activity level granularity
5. **Ministry codes** — 3-digit; full list on Kemenkeu website
