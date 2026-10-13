# Putusan Mahkamah Konstitusi — Constitutional Court Decisions

**Agency:** Mahkamah Konstitusi RI (Constitutional Court of Indonesia)
**Portal:** https://mkri.id
**Decisions:** https://mkri.id/index.php?page=web.Putusan
**API type:** ✅ Public HTML + PDF download

## Overview

Constitutional review decisions from Indonesia's Constitutional Court. Critical for understanding which laws (UU) have been invalidated, amended, or reinterpreted by judicial review. Smaller volume than Supreme Court but high legal significance.

## Search Decisions

```python
import requests
from bs4 import BeautifulSoup

session = requests.Session()
session.headers["User-Agent"] = "Mozilla/5.0"

resp = session.get("https://mkri.id/index.php", params={
    "page": "web.Putusan",
    "id": "",
    "kat": "1",   # 1 = Putusan, 2 = Ketetapan
    "cari": "upah minimum",
    "hlm": 1,
}, timeout=30)

soup = BeautifulSoup(resp.text, "html.parser")
for row in soup.select("table.table tbody tr"):
    cells = row.find_all("td")
    if len(cells) >= 3:
        print({
            "nomor": cells[0].text.strip(),
            "perihal": cells[1].text.strip(),
            "tanggal": cells[2].text.strip(),
        })
```

## Download Decision PDF

```python
# Decision detail + PDF link
case_id = "003/PUU-IV/2006"
resp = session.get("https://mkri.id/index.php", params={
    "page": "web.Putusan",
    "id": case_id,
})
soup = BeautifulSoup(resp.text, "html.parser")

pdf_link = soup.select_one("a[href$='.pdf']")
if pdf_link:
    pdf_url = "https://mkri.id" + pdf_link["href"]
    pdf_data = session.get(pdf_url, timeout=60).content
    with open(f"mk_{case_id.replace('/', '_')}.pdf", "wb") as f:
        f.write(pdf_data)
```

## Case Types

| Type | Description |
|------|-------------|
| PUU | Pengujian Undang-Undang (Judicial Review) |
| SKLN | Sengketa Kewenangan Lembaga Negara |
| PHPU | Perselisihan Hasil Pemilihan Umum |
| PBB | Pembubaran Partai Politik |

## Relationship to pasal.id / JDIH

MK decisions that strike down or conditionally uphold laws affect the validity of pasal.id data. A regulation may be on peraturan.go.id but partially invalidated by an MK ruling. Always cross-check:

```python
# Workflow: check pasal.id status, then verify against MK decisions
# search MK for the UU number to find any judicial review
resp = session.get("https://mkri.id/index.php", params={
    "page": "web.Putusan",
    "cari": "UU 13/2003",  # Search by law reference
    "kat": "1",
})
```

## Gotchas

1. **No auth required** — all decisions publicly accessible
2. **Parse PDF for pasal references** — HTML summaries don't include full legal analysis
3. **MK decisions have erga omnes effect** — binding on everyone, not just parties
4. **PHPU decisions** — election dispute rulings; high volume during election years
5. **Cross-reference with pasal.id** — pasal.id tracks amended status but may lag MK decisions
