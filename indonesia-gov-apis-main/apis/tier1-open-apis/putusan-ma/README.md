# Putusan Mahkamah Agung — Supreme Court Decisions

**Agency:** Mahkamah Agung RI (Supreme Court of Indonesia)
**Portal:** https://putusan3.mahkamahagung.go.id
**API type:** ✅ Public web search + full-text HTML/PDF access

## Overview

Millions of Indonesian court decisions publicly accessible: civil, criminal, commercial, administrative (TUN), and religious courts. Searchable by keyword, case number, judge, court, and year.

## Search

```python
import requests

BASE = "https://putusan3.mahkamahagung.go.id"

resp = requests.post(
    f"{BASE}/search/index/pencarian/ajax/putusan",
    json={
        "q": "wanprestasi kontrak",
        "tahun": "2024",
        "jenis_doc": "Putusan",
        "page": 1,
    },
    headers={"X-Requested-With": "XMLHttpRequest"},
    timeout=30,
)
results = resp.json()
```

## Pagination

```python
import time

for page in range(1, 50):
    data = requests.post(
        f"{BASE}/search/index/pencarian/ajax/putusan",
        json={"q": "wanprestasi", "tahun": "2023", "page": page},
        headers={"X-Requested-With": "XMLHttpRequest"},
        timeout=30,
    ).json()
    decisions = data.get("data", [])
    if not decisions:
        break
    for d in decisions:
        print(d.get("nomor"), d.get("tanggal_musyawarah"))
    time.sleep(1)
```

## Court Types

| Code | Description |
|------|-------------|
| `PN` | Pengadilan Negeri (District Court) |
| `PT` | Pengadilan Tinggi (High Court) |
| `MA` | Mahkamah Agung (Supreme Court) |
| `PA` | Pengadilan Agama (Religious Court) |
| `PTUN` | Pengadilan Tata Usaha Negara (Administrative Court) |

## Case Types

| Code | Type |
|------|------|
| `Pdt.G` | Civil lawsuit |
| `Pid.B` | Criminal |
| `Pdt.Sus-PHI` | Labor dispute |
| `TUN` | Administrative |
| `Ag` | Religious/Islamic |

## Gotchas

1. **No auth required** — all decisions publicly accessible
2. **POST for search, GET for detail** — different verbs
3. **Millions of records** — use specific queries + year filters
4. **Older decisions are scanned PDFs** — need OCR for text extraction
5. **Rate limit** — 1s delay between pages is sufficient
