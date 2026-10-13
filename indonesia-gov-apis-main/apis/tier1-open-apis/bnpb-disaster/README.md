# BNPB — Disaster Data & Risk Portal

**Agency:** Badan Nasional Penanggulangan Bencana (National Disaster Management Agency)
**Data Portal:** https://data.bnpb.go.id
**InaRisk:** https://inarisk.bnpb.go.id
**API type:** ✅ REST JSON + CKAN-based data portal

## Overview

Two distinct services:
1. **data.bnpb.go.id** — CKAN portal with historical disaster event datasets
2. **InaRisk** — Risk scoring API by geographic coordinates (IRBI index)

## InaRisk — Risk Score by Coordinate

```python
import requests

resp = requests.get("https://inarisk.bnpb.go.id/api/risk/score", params={
    "lat": -6.2088,
    "lon": 106.8456,
}, timeout=15)
risk = resp.json()
# Returns per-hazard risk scores (flood, earthquake, tsunami, landslide, etc.)
print(risk)
```

### Risk Score Response

```json
{
  "location": {"lat": -6.2088, "lon": 106.8456},
  "risks": {
    "banjir": {"score": 3, "class": "Tinggi"},
    "gempa": {"score": 2, "class": "Sedang"},
    "tsunami": {"score": 1, "class": "Rendah"},
    "longsor": {"score": 1, "class": "Rendah"},
    "gunung_api": {"score": 0, "class": "Tidak Ada"}
  },
  "kabupaten": "Jakarta Selatan",
  "provinsi": "DKI Jakarta"
}
```

## IRBI — Disaster Risk Index by Kabupaten (Annual)

```python
resp = requests.get("https://inarisk.bnpb.go.id/api/irbi", params={"tahun": 2023})
irbi_data = resp.json()

top10 = sorted(irbi_data, key=lambda x: x.get("skor_total", 0), reverse=True)[:10]
for area in top10:
    print(f"{area['kabkota']}: {area['skor_total']}")
```

## Historical Events (CKAN)

```python
resp = requests.get("https://data.bnpb.go.id/api/3/action/package_search", params={
    "q": "banjir 2024",
    "rows": 20,
})
for ds in resp.json()["result"]["results"]:
    print(ds["title"], "—", ds.get("num_resources", 0), "resources")
```

## Hazard Types

| Bahasa | English |
|--------|---------|
| `banjir` | Flood |
| `gempa` | Earthquake |
| `tsunami` | Tsunami |
| `longsor` | Landslide |
| `gunung_api` | Volcanic eruption |
| `kekeringan` | Drought |
| `kebakaran_hutan` | Forest fire |

## Gotchas

1. **Two separate systems** — InaRisk API and data.bnpb.go.id CKAN are unrelated
2. **IRBI is annual** — updated once a year, covers all 514 kabupaten/kota
3. **CKAN API** — standard CKAN toolkit applies for the data portal
4. **No auth required** for public endpoints
5. **GeoJSON downloads** available for hazard zone polygons via data portal
