# BMKG — Meteorology, Climatology & Geophysics Data

**Agency:** Badan Meteorologi, Klimatologi, dan Geofisika
**Portal:** https://data.bmkg.go.id
**API type:** ✅ REST JSON + XML (no auth required)

## Overview

BMKG provides real-time public feeds for weather forecasts, earthquake events, and tsunami alerts. No API key or registration required.

## Earthquake Data

```python
import requests

# Latest significant earthquake
resp = requests.get("https://data.bmkg.go.id/DataMKG/TEWS/autogempa.json", timeout=10)
quake = resp.json()["Infogempa"]["gempa"]
print(f"M{quake['Magnitude']} — {quake['Wilayah']} @ {quake['Tanggal']} {quake['Jam']}")

# Last 15 earthquakes (M >= 5.0)
resp15 = requests.get("https://data.bmkg.go.id/DataMKG/TEWS/gempaterkini.json", timeout=10)
for q in resp15.json()["Infogempa"]["gempa"]:
    print(f"{q['Tanggal']} {q['Jam']} — M{q['Magnitude']} {q['Wilayah']}")
```

### Earthquake Response Fields

| Field | Description |
|-------|-------------|
| `Tanggal` | Date (WIB) |
| `Jam` | Time (WIB) |
| `Magnitude` | Richter magnitude |
| `Kedalaman` | Depth (km) |
| `Lintang` / `Bujur` | Latitude / Longitude |
| `Wilayah` | Region description |
| `Potensi` | Tsunami potential |

## Weather Forecast (3-Day by Province)

```python
import requests
import xml.etree.ElementTree as ET

# Province slug examples: DKIJakarta, JawaBarat, JawaTimur, Bali, Aceh, ...
# Full list: 34 provinces in camelCase with no spaces
province = "DKIJakarta"
url = f"https://data.bmkg.go.id/DataMKG/MEWS/DigitalForecast/DigitalForecast-{province}.xml"

resp = requests.get(url, timeout=15)
root = ET.fromstring(resp.content)

for area in root.findall(".//area"):
    name = area.get("description")
    temps = [p for p in area.findall("parameter") if p.get("id") == "t"]
    if temps:
        values = [v.text for v in temps[0].findall("timerange/value")]
        print(f"{name}: {values}")
```

## Endpoints Summary

| Endpoint | Data | Update |
|----------|------|--------|
| `/DataMKG/TEWS/autogempa.json` | Latest earthquake | Real-time |
| `/DataMKG/TEWS/gempaterkini.json` | Last 15 earthquakes ≥M5 | Real-time |
| `/DataMKG/TEWS/tsunamigempa.json` | Active tsunami alert | Real-time |
| `/DataMKG/MEWS/DigitalForecast/DigitalForecast-{Province}.xml` | 3-day forecast | 6-hourly |

## Gotchas

1. **No auth required** — fully public feeds
2. **XML for forecasts, JSON for earthquakes** — different formats per data type
3. **Times are WIB (UTC+7)** — convert to UTC if needed
4. **Province names are camelCase** — `DKIJakarta`, `JawaBarat`, not `DKI Jakarta`
5. **Tsunami feed returns `{}` when no alert** — check before parsing
6. **Forecast XML can be large** — 100KB+ per province; filter by area if possible
