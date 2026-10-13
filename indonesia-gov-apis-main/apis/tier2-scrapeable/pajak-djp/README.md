# Pajak.go.id / DJP — Tax Authority Data

**Agency:** Direktorat Jenderal Pajak (DGT — Directorate General of Taxes)
**Portal:** https://pajak.go.id
**NPWP Verification:** https://ereg.pajak.go.id/ceknpwp
**Coretax:** https://coretax.pajak.go.id (2024+)
**API type:** ❌ Login required for full data / ✅ NPWP basic validation is public

## Overview

DJP manages Indonesia's tax system. Full compliance data requires authentication. Public utility: NPWP (tax ID) format validation and basic entity lookup. The 2024 Coretax migration improved NPWP-NIK integration.

## NPWP Format Validation (Public)

```python
import re

def validate_npwp_format(npwp: str) -> bool:
    """NPWP format: XX.XXX.XXX.X-XXX.XXX (15 digits)"""
    cleaned = npwp.replace(".", "").replace("-", "")
    return len(cleaned) == 15 and cleaned.isdigit()

def normalize_npwp(npwp: str) -> str:
    cleaned = npwp.replace(".", "").replace("-", "").strip()
    if len(cleaned) == 15:
        return f"{cleaned[:2]}.{cleaned[2:5]}.{cleaned[5:8]}.{cleaned[8]}-{cleaned[9:12]}.{cleaned[12:]}"
    return npwp
```

## NPWP Basic Lookup (e-Registration)

```python
import requests
from bs4 import BeautifulSoup

session = requests.Session()
session.headers["User-Agent"] = "Mozilla/5.0"

resp = session.get("https://ereg.pajak.go.id/ceknpwp", params={
    "npwp": "01.234.567.8-901.000",
}, timeout=30)

soup = BeautifulSoup(resp.text, "html.parser")
# Returns: basic validity + registered name (no full compliance data)
status = soup.select_one(".npwp-status")
if status:
    print(status.text.strip())
```

## NIK-NPWP Integration (Post-Coretax 2024)

Since 2024, individual taxpayers' NPWP is their 16-digit NIK (national ID):

```python
# Individual NPWP now equals NIK for citizens
# Corporate NPWP remains 15-digit format

def is_individual_taxpayer(npwp: str) -> bool:
    cleaned = npwp.replace(".", "").replace("-", "")
    # Individual: 3rd digit is 0 or 9; also check for 16-digit NIK format
    return cleaned[2] in ("0", "9") or len(cleaned) == 16
```

## Entity Cross-Reference

NPWP appears in many other government datasets and can be used as a cross-reference key:

| Source | NPWP Field |
|--------|-----------|
| BPOM | `npwp` field in product registry |
| AHU | Company registration data |
| OSS/NIB | Business entity NPWP |
| LPSE | Vendor registration |

## Gotchas

1. **Full compliance data needs API partnership** — DJP data exchange requires formal agreement
2. **NPWP as join key** — excellent cross-reference across government databases
3. **Coretax 2024** — major system migration; old e-filing endpoints changed
4. **Individual vs corporate** — different format patterns; validate before matching
5. **NIK = NPWP** for individuals since 2024 — significant for de-duplication
6. **No public bulk lookup** — one at a time; bulk needs API partnership
