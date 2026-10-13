# OCCRP Aleph — Global Beneficial Ownership & Leaks
**Organization:** OCCRP (Organized Crime and Corruption Reporting Project)
**Portal:** https://aleph.occrp.org
**API type:** ✅ REST API (free registration)

## API Usage
```python
import requests

API_KEY = "your-api-key"  # Free at aleph.occrp.org
resp = requests.get("https://aleph.occrp.org/api/2/entities", params={
    "q": "company name",
    "filter:schema": "Company",
    "filter:countries": "id",  # Indonesia
}, headers={"Authorization": f"ApiKey {API_KEY}"})
results = resp.json()
```

## Key Data
- Panama Papers, Pandora Papers, and other leaks
- Indonesian company/person data from beneficial ownership disclosures
- Cross-jurisdictional corporate structures

## Rate Limit
60 requests/minute with free API key.

## Gotchas
1. Free API key — register at aleph.occrp.org
2. Contains Indonesian entity data from major leaks
3. Useful for cross-referencing AHU company data
4. Data is from investigations — not official government records
