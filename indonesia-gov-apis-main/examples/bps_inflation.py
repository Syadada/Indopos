#!/usr/bin/env python3
"""Get inflation data from BPS (Statistics Indonesia)."""

import requests
import sys
import os


def get_inflation(api_key: str):
    """Fetch CPI/inflation data from BPS API."""
    base = "https://webapi.bps.go.id/v1/api"
    
    resp = requests.get(
        f"{base}/list/model/data/domain/0000/var/1/key/{api_key}",
        timeout=30,
    )
    resp.raise_for_status()
    data = resp.json()
    
    if "data" not in data:
        print(f"Unexpected response: {data}")
        return
    
    print(f"{'Year':<8} {'Value':>10}")
    print("-" * 20)
    
    for item in data["data"]:
        year = item.get("tahun", "N/A")
        value = item.get("data_content", "N/A")
        print(f"{year:<8} {value:>10}")


if __name__ == "__main__":
    api_key = os.environ.get("BPS_API_KEY") or (sys.argv[1] if len(sys.argv) > 1 else None)
    if not api_key:
        print("Usage: BPS_API_KEY=xxx python bps_inflation.py")
        print("   or: python bps_inflation.py <api_key>")
        print("\nRegister for free at: https://webapi.bps.go.id/developer/")
        sys.exit(1)
    get_inflation(api_key)
