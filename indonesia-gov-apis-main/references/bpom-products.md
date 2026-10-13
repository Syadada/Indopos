# BPOM Food, Drug & Cosmetics Registry

**Updated: 2026-03-16** — Site redesigned, old product detail URLs (`/index.php/home/produk/0/{id}`) return 404.

## Endpoints

### DataTables Search (server-side)

`POST https://cekbpom.pom.go.id/produk-dt/all`

Requires: `XSRF-TOKEN` cookie + `X-XSRF-TOKEN` header + `webreg_session` cookie.

### CSRF Setup

```python
import httpx
from urllib.parse import unquote

client = httpx.Client()
# Get session + XSRF token
resp = client.get("https://cekbpom.pom.go.id/all-produk")
xsrf = unquote(resp.cookies.get("XSRF-TOKEN", ""))
cookies = {
    "XSRF-TOKEN": resp.cookies["XSRF-TOKEN"],
    "webreg_session": resp.cookies["webreg_session"],
}
```

### Search

```python
resp = client.post(
    "https://cekbpom.pom.go.id/produk-dt/all",
    data={
        "draw": "1",
        "start": "0",
        "length": "25",
        "search[value]": "susu",
        "search[regex]": "false",
    },
    headers={
        "X-XSRF-TOKEN": xsrf,
        "Referer": "https://cekbpom.pom.go.id/all-produk",
    },
    cookies=cookies,
    timeout=30,
)

data = resp.json()
print(f"Total: {data['recordsTotal']}, Filtered: {data['recordsFiltered']}")
for p in data["data"]:
    print(f"{p['PRODUCT_REGISTER']} | {p['PRODUCT_NAME']} | {p.get('REGISTRAR', '')}")
```

## Category-Specific Pages

Each category has its own search page (same DataTables pattern, different CSRF page):

| Page | Category | DT Endpoint |
|------|----------|-------------|
| `/all-produk` | All products | `/produk-dt/all` |
| `/produk-obat` | Drugs | `/produk-dt/obat` (unverified) |
| `/produk-obat-tradisional` | Traditional medicine | `/produk-dt/obat-tradisional` (unverified) |
| `/produk-obat-kuasi` | Quasi-drugs | `/produk-dt/obat-kuasi` (unverified) |
| `/produk-suplemen-kesehatan` | Supplements | `/produk-dt/suplemen` (unverified) |
| `/produk-kosmetika` | Cosmetics | `/produk-dt/kosmetika` (unverified) |
| `/produk-pangan-olahan` | Processed food | `/produk-dt/pangan` (unverified) |

## Response Fields

| Field | Type | Description |
|-------|------|-------------|
| `ID` | int | Internal BPOM record ID |
| `PRODUCT_ID` | string | Product identifier (e.g., "ERBA300311202600079") |
| `PRODUCT_REGISTER` | string | Registration number (MD/ML prefix) |
| `PRODUCT_NAME` | string | Product name |
| `CLASS` | string | Product class code |
| `CLASS_ID` | string | Class ID |
| `APPLICATION` | string | Application type (e.g., "e-Registration Pangan Olahan") |
| `APPLICATION_ID` | string | Application type ID |
| `REGISTRAR` | string | Registering company (may be null) |
| `REGISTRAR_NPWP` | string | Company tax ID (may be null) |
| `MANUFACTURER` | string | Manufacturer (may be null) |
| `BRAND` | string | Brand name (may be null) |

## Detail Page (new format)

Individual product detail: `https://cekbpom.pom.go.id/produk/:productId/:applicationId/detail`

Values from DataTables response fields `PRODUCT_ID` and `APPLICATION_ID`.

**Note:** Old detail URLs (`/index.php/home/produk/0/{registration_no}/10/1/0`) all return 404 as of 2026-03.

## Gotchas

- **CSRF required** — XSRF-TOKEN expires after ~30min. Re-fetch the search page to refresh.
- **Laravel framework** — uses standard Laravel CSRF via `XSRF-TOKEN` cookie + `X-XSRF-TOKEN` header.
- **No `_token` in form body** — unlike older Laravel apps, this one uses the cookie-based XSRF approach.
- **Rate limit** — ~2s minimum between requests (aggressive blocking observed).
- **Max page size** — `length=100` seems to be max; default is 10.
- **`Referer` header** — required or you get 419 Page Expired.
- **Old URLs dead** — `/index.php/home/produk/0/{id}`, `/index.php/home/produk/1` all 404.
- **639K+ total products** as of 2026-03-16.
- **NPWP field** useful for cross-referencing with BPJPH halal data.

## civic-stack SDK

```python
from modules.bpom.scraper import search, fetch

# Search
results = await search("susu", length=20)
for r in results:
    if r.found:
        print(r.result["registration_no"], r.result["product_name"])

# Fetch by registration number (uses search internally)
result = await fetch("ML 270911111300291")
print(result.found, result.result)
```
