# LPSE / INAPROC — Government Procurement Data

**Agency:** LKPP (National Procurement Policy Agency)
**Central Portal:** https://inaproc.id (migrated 2026, replaces all lpse.*.go.id)
**API type:** ❌ No public API — all endpoints behind WAF/SSO/CF Turnstile

## ⚠️ Status: FULLY DEGRADED (March 18, 2026)

LKPP has completed migration from individual `lpse.*.go.id` portals to `inaproc.id`. **All legacy portals are now dead or inaccessible.** No public procurement API exists.

### Legacy Portals (ALL dead)

| Domain | Status | Verified |
|--------|--------|----------|
| lpse.lkpp.go.id | ❌ DNS dead | 2026-03-16 |
| lpse.pu.go.id | ❌ DNS dead | 2026-03-16 |
| lpse.kominfo.go.id | ❌ DNS dead | 2026-03-16 |
| lpse.jakarta.go.id | ❌ DNS dead | 2026-03-18 |
| lpse.kemenkeu.go.id | ❌ No response (CNAME → ars.inaproc.id) | 2026-03-18 |
| lpse.kemkes.go.id | ❌ 403 CF (CNAME → ars.inaproc.id) | 2026-03-18 |
| lpse.kemenag.go.id | ❌ No response (CNAME → ars.inaproc.id) | 2026-03-18 |
| sirup.lkpp.go.id | ❌ DNS dead | 2026-03-18 |
| e-katalog.lkpp.go.id | ❌ DNS dead | 2026-03-18 |
| lkpp.go.id | ❌ No response (DNS resolves to 103.55.160.132) | 2026-03-18 |

### inaproc.id Ecosystem (new)

| Subdomain | What | Access | Tech |
|-----------|------|--------|------|
| `spse.inaproc.id` | SPSE portal (procurement directory) | 🔒 Custom WAF — blocks all automation + Jakarta browser | Next.js |
| `api.inaproc.id` | API gateway | ⚠️ Returns 404 on root — routes unknown | Go/Google via header |
| `data.inaproc.id` | Data dashboard | ✅ Accessible via Jakarta proxy | **Streamlit** |
| `sirup.inaproc.id` | RUP (procurement plans) | ⚠️ Login required — redirects to `/sirup/loginctr/index` | Server-rendered |
| `katalog.inaproc.id` | E-Katalog (product catalog) | ⚠️ 200 via curl, 403 from Playwright (CF) | Next.js |
| `ars.inaproc.id` | Admin/backend | 🔒 Pomerium SSO | — |
| `notification.inaproc.id` | Push notifications | ✅ NestJS API (`{"statusCode":404}`) | NestJS |
| `ws-notification.inaproc.id` | WebSocket notifications | 🔒 WSS only | — |
| `asset.inaproc.id` | Static assets (CSS/JS/images) | ✅ Public CDN | — |
| `files.inaproc.id` | File storage | ✅ Public | — |
| `toggle.eproc.dev` | Feature flags | ❌ No response | — |

### Key Findings

1. **`data.inaproc.id`** — Most promising for data access. It's a **Streamlit app** running on the `data` subdomain. Accessible via Jakarta proxy. Health endpoint at `/_stcore/health` returns `ok`. Needs browser rendering to interact.

2. **`katalog.inaproc.id`** — E-Katalog (government product catalog). Returns 200 for paths like `/produk`, `/kategori`, `/cari` via direct curl, but Playwright gets 403 from Cloudflare. May work with proper anti-detection.

3. **`api.inaproc.id`** — An API gateway exists (returns `404 page not found` in plain text, not HTML). Routes are unknown. Common patterns (`/v1`, `/tender`, `/lelang`, etc.) all return 404.

4. **`sirup.inaproc.id`** — SiRUP (procurement plans) migrated here. Login page renders but requires authentication. Previously had public CKAN-style endpoints.

5. **`spse.inaproc.id`** — The main portal. Custom WAF by LKPP blocks even headless browsers from Jakarta IP. Returns "Akses Ditolak!" (Access Denied) page.

