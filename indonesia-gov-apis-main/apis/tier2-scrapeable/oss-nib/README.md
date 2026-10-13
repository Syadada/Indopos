# OSS / NIB — Business Identification Number Lookup

**Agency:** BKPM / OSS (Online Single Submission)
**Portal:** https://oss.go.id
**API type:** ⚠️ Form-based HTML (public lookup) / Login required for full data

## Overview

NIB (Nomor Induk Berusaha) is the master identifier for all licensed businesses in Indonesia. OSS assigns NIBs and links them to KBLI sector codes, risk level (I/II/III/IV), and license types. Since 2018, all new businesses must have NIB.

## Public NIB Lookup

```python
import requests
from bs4 import BeautifulSoup

session = requests.Session()
session.headers["User-Agent"] = "Mozilla/5.0"

# Public NIB search (no login)
resp = session.get("https://oss.go.id/informasi/cari-nib", params={
    "nib": "1234567890123",  # 13-digit NIB
}, timeout=30)

soup = BeautifulSoup(resp.text, "html.parser")
# Parse result table for company name, KBLI codes, risk level
```

## NIB Response Fields (Public)

| Field | Description |
|-------|-------------|
| NIB | 13-digit business ID |
| Nama pelaku usaha | Business name |
| KBLI | Sector codes (KBLI 2020) |
| Skala usaha | Business scale (Mikro/Kecil/Menengah/Besar) |
| Tingkat risiko | Risk level (I=lowest, IV=highest) |
| Status | Aktif / dll |

## KBLI Sector Codes

KBLI (Klasifikasi Baku Lapangan Usaha Indonesia) follows ISIC structure:

```python
# Common KBLI categories
KBLI_GROUPS = {
    "A": "Pertanian, Kehutanan, Perikanan",
    "C": "Industri Pengolahan",
    "F": "Konstruksi",
    "G": "Perdagangan Besar dan Eceran",
    "I": "Penyediaan Akomodasi dan Makan Minum",
    "J": "Informasi dan Komunikasi",
    "K": "Jasa Keuangan dan Asuransi",
    "Q": "Aktivitas Kesehatan",
}
```

## Gotchas

1. **Full license details require login** — OSS account needed for compliance data
2. **NIB is 13 digits** — leading zeros matter; store as string
3. **KBLI 2020** — latest classification; older businesses may have KBLI 2017 codes
4. **Risk level determines license type** — Level I/II = declaration only, III/IV = permit required
5. **Rate limiting** — use delays; OSS blocks aggressive scrapers
6. **DPMPTSP integration** — regional permits link back to OSS NIB
