# Open Data Kota Bandung — Bandung City Open Data

**Agency:** Pemerintah Kota Bandung
**Portal:** https://opendata.bandung.go.id
**API type:** ✅ CKAN API

## Overview

Bandung was one of Indonesia's earliest adopters of open data, driven by its smart city program. Datasets include air quality, traffic conditions, budget, social services, and UMKM data.

## CKAN API

```python
import requests

CKAN = "https://opendata.bandung.go.id/api/3/action"

# Search datasets
resp = requests.get(f"{CKAN}/package_search", params={
    "q": "kualitas udara",
    "rows": 10,
})
for ds in resp.json()["result"]["results"]:
    print(ds["title"])

# Get dataset details
resp = requests.get(f"{CKAN}/package_show", params={"id": "dataset-slug"})
dataset = resp.json()["result"]
```

## Gotchas

1. **Early adopter** — dataset catalog is mature but some older datasets are stale
2. **Smart city data** — IoT sensor data (air quality, traffic) available in some datasets
3. **Standard CKAN** — all standard API actions work
4. **Bahasa Indonesia** — all metadata in Indonesian