## Remaining Data Access Options

### 1. data.inaproc.id (Streamlit Dashboard)
```python
# Streamlit apps can be interacted with via Playwright
# Health check:
# GET https://data.inaproc.id/_stcore/health → "ok"
# Requires Jakarta proxy + browser rendering
```

### 2. katalog.inaproc.id (E-Katalog)
```python
import httpx

# Direct curl works for page content (not API)
# Pages: /produk, /kategori, /cari
# Playwright blocked by CF
async with httpx.AsyncClient(proxy="socks5://127.0.0.1:1080") as client:
    resp = await client.get("https://katalog.inaproc.id/cari", params={"q": "komputer"})
    # Returns Next.js HTML — needs parsing
```

### 3. data.go.id (Open Data)
```python
# Indonesia's open data portal — sometimes has LKPP datasets
# Portal migrated from CKAN to Next.js — old API endpoints return HTML
# GET https://data.go.id → Next.js app (no CKAN API)
```

## Historical: SPSE JSON API (DEAD)

> **Note:** The following endpoints are documented for historical reference only.
> All `lpse.*.go.id/eproc4` portals are now dead or behind authentication.

```python
# THESE NO LONGER WORK — kept for reference
PORTALS = [
    "https://lpse.jakarta.go.id/eproc4",   # DNS dead since 2026-03-18
    "https://lpse.kemenag.go.id/eproc4",    # CNAME ars.inaproc.id, no response
]

# Standard SPSE endpoints (format unchanged, portals unreachable):
# GET /eproc4/dt/rekanan?term=...&draw=1&start=0&length=10
# GET /eproc4/dt/tender?term=...&draw=1&start=0&length=10
```

### Historical SPSE Response Format

<details>
<summary>Vendor (/dt/rekanan) — for reference</summary>

```json
{
  "data": [
    {
      "kodeRekanan": "R-001",
      "namaRekanan": "PT CONTOH SEJAHTERA",
      "npwp": "01.234.567.8-012.000",
      "alamat": "Jl. Sudirman No. 1",
      "kota": "Jakarta",
      "statusAktif": true
    }
  ]
}
```
</details>

<details>
<summary>Tender (/dt/tender) — for reference</summary>

```json
{
  "data": [
    {
      "kode": "T-001",
      "namaPaket": "Pengadaan Komputer",
      "namaSatker": "Dinas Pendidikan DKI Jakarta",
      "nilaiPagu": 1000000000,
      "tahapTender": "Pengumuman"
    }
  ]
}
```
</details>

## Gotchas

1. **All legacy LPSE portals are dead** — do not attempt to scrape `lpse.*.go.id`
2. **inaproc.id uses multiple protection layers** — CF Turnstile, custom WAF, Pomerium SSO
3. **spse.inaproc.id blocks even Jakarta-based Playwright** — "Akses Ditolak!" custom security page
4. **data.inaproc.id (Streamlit) is the most accessible** — needs Jakarta proxy + browser
5. **katalog.inaproc.id has split behavior** — curl 200, Playwright 403 (CF fingerprinting)
6. **api.inaproc.id exists but routes unknown** — returns 404 in plaintext (Go/Google proxy)
7. **data.go.id migrated from CKAN** — old `/api/3/action/*` endpoints no longer work
8. **Migration happened fast** — portals that worked on March 16 were dead by March 18

## civic-stack SDK

```python
from civic_stack.lpse import fetch, search, search_tenders

# ⚠️ MODULE DEGRADED — all portals dead as of 2026-03-18
# Will return empty results or error responses
result = await fetch("PT Garuda Indonesia", proxy_url="socks5h://127.0.0.1:1080")
```

## Next Steps

- [ ] Explore `data.inaproc.id` Streamlit app via Playwright (most promising)
- [ ] Reverse-engineer `katalog.inaproc.id` Next.js data fetching
- [ ] Monitor `api.inaproc.id` for new routes (currently 404)
- [ ] Check if LKPP publishes bulk data downloads anywhere
- [ ] Update civic-stack SDK to mark LPSE module as fully degraded
