#!/usr/bin/env python3
"""Get Bank Indonesia exchange rates via web scraping."""

import requests
from bs4 import BeautifulSoup


def get_exchange_rates():
    """Scrape BI exchange rate page."""
    resp = requests.get(
        "https://www.bi.go.id/id/statistik/informasi-kurs/transaksi-bi/Default.aspx",
        headers={"User-Agent": "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36"},
        timeout=30,
    )
    resp.raise_for_status()
    
    soup = BeautifulSoup(resp.text, "html.parser")
    table = soup.find("table", class_="table1")
    
    if not table:
        print("Could not find exchange rate table")
        return
    
    print(f"{'Currency':<12} {'Buy':>12} {'Sell':>12}")
    print("-" * 38)
    
    for row in table.find_all("tr")[1:]:
        cols = [td.text.strip() for td in row.find_all("td")]
        if len(cols) >= 3:
            currency = cols[0]
            buy = cols[1]
            sell = cols[2]
            print(f"{currency:<12} {buy:>12} {sell:>12}")


if __name__ == "__main__":
    get_exchange_rates()
