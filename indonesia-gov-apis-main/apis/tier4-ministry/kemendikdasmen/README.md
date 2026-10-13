# Kemendikdasmen — Education Data
**Agency:** Kementerian Pendidikan Dasar dan Menengah
**Portal:** https://data.kemendikdasmen.go.id | https://referensi.data.kemdikbud.go.id
**API type:** ⚠️ Partial API (school registry has REST, full DAPODIK needs partnership)

## School Registry API
```python
import requests
resp = requests.get("https://referensi.data.kemdikbud.go.id/api/sekolah", params={
    "nama": "SMA Negeri 1",
    "propinsi": "030000",  # DKI Jakarta
})
schools = resp.json()
```

## Key Data
- School registry (NPSN — unique school ID)
- Student counts by school
- Teacher registry (GTK)
- Accreditation status

## Gotchas
1. `referensi.data.kemdikbud.go.id` has school search API — no auth needed
2. Full DAPODIK data requires partnership
3. Province codes follow BPS coding system
