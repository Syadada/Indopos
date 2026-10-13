# Open Data Jabar — Jawa Barat Provincial Open Data

**Agency:** Pemerintah Provinsi Jawa Barat
**Portal:** https://opendata.jabarprov.go.id
**Statistical tables:** https://data.jabarprov.go.id
**API type:** ✅ CKAN API

## Overview

Jawa Barat (West Java) has one of Indonesia's more active regional open data programs. Datasets span agriculture, population, economy, health, and education. Also integrates with Pikobar (COVID-era dashboard) and Jabar Saber Hoaks fact-checking.

## CKAN API

```python
import requests

CKAN = "https://opendata.jabarprov.go.id/api/3/action"

# Search datasets
resp = requests.get(f"{CKAN}/package_search", params={
    "q": "penduduk",
    "rows": 10,
})
for ds in resp.json()["result"]["results"]:
    print(ds["title"])

# Query dataset records
resp = requests.get(f"{CKAN}/datastore_search", params={
    "resource_id": "resource-id-here",
    "limit": 100,
})
records = resp.json()["result"]["records"]
```

## Statistical Tables Portal

```python
# data.jabarprov.go.id has structured statistical tables (non-CKAN)
resp = requests.get("https://data.jabarprov.go.id/api/bigdata/bps/v2", params={
    "kode_provinsi": "32",  # 32 = Jawa Barat
    "kode_kabkota": "3201",  # optional kabupaten/kota filter
    "id_dataset": "dataset-id",
})
```

## Gotchas

1. **Two portals** — CKAN at `opendata.jabarprov.go.id` + stat tables at `data.jabarprov.go.id`
2. **CKAN is primary** — use it for bulk data download
3. **Standard CKAN** — all standard CKAN API actions work
4. **38 kabupaten/kota** in Jawa Barat — largest province by # of cities
5. **Bahasa Indonesia only** — all metadata and data in Indonesian
