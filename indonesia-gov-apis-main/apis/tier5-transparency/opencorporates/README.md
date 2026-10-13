# OpenCorporates — Global Company Registry
**Organization:** OpenCorporates Ltd
**Portal:** https://opencorporates.com
**API type:** ✅ REST API (free tier limited, paid for bulk)

## API Usage
```python
import requests

resp = requests.get("https://api.opencorporates.com/v0.4/companies/search", params={
    "q": "company name",
    "jurisdiction_code": "id",  # Indonesia
    "api_token": "your-api-token",
})
companies = resp.json()["results"]["companies"]
```

## Rate Limits
- Free: 500 requests/day
- Paid: higher limits available

## Gotchas
1. Indonesian data sourced from AHU (Kemenkumham)
2. Free tier is limited but sufficient for lookups
3. Structured company data (name, status, officers, filings)
