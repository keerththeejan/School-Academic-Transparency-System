#!/usr/bin/env bash
set -euo pipefail
cd "$(dirname "$0")/.."
set -a
# shellcheck disable=SC1091
source .env
set +a
mkdir -p storage/app/backups
stamp="$(date +%Y%m%d-%H%M)"
out="storage/app/backups/sats-${stamp}.sql"
mysqldump --host="$DB_HOST" --port="$DB_PORT" --user="$DB_USERNAME" --password="$DB_PASSWORD" \
  --single-transaction --routines --triggers "$DB_DATABASE" > "$out"
echo "Wrote $out"
