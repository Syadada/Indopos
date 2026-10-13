# Satu Data Surabaya — Surabaya City Open Data

**Agency:** Pemerintah Kota Surabaya
**Portal:** https://satudata.surabaya.go.id
**Mirror:** https://opendata.surabaya.go.id
**API type:** ✅ CKAN API

## Overview

One of Indonesia's most complete city-level open data portals. Surabaya was an early adopter of open data. Covers demographics, UMKM registry, social welfare, public facilities, and city budget.

## CKAN API

```python
import requests

CKAN = "https://satudata.surabaya.go.id/api/3/action"

# Search
resp = requests.get(f"{CKAN}/package_search", params={
    "q": "UMKM",
    "rows": 20,
})
for ds in resp.json()["result"]["results"]:
    print(f"{ds['title']} — {ds.get('metadata_modified', 'N/A')}")

# Direct datastore access
resp = requests.get(f"{CKAN}/datastore_search", params={
    "resource_id": "resource-id",
    "limit": 1000,
})
```

## Notable Datasets

| Dataset | Description |
|---------|-------------|
| Data kependudukan | Population by RT/RW |
| UMKM terdaftar | Registered micro-enterprises |
| Fasilitas umum | Public facilities with coordinates |
| Realisasi APBD | City budget execution |
| Perizinan | Business permits issued |

## Gotchas

1. **Two URLs** — `satudata.surabaya.go.id` (primary) and `opendata.surabaya.go.id` (may redirect)
2. **e-Kinerja integration** — city performance data available alongside open data
3. **Data granularity** — some datasets go down to RT/RW level (very fine-grained)
4. **UMKM registry** — one of the more complete city-level SME registries
