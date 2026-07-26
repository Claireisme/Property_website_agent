#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"

echo "Testing agency-site..."
(
  cd "$ROOT_DIR/apps/agency-site"
  php artisan test
  ./vendor/bin/pint --dirty
)

echo "Testing main-portal..."
(
  cd "$ROOT_DIR/apps/main-portal"
  php artisan test
  ./vendor/bin/pint --dirty
)

echo "All checks passed."
