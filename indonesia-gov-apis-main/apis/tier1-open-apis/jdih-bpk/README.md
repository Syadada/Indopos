# JDIH BPK — National Legal Documentation Network

**Agency:** Badan Pemeriksa Keuangan (BPK) + Perpusnas (National Library)
**BPK Portal:** https://peraturan.bpk.go.id
**Perpusnas API:** https://api-jdih.perpusnas.go.id
**API type:** ✅ Partial JSON API (Perpusnas) + structured HTML scraping (BPK)

## Overview

JDIH (Jaringan Dokumentasi dan Informasi Hukum) is Indonesia's national legal documentation network. Two access points:

- **BPK JDIH** (`peraturan.bpk.go.id`) — most comprehensive, structured HTML
- **Perpusnas JDIH API** (`api-jdih.perpusnas.go.id`) — partial JSON API, paginated

## Perpusnas JDIH API (JSON)

```python
import requests

BASE = "https://api-jdih.perpusnas.go.id"

resp = requests.get(BASE, params={
    "page": 1,
    "type": "peraturan",
    "keyword": "ketenagakerjaan",
})
data = resp.json()

for reg in data.get("data", []):
    print(f"{reg['jenis']} No.{reg['nomor']}/{reg['tahun']}: {reg['judul']}")
```

## BPK JDIH Scraping

```python
from bs4 import BeautifulSoup
import requests

session = requests.Session()
session.headers["User-Agent"] = "Mozilla/5.0"

resp = session.get("https://peraturan.bpk.go.id/Search", params={
    "query": "upah minimum",
    "PerPage": 20,
    "PageNum": 1,
})
soup = BeautifulSoup(resp.text, "html.parser")

for item in soup.select(".regulation-item"):
    title = item.select_one(".title").text.strip()
    year = item.select_one(".year").text.strip()
    print(f"({year}): {title}")
```

## Regulation Types

| Code | Type |
|------|------|
| UU | Undang-Undang |
| PP | Peraturan Pemerintah |
| Perpres | Peraturan Presiden |
| Permen | Peraturan Menteri |
| Perda | Peraturan Daerah |
| Kepres | Keputusan Presiden |

## Gotchas

1. **BPK JDIH is HTML-only** — parse with BeautifulSoup; structure is consistent
2. **Perpusnas API is undocumented** — parameters discovered via DevTools
3. **Some regulations are PDF-only** — full text needs PDF parsing (`pdfplumber`)
4. **Revocation status** may lag — cross-check with pasal.id for live status
5. **No auth required** for either endpoint
