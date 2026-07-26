#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"

echo "Seeding agency-site..."
(
  cd "$ROOT_DIR/apps/agency-site"
  php artisan migrate --force
  php artisan db:seed --force
  php artisan properties:translate --fake --force
)

echo "Preparing main-portal..."
(
  cd "$ROOT_DIR/apps/main-portal"
  php artisan migrate --force
  php artisan db:seed --force
)

echo "Syncing agency feed into main portal..."
(
  cd "$ROOT_DIR/apps/main-portal"
  php artisan sync:agency-feed
)

echo "Local seed and sync complete."
