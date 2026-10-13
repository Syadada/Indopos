# Satu Data Jakarta — DKI Jakarta Open Data

**Agency:** Pemerintah Provinsi DKI Jakarta
**Portal:** https://satudata.jakarta.go.id
**Spatial:** https://jakartasatu.jakarta.go.id
**API type:** ✅ CKAN API (best-in-class regional portal)

## Overview

Jakarta's open data portal is the most mature regional CKAN instance in Indonesia. Covers demographics, land use, transport, flood data, air quality, and social services. Also integrates spatial data via jakartasatu.jakarta.go.id.

## CKAN API

```python
import requests

CKAN = "https://satudata.jakarta.go.id/api/3/action"

# Search datasets
resp = requests.get(f"{CKAN}/package_search", params={
    "q": "banjir",
    "rows": 20,
    "start": 0,
})
results = resp.json()["result"]["results"]
for ds in results:
    print(f"{ds['title']} — {ds.get('num_resources', 0)} files")

# Get dataset resources
resp = requests.get(f"{CKAN}/package_show", params={"id": "dataset-slug"})
dataset = resp.json()["result"]
for resource in dataset["resources"]:
    print(f"  {resource['format']}: {resource['url']}")
```

## Datastore Query

```python
# Query a specific resource's data directly
resp = requests.get(f"{CKAN}/datastore_search", params={
    "resource_id": "abc123-resource-id",
    "q": "jakarta selatan",
    "limit": 100,
})
records = resp.json()["result"]["records"]
```

## Notable Datasets

| Dataset | Description |
|---------|-------------|
| Batas administrasi | Administrative boundaries |
| Kependudukan | Population by kelurahan |
| Kualitas udara | Air quality (ISPU) hourly |
| SPBU | Gas station locations |
| Fasilitas kesehatan | Healthcare facilities |
| UMKM Jakarta | Registered SMEs |
| Realisasi APBD | Budget execution |

## Spatial Data (jakartasatu)

```python
# Spatial layers via ArcGIS REST API
resp = requests.get(
    "https://jakartasatu.jakarta.go.id/server/rest/services/apps/Jakartasatu/MapServer",
    params={"f": "json"},
)
layers = resp.json().get("layers", [])
for layer in layers:
    print(f"{layer['id']}: {layer['name']}")
```

## Gotchas

1. **Best regional CKAN in Indonesia** — dataset quality is generally high
2. **Old portal** (`data.jakarta.go.id`) still active — some datasets only there
3. **Spatial layer IDs** — can change; always query the service index first
4. **Air quality data** — real-time ISPU (Indeks Standar Pencemaran Udara) available
5. **Data license** — most datasets are CC-BY; check per dataset
