# SIGAP / InaRisk — Disaster Risk Assessment
**Agency:** BNPB (Badan Nasional Penanggulangan Bencana)
**Portal:** https://sigap.bnpb.go.id | https://inarisk.bnpb.go.id
**API type:** ✅ REST API + WMS

## Overview
Indonesia Disaster Risk Index (IRBI) by kabupaten/kota. Flood, earthquake, tsunami, volcanic risk scores. Essential for property and location risk assessment.

## Usage
```python
import requests

# Get risk score by coordinates
resp = requests.get("https://inarisk.bnpb.go.id/api/risk/score", params={
    "lat": -6.2088,
    "lon": 106.8456,
})
risk = resp.json()
# Returns risk scores by hazard type (flood, earthquake, tsunami, etc.)
```

## Gotchas
1. InaRisk REST API: `inarisk.bnpb.go.id/api`
2. Returns risk scores per hazard type for given coordinates
3. WMS layers also available for map visualization
4. Complements BMKG data for comprehensive disaster awareness
