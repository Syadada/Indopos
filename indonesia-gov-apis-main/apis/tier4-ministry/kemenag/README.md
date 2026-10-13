# Kemenag — Religious Affairs Data
**Agency:** Kementerian Agama
**Portal:** https://simas.kemenag.go.id | https://emis.kemenag.go.id
**API type:** ⚠️ Scraping (web search interfaces)

## Mosque Registry (SIMAS)
```python
import requests
from bs4 import BeautifulSoup

resp = requests.get("https://simas.kemenag.go.id/search", params={
    "q": "masjid agung",
}, headers={"User-Agent": "Mozilla/5.0"}, timeout=30)
# Parse HTML response
```

## Pesantren & Madrasah (EMIS)
- Pesantren registry: `emis.kemenag.go.id/emis_sdm`
- Madrasah data: integrated with education statistics

## Key Data
- 300,000+ mosques registered in SIMAS
- Pesantren locations and student counts
- Madrasah accreditation data

## Gotchas
1. SIMAS is the most complete mosque database in Indonesia
2. EMIS has pesantren and madrasah data
3. Web scraping only — no public API
4. Useful for cross-referencing halal supply chain data
