# AHU Online — Company Registry

**Agency:** Kementerian Hukum dan HAM / Ditjen AHU
**Portal:** https://ahu.go.id
**ISPU Portal:** https://ispu.ahu.go.id
**API type:** ⚠️ Form-based HTML scraping (CAPTCHA on detailed views)

## Overview

Registry of all legally registered Indonesian business entities: PT (limited company), CV, Firma, Koperasi. Shows company name, NIB, shareholders, directors, notary, and status. Core source for entity verification.

## Basic Company Search

```python
import requests
from bs4 import BeautifulSoup

session = requests.Session()
session.headers["User-Agent"] = "Mozilla/5.0"

# Load search page
resp = session.get("https://ahu.go.id/pencarian/cari-pt", timeout=30)
soup = BeautifulSoup(resp.text, "html.parser")

# Get CSRF token if present
csrf = soup.find("input", {"name": "_token"})
token = csrf["value"] if csrf else None

# Submit search
resp = session.post("https://ahu.go.id/pencarian/cari-pt", data={
    "_token": token,
    "nama": "PT Maju Bersama",
    "status": "aktif",
}, timeout=30)

soup = BeautifulSoup(resp.text, "html.parser")
for row in soup.select("table tbody tr"):
    cells = row.find_all("td")
    if len(cells) >= 3:
        print({
            "nama": cells[0].text.strip(),
            "nomor_akta": cells[1].text.strip(),
            "status": cells[2].text.strip(),
        })
```

## Key Data Fields (Basic Public View)

| Field | Description |
|-------|-------------|
| Nama perusahaan | Company name |
| Nomor AHU | AHU registration number |
| Status | Aktif / Dibubarkan / dll |
| Tanggal berdiri | Establishment date |
| Modal dasar | Authorized capital |
| Direktur | Directors |
| Komisaris | Commissioners |

## Beneficial Ownership (Separate — see `tier5-transparency/ahu-bo`)

The beneficial ownership register is at https://bo.ahu.go.id — separate from the main AHU registry.

## Gotchas

1. **CAPTCHA on detailed reports** — basic search is accessible; full akta details are behind CAPTCHA or paid service
2. **Selenium may be required** for dynamic pages
3. **Free basic info** — paid option for full ownership history
4. **NIB linkage** — use OSS/NIB to cross-reference business license status
5. **CV and Firma** use different search paths than PT
6. **SIMBA** (`ispu.ahu.go.id`) is the detailed document system — requires login
