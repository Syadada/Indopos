# Satgas Waspada Investasi — Investment Fraud Alerts
**Agency:** OJK / Multi-agency task force
**Portal:** https://waspadainvestasi.ojk.go.id
**API type:** ✅ Public list (scrapeable, frequently updated)

## Overview
Official list of illegal investment platforms, unlicensed MLM, robot trading scams, crypto frauds. Updated frequently. High-value data source.

## Usage
```python
import requests
from bs4 import BeautifulSoup

resp = requests.get(
    "https://waspadainvestasi.ojk.go.id/",
    headers={"User-Agent": "Mozilla/5.0 (Windows NT 10.0; Win64; x64)"},
    timeout=30,
)
soup = BeautifulSoup(resp.text, "html.parser")
# Parse alert list table
```

## Gotchas
1. Direct list page is scrapeable
2. High-value, frequently searched
3. Critical for any fintech legitimacy checker
4. Updated weekly — worth automating scrapes
