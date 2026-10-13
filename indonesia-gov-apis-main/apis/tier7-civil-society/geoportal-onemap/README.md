# Indonesia Geoportal — One Map Policy
**Agency:** BIG / KLHK / Multiple
**Portal:** https://geoportal.indonesia.go.id
**API type:** ✅ WMS/WFS/REST

## Overview
Implementation of One Map Policy. 85 thematic maps including forest concessions, plantation licenses, HGU land, oil & gas blocks. Critical for spatial compliance.

## Usage
```python
import requests

# WMS GetCapabilities
resp = requests.get("https://geoportal.indonesia.go.id/home", params={
    "service": "WMS",
    "version": "1.1.1",
    "request": "GetCapabilities",
})

# WFS for vector data
resp = requests.get("https://map.big.go.id/wfs", params={
    "service": "WFS",
    "version": "2.0.0",
    "request": "GetFeature",
    "typeName": "ne:batas_desa_desil_all",
    "outputFormat": "json",
    "maxFeatures": 100,
})
```

## Key Layers
- Administrative boundaries (province, kabupaten, desa)
- Forest concessions (HPH, HTI)
- Mining permits (IUP)
- Spatial planning (RTRW)

## Gotchas
1. WMS/WFS services from 20+ agencies
2. `map.big.go.id/wfs` for admin boundary vector data
3. RTRW zoning layers available
4. Some layers require specific agency WMS endpoints
