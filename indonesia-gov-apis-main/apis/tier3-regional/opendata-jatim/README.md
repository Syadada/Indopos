# Open Data Jawa Timur — East Java Provincial Open Data

**Agency:** Pemerintah Provinsi Jawa Timur
**Portal:** https://opendata.jatimprov.go.id
**API type:** ✅ CKAN API

## Overview

East Java's CKAN-based open data portal covering economy, infrastructure, employment, population, health, and tourism across 38 kabupaten/kota.

## CKAN API

```python
import requests

CKAN = "https://opendata.jatimprov.go.id/api/3/action"

# List all datasets
resp = requests.get(f"{CKAN}/package_list")
all_datasets = resp.json()["result"]

# Search
resp = requests.get(f"{CKAN}/package_search", params={
    "q": "kemiskinan",
    "rows": 20,
})
results = resp.json()["result"]["results"]

# Datastore query
resp = requests.get(f"{CKAN}/datastore_search", params={
    "resource_id": "resource-id",
    "limit": 500,
    "offset": 0,
})
records = resp.json()["result"]["records"]
```

## Gotchas

1. **Standard CKAN** — all standard CKAN API actions apply
2. **38 kabupaten/kota** — large province; filter by location field in datasets
3. **Dataset freshness varies** — check `metadata_modified` field per dataset
4. **Download formats** — mostly CSV and Excel; some GeoJSON for spatial data
