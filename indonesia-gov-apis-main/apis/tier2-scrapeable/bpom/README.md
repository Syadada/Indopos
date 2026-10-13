# BPOM — Food, Drug & Cosmetics Registry

**Agency:** Badan Pengawas Obat dan Makanan (National Agency of Drug and Food Control)
**Portal:** https://cekbpom.pom.go.id
**Data size:** 242,000+ registered processed foods ("pangan olahan")
**API type:** ⚠️ DataTables AJAX (requires CSRF token + session cookie)

## Overview

BPOM maintains a registry of approved food products, drugs, cosmetics, and traditional medicines. The public search portal uses jQuery DataTables with server-side processing.

## Search API

### Step 1: Get Session + CSRF Token

```python
import requests
from bs4 import BeautifulSoup

session = requests.Session()
session.headers.update({
    "User-Agent": "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36"
})

# Load the search page to get CSRF token
page = session.get("https://cekbpom.pom.go.id/produk/pangan-olahan", timeout=30)
soup = BeautifulSoup(page.text, "html.parser")
csrf_token = soup.find("meta", attrs={"name": "csrf-token"})["content"]
```

### Step 2: Search via DataTables AJAX

```python
resp = session.post(
    "https://cekbpom.pom.go.id/produk-dt",
    data={
        "_token": csrf_token,
        "draw": 1,
        "start": 0,
        "length": 25,
        "search[value]": "susu",  # Search query
        "columns[0][data]": "no_reg",
        "order[0][column]": 0,
        "order[0][dir]": "asc",
    },
    headers={
        "X-Requested-With": "XMLHttpRequest",
        "Accept": "application/json",
    },
    timeout=30,
)
data = resp.json()
```

### Response Format

```json
{
  "draw": 1,
  "recordsTotal": 242253,
  "recordsFiltered": 1547,
  "data": [
    {
      "no_reg": "MD 123456789012",
      "nama_produk": "Susu Bubuk Example",
      "merk": "BrandName",
      "kemasan": "Kotak 400 g",
      "pendaftar": "PT Example Indonesia",
      "npwp": "01.234.567.8-901.000"
    }
  ]
}
```

### Key Fields

| Field | Description |
|-------|-------------|
| `no_reg` | Registration number (MD/ML prefix) |
| `nama_produk` | Product name |
| `merk` | Brand name |
| `kemasan` | Packaging description |
| `pendaftar` | Registering company |
| `npwp` | Company tax ID (useful for cross-referencing with BPJPH) |

## Product Categories

| URL Path | Category |
|----------|----------|
| `/produk/pangan-olahan` | Processed food (pangan olahan) |
| `/produk/obat` | Drugs/medicines |
| `/produk/obat-tradisional` | Traditional medicines/jamu |
| `/produk/kosmetik` | Cosmetics |
| `/produk/suplemen-kesehatan` | Health supplements |

Each category has its own DataTables endpoint with the same pattern.

## Bulk Scraping

```python
import time

offset = 0
page_size = 100
all_products = []

while True:
    resp = session.post(
        "https://cekbpom.pom.go.id/produk-dt",
        data={
            "_token": csrf_token,
            "draw": offset // page_size + 1,
            "start": offset,
            "length": page_size,
            "search[value]": "",  # Empty = all products
        },
        headers={"X-Requested-With": "XMLHttpRequest"},
        timeout=30,
    )
    data = resp.json()
    records = data.get("data", [])
    if not records:
        break
    all_products.extend(records)
    offset += len(records)
    
    # CSRF token may expire — refresh periodically
    if offset % 10000 == 0:
        page = session.get("https://cekbpom.pom.go.id/produk/pangan-olahan")
        soup = BeautifulSoup(page.text, "html.parser")
        csrf_token = soup.find("meta", attrs={"name": "csrf-token"})["content"]
    
    time.sleep(2)  # Be polite — BPOM rate-limits aggressively
```

## Cross-Referencing with BPJPH

BPOM's `npwp` field can be used to match companies across BPJPH's halal database:

```python
# Normalize NPWP for matching
def normalize_npwp(npwp: str) -> str:
    return npwp.replace(".", "").replace("-", "").strip()

# Match BPOM company to BPJPH business
bpom_npwp = normalize_npwp(product["npwp"])
# Look up in BPJPH data...
```

## Gotchas

1. **CSRF token required** — must load page first, extract from `<meta>` tag
2. **Session cookies required** — use `requests.Session()`, not raw `requests.get()`
3. **CSRF expires** — refresh every ~10,000 requests or after 30 minutes
4. **Rate limiting is aggressive** — 2s delay minimum, 5s recommended
5. **DataTables format** — must send proper DataTables parameters
6. **X-Requested-With header** — required for AJAX endpoint
7. **Max page size** — officially 100, larger may be rejected
8. **Data quality** — some records have empty NPWP or garbled text
9. **IP blocking** — datacenter IPs frequently blocked
