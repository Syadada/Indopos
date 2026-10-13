#!/usr/bin/env python3
"""Check if a financial entity appears in OJK's illegal entity alert list."""

import requests
from bs4 import BeautifulSoup
import sys


def check_illegal(query: str):
    """Search OJK's SikapiUangmu portal for an entity."""
    resp = requests.get(
        "https://sikapiuangmu.ojk.go.id/FrontEnd/AlertPortal/Search",
        params={"q": query},
        headers={"User-Agent": "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36"},
        timeout=30,
    )
    resp.raise_for_status()
    
    soup = BeautifulSoup(resp.text, "html.parser")
    results = soup.select(".alert-item, table tbody tr")
    
    if not results:
        print(f"No results found for '{query}'")
        print("Note: This does NOT mean the entity is legal — it may simply not be in the database.")
        return
    
    print(f"Found {len(results)} result(s) for '{query}':")
    print()
    
    for item in results[:10]:
        cols = [td.text.strip() for td in item.find_all("td")]
        if cols:
            print(f"  ⚠️  {' | '.join(cols)}")


if __name__ == "__main__":
    if len(sys.argv) < 2:
        print("Usage: python check_ojk.py <company_name>")
        sys.exit(1)
    check_illegal(sys.argv[1])
