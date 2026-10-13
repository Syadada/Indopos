# Satu Data Komdigi — Digital & Telecoms Data

**Agency:** Kementerian Komunikasi dan Digital (Ministry of Digital Affairs)
**Portal:** https://data.komdigi.go.id/opendata
**API type:** ✅ XLSX/CSV downloads

## Overview

Internet penetration, broadband subscriber counts, press freedom index, 4G village coverage, digital literacy metrics, and spectrum allocation data.

## Access Data

```python
import requests
from bs4 import BeautifulSoup
import pandas as pd
from io import BytesIO

session = requests.Session()
session.headers["User-Agent"] = "Mozilla/5.0"

# Browse open data catalog
resp = session.get("https://data.komdigi.go.id/opendata", timeout=30)
soup = BeautifulSoup(resp.text, "html.parser")

# Find download links
for link in soup.select("a[href*='.xlsx'], a[href*='.csv']"):
    print(link.text.strip(), ":", link["href"])
```

## Key Datasets

| Dataset | Description | Freq |
|---------|-------------|------|
| Penetrasi internet | Internet penetration by province | Annual |
| Pelanggan broadband | Broadband subscribers by ISP | Quarterly |
| Desa 4G | Village 4G coverage progress | Monthly |
| Literasi digital | Digital literacy index by province | Annual |
| Siaran pers | Press release data and announcements | Continuous |

## Digital Village Coverage

```python
# Track 4G village coverage rollout
resp = session.get("https://data.komdigi.go.id/opendata/desa-broadband", timeout=30)
soup = BeautifulSoup(resp.text, "html.parser")
download_link = soup.select_one("a.download-btn")
if download_link:
    df = pd.read_excel(BytesIO(session.get(download_link["href"], timeout=30).content))
    print(f"Total desa covered: {df['covered'].sum()}")
```

## Gotchas

1. **Ministry recently renamed** — was Kominfo (Komisi Informasi), now Komdigi; URLs changing
2. **XLSX format** — primary download format; use `pandas.read_excel()` or `openpyxl`
3. **Annual cadence** — most indicators updated annually
4. **ISP data** — operator-level subscriber data is aggregate, not per-user
5. **PDNS incident** — 2024 ransomware attack on national data center; some data may be affected
