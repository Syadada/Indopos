# AHU-BO — Beneficial Ownership Registry
**Agency:** Kemenkumham AHU (Administrasi Hukum Umum)
**Portal:** https://bo.ahu.go.id
**API type:** ⚠️ Web search (limited public fields)

## Overview
Public-facing beneficial ownership search integrated with AHU company registry. Part of OECD/G20 transparency push. Shows ultimate beneficial owners of Indonesian legal entities. Launched 2019.

## Usage
```python
import requests
from bs4 import BeautifulSoup

resp = requests.get("https://bo.ahu.go.id/search", params={
    "q": "company name",
}, headers={"User-Agent": "Mozilla/5.0"}, timeout=30)
# Parse HTML response for BO data
```

## Gotchas
1. Basic name search only — limited public fields
2. More detailed data requires official access
3. Complements AHU company registry (ahu.go.id)
