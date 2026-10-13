# KPK e-LHKPN — Public Officials Wealth Declarations

**Agency:** Komisi Pemberantasan Korupsi (Corruption Eradication Commission)
**Portal:** https://elhkpn.kpk.go.id
**API type:** ⚠️ Web search + PDF download

## Overview

LHKPN (Laporan Harta Kekayaan Penyelenggara Negara) are mandatory wealth declarations filed by all Indonesian public officials: ministers, DPR/DPRD members, judges, KPK officials, regional heads, and state-owned enterprise directors.

## Search Official by Name

```python
import requests
from bs4 import BeautifulSoup

session = requests.Session()
session.headers["User-Agent"] = "Mozilla/5.0"

# Search by name
resp = session.get("https://elhkpn.kpk.go.id/portal/user/search_pejabat", params={
    "nama": "Basuki Tjahaja",
    "instansi": "",
    "jabatan": "",
}, timeout=30)

soup = BeautifulSoup(resp.text, "html.parser")
for row in soup.select(".result-item"):
    name = row.select_one(".nama").text.strip()
    institution = row.select_one(".instansi").text.strip()
    detail_url = row.select_one("a")["href"]
    print(f"{name} — {institution}: {detail_url}")
```

## Get Report Detail

```python
# LHKPN detail page (HTML summary + PDF download)
resp = session.get("https://elhkpn.kpk.go.id/register/detail/12345", timeout=30)
soup = BeautifulSoup(resp.text, "html.parser")

# Extract declared assets summary
total_assets = soup.select_one(".total-harta").text.strip()
print(f"Total declared assets: {total_assets}")

# PDF download link
pdf_link = soup.select_one("a[href*='.pdf']")
if pdf_link:
    pdf_resp = session.get(pdf_link["href"], timeout=60)
    with open("lhkpn.pdf", "wb") as f:
        f.write(pdf_resp.content)
```

## Data Fields Available

| Field | Description |
|-------|-------------|
| Nama | Official's name |
| Jabatan | Current position |
| Instansi | Government institution |
| Tahun lapor | Reporting year |
| Total harta | Total declared net worth |
| Tanah/bangunan | Land and buildings value |
| Kendaraan | Vehicle value |
| Surat berharga | Securities value |
| Kas/setara | Cash and equivalents |
| Hutang | Declared debts |

## Gotchas

1. **Annual submission cycles** — data lags by 1 year (officials declare previous year's wealth)
2. **PDF format** — full reports are PDFs; parse with `pdfplumber` for structured data
3. **Self-declared** — data is as accurate as the official's declaration; KPK audits selectively
4. **Coverage** — covers ~300,000+ officials but completeness varies by institution
5. **Name search is fuzzy** — partial name matching works; use institution filter to narrow
6. **Historical data** — multiple years available per official; year is a filter parameter
