#!/usr/bin/env python3
"""Search BPJPH halal certification database."""

import requests
import sys


def search_halal(query: str, limit: int = 10):
    """Search halal-certified businesses by supervisor name prefix."""
    resp = requests.post(
        "https://cmsbl.halal.go.id/api/search/data_penyelia",
        json={
            "nama_penyelia": query,
            "start": 0,
            "length": limit,
        },
        headers={"Content-Type": "application/json"},
        timeout=30,
    )
    resp.raise_for_status()
    data = resp.json()
    
    print(f"Total records matching '{query}': {data.get('recordsTotal', 0):,}")
    print()
    
    for biz in data.get("data", []):
        print(f"  {biz.get('nama', 'N/A')}")
        print(f"  📍 {biz.get('alamat', 'N/A')}")
        print(f"  📜 Certificate: {biz.get('nomor_sertifikat', 'N/A')}")
        print(f"  📅 Valid until: {biz.get('berlaku_sampai', 'N/A')}")
        print()


if __name__ == "__main__":
    query = sys.argv[1] if len(sys.argv) > 1 else "Ahmad"
    search_halal(query)
