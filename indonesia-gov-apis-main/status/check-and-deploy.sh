#!/usr/bin/env bash
# Daily portal status check + commit + deploy to CF Pages
set -euo pipefail

cd "$(dirname "$0")/.."

# Run the check
python3 status/check.py

# Build consolidated history.json (single file for frontend)
python3 -c "
import json, os, glob
data_dir = 'status/data'
with open(os.path.join(data_dir, 'index.json')) as f:
    days = json.load(f)
history = {}
for day in days:
    fp = os.path.join(data_dir, f'{day}.json')
    if os.path.exists(fp):
        with open(fp) as f:
            history[day] = json.load(f)
with open(os.path.join(data_dir, 'history.json'), 'w') as f:
    json.dump(history, f)
print(f'history.json: {len(history)} days')
"

# Commit if there are changes
if git diff --quiet status/data/; then
  echo "No changes to commit"
else
  git add status/data/
  DATE=$(date -u +%Y-%m-%d)
  git commit -m "status: daily check ${DATE}"
  git push
fi

# Deploy to CF Pages
CF_TOKEN=$(pass cloudflare/api-token)
CLOUDFLARE_API_TOKEN=$CF_TOKEN npx wrangler pages deploy status --project-name gov-portal-status --branch main
echo "✅ Deployed"
