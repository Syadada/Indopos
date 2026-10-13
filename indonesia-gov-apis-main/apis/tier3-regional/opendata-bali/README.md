# Open Data Bali — Bali Provincial Open Data

**Agency:** Pemerintah Provinsi Bali
**Portal:** https://data.baliprov.go.id
**API type:** ⚠️ CSV/XLSX downloads (less mature API)

## Overview

Bali's provincial open data portal covering tourism statistics, land use, population, and agriculture. Less mature than Jakarta/Jabar — primarily static file downloads rather than live API queries.

## Access Data

```python
import requests
from bs4 import BeautifulSoup
import pandas as pd
from io import BytesIO

session = requests.Session()
session.headers["User-Agent"] = "Mozilla/5.0"

# Browse dataset catalog
resp = session.get("https://data.baliprov.go.id/dataset", timeout=30)
soup = BeautifulSoup(resp.text, "html.parser")

for ds in soup.select(".dataset-item"):
    title = ds.select_one("h3").text.strip()
    link = ds.select_one("a")["href"]
    print(f"{title}: https://data.baliprov.go.id{link}")

# Download a specific CSV dataset
csv_url = "https://data.baliprov.go.id/dataset/.../resource/.../download/data.csv"
df = pd.read_csv(BytesIO(session.get(csv_url, timeout=30).content))
print(df.head())
```

## Notable Datasets

| Dataset | Description |
|---------|-------------|
| Kunjungan wisatawan | Tourist arrival statistics by origin |
| Penginapan | Accommodation capacity by kabupaten |
| Pertanian | Agricultural production by crop type |
| Kependudukan | Population by district |
| Lahan | Land use classification |

## Gotchas

1. **Less mature** — primarily file downloads, limited live API
2. **Tourism focus** — best data is tourism-related (Bali's primary sector)
3. **Annual cadence** — most datasets updated annually, not monthly
4. **BPS Bali** (`bali.bps.go.id`) is more reliable for statistical data than the portal
5. **CKAN may be present** — check `/api/3/action/package_list` as some portals do expose CKAN
