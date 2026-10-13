# BIG Geospatial / INA-SDI — National Geospatial Data

**Agency:** Badan Informasi Geospasial (Geospatial Information Agency)
**Portal:** https://tanahair.indonesia.go.id
**WFS Endpoint:** https://map.big.go.id/wfs
**INA-SDI:** https://geoservices.ina-sdi.or.id
**API type:** ✅ OGC WMS/WFS standard services (no auth required)

## Overview

Authoritative Indonesian geospatial data: administrative boundaries, topography, land use, and RTRW (spatial planning) zoning. Accessible via standard OGC web services.

## Administrative Boundaries via WFS

```python
import requests

WFS = "https://map.big.go.id/wfs"

resp = requests.get(WFS, params={
    "service": "WFS",
    "version": "2.0.0",
    "request": "GetFeature",
    "typeName": "ne:batas_desa_desil_all",
    "outputFormat": "application/json",
    "count": 100,
    "CQL_FILTER": "PROVINSI='DKI JAKARTA'",
})
geojson = resp.json()

for feature in geojson["features"]:
    p = feature["properties"]
    print(f"{p.get('DESA')} — {p.get('KECAMATAN')} — {p.get('KABKOT')}")
```

## Point-in-Polygon (coordinate to admin unit)

```python
def get_admin_unit(lat, lon):
    resp = requests.get(WFS, params={
        "service": "WFS",
        "version": "2.0.0",
        "request": "GetFeature",
        "typeName": "ne:batas_desa_desil_all",
        "outputFormat": "application/json",
        "CQL_FILTER": f"CONTAINS(the_geom, POINT({lon} {lat}))",
    })
    features = resp.json().get("features", [])
    return features[0]["properties"] if features else None

admin = get_admin_unit(-6.2088, 106.8456)  # Jakarta
```

## Available WFS Layers

| Layer Name | Description |
|-----------|-------------|
| `ne:batas_desa_desil_all` | Village/kelurahan boundaries |
| `ne:batas_kecamatan_desil_all` | Sub-district boundaries |
| `ne:batas_kabkota_desil_all` | City/regency boundaries |
| `ne:batas_provinsi_desil_all` | Provincial boundaries |
| `ne:rtrw_kawasan_budidaya` | RTRW development zones |
| `ne:rtrw_kawasan_lindung` | RTRW protected areas |

## WMS Map Tiles

```python
resp = requests.get("https://map.big.go.id/wms", params={
    "service": "WMS",
    "version": "1.3.0",
    "request": "GetMap",
    "layers": "ne:batas_provinsi_desil_all",
    "bbox": "95.0,-11.0,141.0,6.0",
    "width": 800,
    "height": 400,
    "crs": "EPSG:4326",
    "format": "image/png",
})
with open("map.png", "wb") as f:
    f.write(resp.content)
```

## Gotchas

1. **No auth required** for WFS/WMS
2. **CRS is EPSG:4326** — standard WGS84 lat/lon
3. **Large datasets** — use `count` and `CQL_FILTER` to limit response size
4. **RTRW data gaps** — not all kabupaten/kota have digitized zoning data yet
5. **Slow for province-level polygons** — can take 10-30s; cache results
6. **Layer names may change** — run `GetCapabilities` to verify current layers
