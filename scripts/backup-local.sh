#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
BACKUP_DIR="$ROOT_DIR/backups"
STAMP="$(date +%Y%m%d-%H%M%S)"

mkdir -p "$BACKUP_DIR"

backup_app() {
  local app_name="$1"
  local app_dir="$ROOT_DIR/apps/$app_name"
  local output="$BACKUP_DIR/$app_name-backup-$STAMP.tar.gz"

  tar \
    --exclude="vendor" \
    --exclude="node_modules" \
    --exclude="storage/framework/cache" \
    --exclude="storage/framework/sessions" \
    --exclude="storage/framework/views" \
    --exclude="storage/logs" \
    -czf "$output" \
    -C "$app_dir" \
    database storage .env.example

  echo "Created $output"
}

backup_app "agency-site"
backup_app "main-portal"

echo "Local backups complete."
