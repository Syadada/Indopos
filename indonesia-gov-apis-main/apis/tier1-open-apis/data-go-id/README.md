# data.go.id — National Open Data Portal

**Agency:** Satu Data Indonesia (One Data Indonesia)
**Portal:** https://data.go.id
**API type:** ✅ CKAN-based API (standard CKAN endpoints, no auth)

## Overview

Indonesia's national open data portal runs on CKAN (Comprehensive Knowledge Archive Network). It hosts 10,000+ datasets from various government agencies. Standard CKAN API endpoints work.

## API Endpoints

### Search Datasets

```python
import requests

# Search datasets by keyword
resp = requests.get("https://data.go.id/api/3/action/package_search", params={
    "q": "keuangan",      # Search query (Indonesian)
    "rows": 10,            # Results per page
    "start": 0,            # Offset
    "sort": "score desc",  # Relevance sort
})
results = resp.json()["result"]["results"]

for dataset in results:
    print(f"Title: {dataset['title']}")
    print(f"Org: {dataset.get('organization', {}).get('title', 'N/A')}")
    print(f"Resources: {len(dataset.get('resources', []))}")
    print()
```

### Get Dataset Details

```python
# Get a specific dataset by ID or name
resp = requests.get("https://data.go.id/api/3/action/package_show", params={
    "id": "dataset-name-or-id",
})
dataset = resp.json()["result"]

# Access downloadable resources
for resource in dataset["resources"]:
    print(f"  {resource['name']}: {resource['format']} → {resource['url']}")
```

### List Organizations

```python
# List all contributing organizations
resp = requests.get("https://data.go.id/api/3/action/organization_list", params={
    "all_fields": True,
    "limit": 50,
})
orgs = resp.json()["result"]
```

### List Tags/Categories

```python
resp = requests.get("https://data.go.id/api/3/action/tag_list")
tags = resp.json()["result"]
```

## Common Search Queries

| Query | Indonesian Term | Expected Results |
|-------|----------------|-----------------|
| Finance | `keuangan` | Budget, spending data |
| Health | `kesehatan` | Hospital, disease data |
| Education | `pendidikan` | School, enrollment data |
| Population | `penduduk` | Census, demographic data |
| Infrastructure | `infrastruktur` | Roads, buildings |
| Agriculture | `pertanian` | Farming, crops, livestock |
| Tourism | `pariwisata` | Visitor stats |

## Resource Formats

| Format | Frequency | How to Parse |
|--------|-----------|-------------|
| CSV | Common | `pandas.read_csv()` |
| Excel | Very common | `openpyxl`, `pandas.read_excel()` |
| PDF | Common | `pdfplumber`, `pypdf` |
| JSON | Rare | `json.loads()` |
| GeoJSON | Rare | `geopandas` |
| XML | Rare | `lxml`, `xml.etree` |

## Download a Resource

```python
import pandas as pd

# Find a CSV resource and download
for resource in dataset["resources"]:
    if resource["format"].upper() == "CSV":
        df = pd.read_csv(resource["url"])
        print(df.head())
        break
```

## Gotchas

1. **Data quality varies wildly** — some datasets are just links to PDFs
2. **Many datasets are stale** — last updated 2020-2022
3. **Indonesian language only** — search queries should be in Indonesian
4. **No auth required** — fully public API
5. **CKAN standard** — any CKAN tutorial/library works
6. **Large result sets** — use `start` + `rows` for pagination
7. **Some download URLs are broken** — resources may link to dead pages
8. **Encoding** — most files are UTF-8 but some older CSVs are Windows-1252
9. **Rate limiting** — not aggressive, but be polite
