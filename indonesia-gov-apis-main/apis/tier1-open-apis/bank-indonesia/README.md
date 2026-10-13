# Bank Indonesia — Central Bank Data

**Agency:** Bank Indonesia (BI)
**Portal:** https://www.bi.go.id/id/statistik/
**API Portal:** https://dataapi.bi.go.id/
**API type:** ✅ REST API (some endpoints require registration)

## Exchange Rates (JISDOR)

BI publishes daily USD/IDR reference rates (Jakarta Interbank Spot Dollar Rate).

### Via API Portal

```python
import requests

# BI exchange rate API
resp = requests.get("https://dataapi.bi.go.id/dataexchange/v1/exchange-rates", params={
    "date": "2026-03-07",
    "currency": "USD",
})
rates = resp.json()
```

### Via Web Scraping (More Reliable)

```python
from bs4 import BeautifulSoup

# Scrape the KURS page
resp = requests.get(
    "https://www.bi.go.id/id/statistik/informasi-kurs/transaksi-bi/Default.aspx",
    headers={"User-Agent": "Mozilla/5.0 (Windows NT 10.0; Win64; x64)"},
    timeout=30,
)
soup = BeautifulSoup(resp.text, "html.parser")
table = soup.find("table", class_="table1")
if table:
    for row in table.find_all("tr")[1:]:
        cols = [td.text.strip() for td in row.find_all("td")]
        if len(cols) >= 4:
            currency = cols[0]
            buy = cols[1]
            sell = cols[2]
            print(f"{currency}: Buy {buy} / Sell {sell}")
```

## BI Rate (Interest Rate)

```python
# Scrape BI Rate history
resp = requests.get(
    "https://www.bi.go.id/id/statistik/indikator/bi-rate.aspx",
    headers={"User-Agent": "Mozilla/5.0 (Windows NT 10.0; Win64; x64)"},
    timeout=30,
)
# Parse HTML table for historical BI Rate data
```

## Key Data Available

| Data | Source | Format |
|------|--------|--------|
| USD/IDR Exchange Rate | JISDOR | API + HTML |
| BI Rate (7-Day RR) | Monetary page | HTML |
| Inflation | Monetary statistics | HTML + Excel |
| Money Supply (M1, M2) | Monetary statistics | Excel |
| Payment System | Digital payments stats | Excel |
| Banking Statistics | SLIK | Restricted |

## Data API Portal (dataapi.bi.go.id)

BI's newer API portal provides structured access:

```python
# List available datasets
resp = requests.get("https://dataapi.bi.go.id/dataexchange/v1/datasets")

# Get specific dataset
resp = requests.get("https://dataapi.bi.go.id/dataexchange/v1/datasets/{dataset_id}/data", params={
    "startDate": "2026-01-01",
    "endDate": "2026-03-07",
})
```

## Gotchas

1. **API portal is relatively new** — documentation is improving but still sparse
2. **ASP.NET ViewState** — web scraping requires handling ViewState tokens
3. **Exchange rates** — JISDOR is published at 10:00 WIB (03:00 UTC) each trading day
4. **No weekend/holiday rates** — exchange rates only on business days
5. **Data API registration** — some endpoints require free registration
6. **Excel downloads** — most detailed statistical data is Excel-only
7. **HTTPS required** — BI blocks HTTP connections
8. **Rate limiting** — gentle, but don't hammer the endpoints
