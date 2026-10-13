# NTA — Japan Invoice Registry (Reference)

**Agency:** National Tax Agency (Japan) — 国税庁
**Portal:** https://www.invoice-kohyo.nta.go.jp/
**API type:** ✅ REST API (requires Application ID — 4-6 week approval)

> **Note:** This is a Japanese government API included as a reference for the InvoiceCheck project. Not an Indonesian data source.

## Overview

Japan's Qualified Invoice System (インボイス制度) requires businesses to register for tax invoice numbers. The NTA provides a public API to validate these numbers.

## API Application

1. Apply at https://www.invoice-kohyo.nta.go.jp/web/api/
2. Wait 4-6 weeks for Application ID
3. Rate limit: 1 request/second

## Endpoints

```python
import requests

APP_ID = "your-application-id"

# Look up by registration number
resp = requests.get(
    "https://web-api.invoice-kohyo.nta.go.jp/1/num",
    params={
        "id": APP_ID,
        "number": "T1234567890123",  # 13-digit number with T prefix
        "type": "21",                 # 21 = JSON format
    },
    timeout=30,
)
data = resp.json()
```

## Response

```json
{
  "lastUpdateDate": "2026-03-01",
  "count": "1",
  "announcement": [
    {
      "registratedNumber": "T1234567890123",
      "process": "01",
      "correct": "0",
      "kind": "2",
      "country": "",
      "latest": "1",
      "registrationDate": "2023-10-01",
      "updateDate": "2023-10-01",
      "disposalDate": "",
      "expireDate": "",
      "name": "株式会社サンプル",
      "address": "東京都千代田区..."
    }
  ]
}
```

## Gotchas

1. **Application ID required** — 4-6 week approval process
2. **Rate limit: 1 req/s** — strictly enforced
3. **Japanese only** — responses are in Japanese
4. **T-prefix required** — registration numbers start with `T` + 13 digits
5. **Not an Indonesian API** — included for cross-reference only
