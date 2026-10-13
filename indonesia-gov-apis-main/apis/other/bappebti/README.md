# BAPPEBTI — Commodities Futures & Crypto Regulation

**Agency:** Badan Pengawas Perdagangan Berjangka Komoditi
**Portal:** https://bappebti.go.id
**API type:** ❌ HTML scraping only

## Overview

BAPPEBTI regulates commodities futures trading and (since 2019) cryptocurrency exchanges in Indonesia. Maintains lists of licensed brokers and approved crypto assets.

## Licensed Futures Brokers

```python
import requests
from bs4 import BeautifulSoup

resp = requests.get(
    "https://bappebti.go.id/pialang_berjangka",
    headers={"User-Agent": "Mozilla/5.0 (Windows NT 10.0; Win64; x64)"},
    timeout=30,
)
soup = BeautifulSoup(resp.text, "html.parser")

for row in soup.select("table tbody tr"):
    cols = [td.text.strip() for td in row.find_all("td")]
    if cols:
        print(f"Broker: {cols[0]}")
```

## Licensed Crypto Exchanges

```python
resp = requests.get(
    "https://bappebti.go.id/pedagang_fisik_aset_kripto",
    headers={"User-Agent": "Mozilla/5.0 (Windows NT 10.0; Win64; x64)"},
    timeout=30,
)
# Parse table of licensed crypto exchanges
```

## Approved Crypto Assets

BAPPEBTI publishes a list of cryptocurrencies approved for trading in Indonesia:

```python
resp = requests.get(
    "https://bappebti.go.id/resources/docs/aset_kripto_yang_dapat_diperdagangkan.pdf",
    timeout=30,
)
# Download PDF of approved crypto assets
```

## Key Pages

| URL Path | Content |
|----------|---------|
| `/pialang_berjangka` | Licensed futures brokers |
| `/pedagang_fisik_aset_kripto` | Licensed crypto exchanges |
| `/bursa_berjangka` | Futures exchanges |
| `/lembaga_kliring` | Clearing houses |

## Gotchas

1. **No API** — pure HTML scraping
2. **Crypto regulation transferred** — as of 2025, crypto regulation is transitioning to OJK. Data may move.
3. **PDF-heavy** — approved asset lists are PDFs
4. **Infrequent updates** — lists change quarterly at most
5. **Simple HTML** — no JavaScript rendering needed, `requests` + `BeautifulSoup` sufficient
