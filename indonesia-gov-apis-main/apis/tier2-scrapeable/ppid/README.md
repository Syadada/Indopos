# e-PPID — Public Information Request Portal

**Agency:** All ministries and agencies (Kemkominfo coordination)
**Entry point:** https://ppid.kominfo.go.id (national directory)
**Per-agency:** https://ppid.[ministry].go.id
**API type:** ⚠️ Web portal per ministry (no central API)

## Overview

PPID (Pejabat Pengelola Informasi dan Dokumentasi) is Indonesia's public information disclosure system under UU 14/2008. Each government body has its own PPID portal where citizens can request documents and data. Useful for understanding what data exists even before it's published openly.

## Ministry PPID Portals (Sample)

| Ministry | URL |
|----------|-----|
| Kominfo (coordinator) | https://ppid.kominfo.go.id |
| Kemenkeu | https://ppid.kemenkeu.go.id |
| Kemenkumham | https://ppid.kemenkumham.go.id |
| BPS | https://ppid.bps.go.id |
| Kemenkes | https://ppid.kemkes.go.id |
| OJK | https://ppid.ojk.go.id |

## Scrape Published Information Lists (DIP)

Each PPID portal publishes a Daftar Informasi Publik (DIP) — the list of information they are obligated to provide:

```python
import requests
from bs4 import BeautifulSoup

# Example: BPS PPID
resp = requests.get("https://ppid.bps.go.id/daftar-informasi-publik", timeout=30)
soup = BeautifulSoup(resp.text, "html.parser")

for item in soup.select(".info-item, table tbody tr"):
    cells = item.find_all("td")
    if cells:
        print({
            "title": cells[0].text.strip(),
            "category": cells[1].text.strip() if len(cells) > 1 else "",
        })
```

## Submit a Request

Under UU 14/2008, agencies must respond within 10 working days (extendable to 17):

1. Register on the ministry's PPID portal
2. Submit request with: information description, intended use, format preference
3. Agency acknowledges within 1 day
4. Response due within 10 working days
5. Appeal to Komisi Informasi if denied

## Track Request Status

```python
# Most portals have a tracking endpoint
resp = requests.get("https://ppid.kominfo.go.id/tracking", params={
    "ticket": "REQ-2025-001234",
})
```

## Gotchas

1. **No central API** — each ministry runs independent PPID software
2. **Varied software** — some use PPID standard software, others custom builds
3. **10-day response SLA** — legally mandated but compliance varies
4. **DIP lists what exists** — request from DIP first; non-DIP requests take longer
5. **Exclusions** — confidential info (state secrets, privacy, commercial) can be refused
6. **Komisi Informasi** — appeal body if request is denied; decisions are binding
