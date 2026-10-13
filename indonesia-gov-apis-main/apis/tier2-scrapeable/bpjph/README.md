# BPJPH — Halal Certification Database

**Agency:** Badan Penyelenggara Jaminan Produk Halal (Ministry of Religious Affairs)
**Portal:** https://halal.go.id
**Data size:** ~1.98 million businesses, ~9.8 million certificates
**API type:** ✅ JSON REST API (no auth for public search)

## Endpoints

| Endpoint | Base URL | Purpose |
|----------|----------|---------|
| Penyelia Search | `https://cmsbl.halal.go.id/api/search/data_penyelia` | Business search by supervisor — best for bulk scraping |
| General Search | `https://cmsbl.halal.go.id/api/search` | General search (slow at high offsets) |
| Certificate List | `https://prod-api-si.halal.go.id/api/v2/dashboard/halal-certificate-list` | Official cert API |
| Public Site | `https://bpjph.halal.go.id` | Gatsby frontend (search UI) |

## Penyelia Search API (Recommended)

Best endpoint for bulk data extraction. Supports filtering by supervisor name prefix.

### Request

```python
import requests

resp = requests.post(
    "https://cmsbl.halal.go.id/api/search/data_penyelia",
    json={
        "nama_penyelia": "Ahmad",  # Supervisor name filter
        "start": 0,                # Offset
        "length": 100,             # Page size (max 100)
    },
    headers={"Content-Type": "application/json"},
    timeout=30,
)
data = resp.json()
```

### Response

```json
{
  "recordsTotal": 45231,
  "recordsFiltered": 45231,
  "data": [
    {
      "id": 12345,
      "nama": "PT Example Indonesia",
      "alamat": "Jl. Sudirman No. 1, Jakarta",
      "propinsi": "DKI Jakarta",
      "kota_kab": "Jakarta Selatan",
      "nama_penyelia_halal": "Ahmad Sudirman",
      "nomor_sertifikat": "LPPOM-00123456789-01",
      "berlaku_sampai": "2027-12-31"
    }
  ]
}
```

### Key Fields

| Field | Description |
|-------|-------------|
| `nama` | Business name |
| `alamat` | Address |
| `propinsi` | Province |
| `kota_kab` | City/regency |
| `nama_penyelia_halal` | Halal supervisor name |
| `nomor_sertifikat` | Certificate number |
| `berlaku_sampai` | Expiry date |

## Bulk Scraping Strategy

The general search endpoint degrades badly at high offsets (>100K). Use filter-based pagination instead:

```python
import string
import time

# Iterate through supervisor name prefixes
for letter in string.ascii_uppercase:
    offset = 0
    while True:
        resp = requests.post(
            "https://cmsbl.halal.go.id/api/search/data_penyelia",
            json={
                "nama_penyelia": letter,
                "start": offset,
                "length": 100,
            },
            headers={"Content-Type": "application/json"},
            timeout=30,
        )
        data = resp.json()
        records = data.get("data", [])
        if not records:
            break
        # Process records...
        offset += len(records)
        time.sleep(1)  # Be polite
```

### Performance Numbers (observed)
- ~116,000 records/hour with 1s delay
- Total dataset: ~1.98M businesses
- Full scrape: ~17 hours

## Certificate List API

Higher-level API with certificate-centric data. 9.8M records.

```python
resp = requests.get(
    "https://prod-api-si.halal.go.id/api/v2/dashboard/halal-certificate-list",
    params={
        "page": 1,
        "per_page": 50,
        "search": "keyword",
    },
    headers={
        "Accept": "application/json",
    },
    timeout=30,
)
```

## Gotchas

1. **No auth required** for public search endpoints
2. **Rate limiting is lenient** — 1 req/s is safe, faster may work
3. **Offset-based pagination degrades** — use filter-based approach for bulk
4. **Data can be stale** — certificate status may lag behind reality
5. **`cmsbl.halal.go.id`** is a Node.js backend (not the Gatsby frontend)
6. **Duplicate records exist** — deduplicate by certificate number
7. **Indonesian characters** are UTF-8 but some records have encoding issues
