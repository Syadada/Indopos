# DJPB Treasury — State Treasury & Budget Disbursement

**Agency:** Direktorat Jenderal Perbendaharaan (DJPB), Kementerian Keuangan
**Portal:** https://djpb.kemenkeu.go.id/portal/id/
**Open Data:** https://data.go.id (search "DJPB" or "perbendaharaan")
**API type:** ✅ CKAN API (via data.go.id)

## Overview

DJPB manages state treasury operations including budget disbursement, government account management, and fiscal reporting. Treasury data is published on data.go.id as datasets.

## Data Available

| Dataset | Description | Format |
|---------|-------------|--------|
| Realisasi APBN | Budget execution/disbursement by ministry | CSV/XLSX |
| Laporan Keuangan Pemerintah | Government financial statements | PDF |
| Data SPAN | State payment system transaction summaries | CSV |
| Posisi Kas Negara | State cash position reports | XLSX |

## API Access (via CKAN)

```python
import requests

# Search DJPB datasets on data.go.id
resp = requests.get("https://data.go.id/api/3/action/package_search", params={
    "q": "DJPB perbendaharaan",
    "rows": 10,
})
datasets = resp.json()["result"]["results"]
for ds in datasets:
    print(f"- {ds['title']} ({ds['num_resources']} resources)")
```

## Gotchas

1. **Most data is aggregated** — transaction-level treasury data is not publicly available
2. **Fiscal year alignment** — Indonesian fiscal year = calendar year (Jan-Dec)
3. **Delayed publication** — quarterly/annual reports lag 1-3 months
4. **PDF-heavy** — many reports published as PDF, not machine-readable
5. **SPAN system** — internal treasury system; only summaries are public
