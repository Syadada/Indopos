# BPS — Statistics Indonesia

**Agency:** Badan Pusat Statistik (Central Bureau of Statistics)
**Portal:** https://www.bps.go.id
**API Portal:** https://webapi.bps.go.id/developer/
**API type:** ✅ REST API (free registration, API key required)

## Getting an API Key

1. Register at https://webapi.bps.go.id/developer/
2. Create an application
3. Get your API key (free)

## Base URL

```
https://webapi.bps.go.id/v1/api
```

## Endpoints

### List Available Datasets

```python
import requests

API_KEY = "your-api-key"
BASE = "https://webapi.bps.go.id/v1/api"

# List all variables/indicators
resp = requests.get(f"{BASE}/list/model/var/domain/0000/key/{API_KEY}")
variables = resp.json()
```

### Get Statistical Data

```python
# Get data for a specific variable
# Example: CPI/Inflation (var=1)
resp = requests.get(f"{BASE}/list/model/data/domain/0000/var/1/key/{API_KEY}")
data = resp.json()

for item in data.get("data", []):
    print(f"{item.get('tahun')}: {item.get('data_content')}")
```

### Domain Codes

| Code | Domain |
|------|--------|
| `0000` | National (Indonesia) |
| `3100` | DKI Jakarta |
| `3200` | Jawa Barat |
| `3300` | Jawa Tengah |
| `3500` | Jawa Timur |
| `5100` | Bali |

Full list: 34 provinces, each with `XX00` code pattern.

### Key Variables

| var | Description | Frequency |
|-----|-------------|-----------|
| 1 | Consumer Price Index (CPI) | Monthly |
| 2 | GDP at Current Prices | Quarterly |
| 3 | GDP at Constant Prices | Quarterly |
| 104 | Population | Annual |
| 517 | Imports by Commodity | Monthly |
| 518 | Exports by Commodity | Monthly |

## Dynamic Table API

For more flexible queries:

```python
# Get a specific BPS table
resp = requests.get(f"{BASE}/list/model/data/domain/0000/var/1/th/2025/key/{API_KEY}")
```

### Parameters

| Param | Description |
|-------|-------------|
| `domain` | Geographic domain code |
| `var` | Variable/indicator ID |
| `th` | Year filter |
| `turvar` | Derived variable |
| `vervar` | Vertical variable |

## BPS Publications

BPS also publishes reports as PDFs:

```python
# List publications
resp = requests.get(f"{BASE}/list/model/publication/domain/0000/key/{API_KEY}", params={
    "page": 1,
})
publications = resp.json()
```

## Gotchas

1. **Rate limit:** ~100 requests/day per API key (generous for data, not for scraping)
2. **Data lag:** Most indicators lag 1-3 months
3. **Documentation is sparse** — trial and error required for variable IDs
4. **Some data only in Excel/PDF** — not all datasets are API-accessible
5. **Domain codes matter** — national data uses `0000`, provinces use `XX00`
6. **API returns HTML on error** — check response content-type
7. **Variable IDs are not sequential** — use the list endpoint to discover them
8. **Time series gaps** — some variables have missing years
