# pasal.id — Indonesian Law & Regulation MCP Server

> ⚠️ **Third-party project** — pasal.id is a community-maintained open-source project, NOT an official Indonesian government portal. It indexes data from the official peraturan.go.id.

**Provider:** Open source (community-maintained, third-party)
**MCP Server:** https://pasal-mcp-server-production.up.railway.app/mcp
**Source data:** peraturan.go.id (official government regulation portal)
**API type:** 🔵 MCP (Model Context Protocol) + HTTP-callable tools

## Overview

pasal.id provides structured access to 40,143+ Indonesian regulations and 937,155+ individual pasal (articles/clauses). Sourced from peraturan.go.id and processed with OCR correction for scanned PDFs. Updated weekly.

## MCP Setup (Claude Desktop / Claude Code)

```bash
claude mcp add --transport http pasal-id \
  https://pasal-mcp-server-production.up.railway.app/mcp
```

## MCP Tools

| Tool | Description |
|------|-------------|
| `search_laws` | Full-text search across all regulations |
| `get_pasal` | Retrieve a specific article/clause by ID |
| `get_law_status` | Check if a regulation is active, amended, or revoked |

## REST Usage

```python
import requests

MCP_BASE = "https://pasal-mcp-server-production.up.railway.app"

# Search regulations
resp = requests.post(f"{MCP_BASE}/tools/search_laws", json={
    "query": "upah minimum regional",
    "limit": 10,
})
results = resp.json()

# Check if a law is still valid
resp = requests.post(f"{MCP_BASE}/tools/get_law_status", json={
    "law_id": "uu-13-2003",  # UU 13/2003 Ketenagakerjaan
})
print(resp.json())  # {"status": "amended", "amended_by": ["uu-6-2023"]}
```

## Data Coverage

| Type | Description |
|------|-------------|
| UU | Undang-Undang (Parliament Acts) |
| PP | Peraturan Pemerintah (Government Regulations) |
| Perpres | Peraturan Presiden (Presidential Regulations) |
| Permen | Peraturan Menteri (Ministerial Regulations) |
| Perda | Peraturan Daerah (Regional Regulations) |

## Gotchas

1. **Railway-hosted** — may have cold starts (5-10s); add retry logic with backoff
2. **Weekly sync** — very recent regulations may lag 1-7 days
3. **OCR quality varies** — scanned PDFs corrected but not perfect for old docs
4. **No auth required** — fully public
5. **For high-volume production** — consider self-hosting the open-source server
6. **Pasal IDs** — use law number + article number for stable cross-references
