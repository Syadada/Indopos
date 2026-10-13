# ESDM — Energy & Mining Data

**Agency:** Kementerian Energi dan Sumber Daya Mineral
**Portal:** https://www.esdm.go.id/id/statistik-dan-riset
**Mining permits:** https://minerba.esdm.go.id/public
**API type:** ⚠️ PDF/XLSX annual handbooks + mining permit registry

## Overview

Energy production and consumption by sector, oil & gas lifting, electricity generation, coal and mineral production, and mining permit (IUP) registry.

## Handbook of Energy Statistics

```python
import requests
from bs4 import BeautifulSoup

session = requests.Session()
session.headers["User-Agent"] = "Mozilla/5.0"

# Find annual Handbook of Energy Statistics download
resp = session.get("https://www.esdm.go.id/id/statistik-dan-riset/publikasi/handbook-of-energy", timeout=30)
soup = BeautifulSoup(resp.text, "html.parser")

for link in soup.select("a[href$='.pdf'], a[href$='.xlsx']"):
    name = link.text.strip()
    if "handbook" in name.lower() or "energi" in name.lower():
        print(f"{name}: {link['href']}")
```

## Mining Permit Registry (IUP)

```python
# Public mining permit registry
resp = requests.get("https://minerba.esdm.go.id/public/iup/list", params={
    "status": "aktif",
    "page": 1,
    "limit": 50,
}, timeout=30)
permits = resp.json()

for permit in permits.get("data", []):
    print(f"{permit['perusahaan']} — {permit['komoditas']} — {permit['luas_ha']} ha")
```

## Key Data Available

| Dataset | Source | Format |
|---------|--------|--------|
| Oil & gas lifting | Handbook | PDF/XLSX |
| Coal production by company | Handbook | PDF/XLSX |
| Electricity generation by source | Handbook | PDF/XLSX |
| Energy consumption by sector | Handbook | PDF/XLSX |
| Active mining permits (IUP) | minerba.esdm.go.id | Web/JSON |
| Oil & gas block assignments | esdm.go.id | PDF |

## Electricity Data (PLN Integration)

```python
# PLN publishes electricity statistics separately
resp = requests.get("https://www.pln.co.id/tentang-pln/statistik", timeout=30)
# Contains: generation capacity, production, sales by customer category
```

## Gotchas

1. **Handbook is annual** — published once a year; use for multi-year trend analysis
2. **Mining permit data** — `minerba.esdm.go.id/public` is the public-facing IUP registry
3. **PDF-heavy** — most statistical tables are embedded in PDFs; parse with `pdfplumber`
4. **Oil & gas contract data** — upstream contracts (PSC) are at SKK Migas, not ESDM
5. **EITI cross-reference** — see `tier5-transparency/eiti-indonesia` for verified production data
