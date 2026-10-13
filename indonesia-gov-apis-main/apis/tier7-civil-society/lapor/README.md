# LAPOR — National Public Complaint System

**Agency:** Kementerian PAN-RB (Ministry of Administrative Reform)
**Portal:** https://www.lapor.go.id
**API type:** ❌ No public API

## Overview

LAPOR (Layanan Aspirasi dan Pengaduan Online Rakyat) is Indonesia's national complaint handling system. Citizens submit complaints about public services, which get routed to relevant agencies.

## No API Available

LAPOR does not provide a public API. The platform is web-only with login required for submission. Data is not available for bulk download.

## What You Can Do

- **Submit complaints** via web form (requires registration)
- **Track complaint status** via tracking ID
- **Browse public complaints** (some are published)

## Potential Scraping

```python
import requests

# Browse published complaints (if publicly visible)
resp = requests.get(
    "https://www.lapor.go.id/laporan",
    headers={"User-Agent": "Mozilla/5.0 (Windows NT 10.0; Win64; x64)"},
    timeout=30,
)
# Limited public data — most complaints require authentication to view
```

## Gotchas

1. **No API** — not useful for data projects
2. **Authentication required** — most data behind login
3. **Government-to-government** — primarily for inter-agency complaint routing
4. **Included for completeness** — unlikely to be useful for data projects
