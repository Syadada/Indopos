# KKP — Fisheries & Maritime Data

**Agency:** Kementerian Kelautan dan Perikanan (Ministry of Marine Affairs and Fisheries)
**Portal:** https://satudata.kkp.go.id
**Statistics:** https://statistik.kkp.go.id
**API type:** ⚠️ XLSX + web tables (early SDI adopter)

## Overview

Fish catch statistics, aquaculture production, fishing vessel registry, marine protected areas (KKP was an early adopter of Satu Data Indonesia). Data covers both capture fisheries and aquaculture.

## Statistics Portal

```python
import requests
from bs4 import BeautifulSoup
import pandas as pd
from io import BytesIO

session = requests.Session()
session.headers["User-Agent"] = "Mozilla/5.0"

# Browse annual fisheries statistics
resp = session.get("https://statistik.kkp.go.id/home.php", timeout=30)
soup = BeautifulSoup(resp.text, "html.parser")

for link in soup.select("a[href*='download'], a[href$='.xlsx']"):
    print(link.text.strip(), ":", link.get("href", ""))
```

## Key Datasets

| Dataset | Description | Frequency |
|---------|-------------|-----------|
| Produksi perikanan tangkap | Capture fisheries production by species | Annual |
| Produksi perikanan budidaya | Aquaculture production by commodity | Annual |
| Kapal perikanan | Fishing vessel registry | Annual |
| Nilai ekspor hasil laut | Seafood export value | Monthly |
| Kawasan konservasi laut | Marine protected areas | Per designation |
| Pelabuhan perikanan | Fishing port statistics | Annual |

## Marine Protected Areas (KKP)

```python
# KKP publishes MPA (Kawasan Konservasi Perairan) data
resp = session.get("https://kkp.go.id/djprl/p4k/page/3-data-kawasan-konservasi", timeout=30)
soup = BeautifulSoup(resp.text, "html.parser")
# Parse table of designated marine protected areas
```

## Fish Price Monitoring

```python
# Daily fish price at major fishing ports
resp = requests.get("https://satudata.kkp.go.id/api/v1/harga-ikan", params={
    "tanggal": "2025-03-01",
    "pelabuhan": "Muara Baru",
})
```

## Gotchas

1. **SDI early adopter** — KKP was among first ministries on Satu Data; some API structure available
2. **Annual dominance** — most production data is annual; monthly data for prices/exports
3. **Vessel registry** — full vessel registry needs formal data request; aggregate stats public
4. **Export value** — good for commodity price trends; published monthly
5. **BRSDM data** — research arm has additional datasets at `brsdm.kkp.go.id`
