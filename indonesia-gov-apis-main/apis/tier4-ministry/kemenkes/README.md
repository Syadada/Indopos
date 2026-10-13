# Kemenkes — Health Data & Facility Registry
**Agency:** Kementerian Kesehatan
**Portal:** https://yankes.kemkes.go.id | https://satusehat.kemkes.go.id
**API type:** ⚠️ Partial API (facility registry public, SATUSEHAT needs registration)

## Facility Registry (Fasyankes)
```python
import requests
resp = requests.get("https://yankes.kemkes.go.id/api/fasyankes", params={
    "nama": "RSUD",
    "jenis": "RS",  # RS=Hospital, Puskesmas, Klinik
})
facilities = resp.json()
```

## SATUSEHAT Platform
New national health data exchange. Developer portal at `developers.kemkes.go.id`. Requires registration for API access.

## Key Data
- Hospital & clinic registry
- Doctor specialization lists
- Disease surveillance data
- Health facility accreditation

## Gotchas
1. Fasyankes registry at `yankes.kemkes.go.id` is publicly searchable
2. SATUSEHAT API requires developer registration
3. Historical health statistics available as Excel downloads
