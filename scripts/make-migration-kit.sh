#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
BACKUP_DIR="$ROOT_DIR/backups"
STAMP="$(date +%Y%m%d-%H%M%S)"

usage() {
  cat <<'EOF'
Usage:
  scripts/make-migration-kit.sh main-portal
  scripts/make-migration-kit.sh agency-site
  scripts/make-migration-kit.sh all

Creates a self-contained migration kit under backups/ with:
  - deployable source archive, excluding vendor/node_modules/runtime cache
  - PostgreSQL SQL restore/import file from local SQLite data
  - storage/app/public archive for uploaded images/files
  - README with server-side restore commands
EOF
}

if [[ $# -ne 1 ]]; then
  usage
  exit 1
fi

mkdir -p "$BACKUP_DIR"

make_source_archive() {
  local app="$1"
  local kit_dir="$2"
  local app_dir="$ROOT_DIR/apps/$app"
  local output="$kit_dir/${app}-source.tar.gz"

  tar \
    --exclude="./vendor" \
    --exclude="./vendor/*" \
    --exclude="./node_modules" \
    --exclude="./node_modules/*" \
    --exclude="./backups" \
    --exclude="./backups/*" \
    --exclude="./storage/app/public" \
    --exclude="./storage/app/public/*" \
    --exclude="./storage/framework/cache/*" \
    --exclude="./storage/framework/sessions/*" \
    --exclude="./storage/framework/views/*" \
    --exclude="./storage/logs/*" \
    --exclude="./public/storage" \
    --exclude="./database/database.sqlite.before-pgsql-*" \
    -czf "$output" \
    -C "$app_dir" \
    .

  echo "$output"
}

make_storage_archive() {
  local app="$1"
  local kit_dir="$2"
  local app_dir="$ROOT_DIR/apps/$app"
  local output="$kit_dir/${app}-storage-public.tar.gz"

  if [[ -d "$app_dir/storage/app/public" ]]; then
    tar -czf "$output" -C "$app_dir/storage/app" public
  else
    tar -czf "$output" -T /dev/null
  fi

  echo "$output"
}

copy_latest_matching() {
  local pattern="$1"
  local kit_dir="$2"
  local target_name="$3"
  local latest

  latest="$(ls -t $pattern 2>/dev/null | head -n 1 || true)"
  if [[ -z "$latest" ]]; then
    echo "Could not find generated file matching: $pattern" >&2
    exit 1
  fi

  cp "$latest" "$kit_dir/$target_name"
  echo "$kit_dir/$target_name"
}

write_readme() {
  local app="$1"
  local kit_dir="$2"
  local domain="$3"
  local db_name="$4"
  local db_user="$5"
  local db_pass="$6"
  local app_key="$7"
  local sql_file="$8"
  local source_file="$9"
  local storage_file="${10}"
  local app_title

  if [[ "$app" == "main-portal" ]]; then
    app_title="Main portal"
  else
    app_title="Agency site"
  fi

  cat > "$kit_dir/README-RESTORE.md" <<EOF
# ${app_title} Migration Kit

Generated: ${STAMP}

## Files

- \`${source_file}\` - Laravel source archive
- \`${sql_file}\` - PostgreSQL data file
- \`${storage_file}\` - uploaded public files/images

## Server Restore

Upload the three files in this kit to the server, for example:

\`\`\`bash
/www/wwwroot/${domain}
\`\`\`

Then run:

\`\`\`bash
cd /www/wwwroot/${domain}

tar -xzf ${source_file} -C .
composer install --no-dev --optimize-autoloader
npm install
npm run build

cp .env.example .env 2>/dev/null || true
\`\`\`

Edit \`.env\` and set:

\`\`\`env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://${domain}
APP_KEY=${app_key}

DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=${db_name}
DB_USERNAME=${db_user}
DB_PASSWORD=${db_pass}

SESSION_DRIVER=file
SESSION_COOKIE=${domain//./_}_session
SESSION_DOMAIN=
SESSION_SECURE_COOKIE=true
SESSION_SAME_SITE=lax

ASSET_URL=https://${domain}
LIVEWIRE_ASSET_URL=https://${domain}/vendor/livewire
\`\`\`

For agency-site, also set the server-side translation gateway to the public main portal domain:

\`\`\`env
TRANSLATION_GATEWAY_URL=https://house.520.ie/api/internal/translations/property
\`\`\`

Create schema and import data:

\`\`\`bash
php artisan config:clear
php artisan migrate --force
PGPASSWORD='${db_pass}' psql -h 127.0.0.1 -U ${db_user} -d ${db_name} -f ${sql_file}
\`\`\`

Restore uploaded files/images and storage link:

\`\`\`bash
tar -xzf ${storage_file} -C storage/app
rm -rf public/storage
ln -s ../storage/app/public public/storage
\`\`\`

Publish Livewire assets and clear Laravel caches:

\`\`\`bash
php artisan livewire:publish --assets
mkdir -p storage/framework/sessions storage/framework/cache storage/framework/views bootstrap/cache
chown -R www:www storage bootstrap/cache
chmod -R 775 storage bootstrap/cache
rm -f bootstrap/cache/*.php
php artisan optimize:clear
php artisan config:clear
php artisan cache:clear
php artisan view:clear
\`\`\`

## Checks

\`\`\`bash
php artisan migrate:status
curl -I https://${domain}/vendor/livewire/livewire.min.js
curl -I https://${domain}/admin/login
\`\`\`

If admin login loops back to the login page, first check Livewire assets:

\`\`\`bash
curl -I https://${domain}/vendor/livewire/livewire.min.js
\`\`\`

It should return \`200\`.
EOF
}

make_kit() {
  local app="$1"
  local domain db_name db_user db_pass app_key sql_file source_file storage_file kit_dir

  case "$app" in
    main-portal)
      domain="house.520.ie"
      db_name="house"
      db_user="house"
      db_pass="SDE7kjn8Hmb6cJaW"
      app_key="base64:uF/5OGYXZWO8v3kDnTuXvBERGynolOt0zcbdDgPVzZs="
      ;;
    agency-site)
      domain="gnd.520.ie"
      db_name="gnd"
      db_user="gnd"
      db_pass="2YFe6AJmSmW8RshE"
      app_key="base64:Nkec207nbn65RhwajMb0ISO23Wfxj3n1y7+y4ga1HUM="
      ;;
    *)
      echo "Unsupported app: $app" >&2
      exit 1
      ;;
  esac

  kit_dir="$BACKUP_DIR/${app}-migration-kit-$STAMP"
  mkdir -p "$kit_dir"

  source_file="$(basename "$(make_source_archive "$app" "$kit_dir")")"
  storage_file="$(basename "$(make_storage_archive "$app" "$kit_dir")")"

  if [[ "$app" == "main-portal" ]]; then
    php "$ROOT_DIR/scripts/export-main-portal-pgsql-restore.php" >/dev/null
    sql_file="$(basename "$(copy_latest_matching "$BACKUP_DIR/main-portal-postgresql-restore-*.sql" "$kit_dir" "main-portal-postgresql-restore.sql")")"
  else
    php "$ROOT_DIR/scripts/export-agency-site-pgsql-data.php" >/dev/null
    sql_file="$(basename "$(copy_latest_matching "$BACKUP_DIR/agency-site-postgresql-data-*.sql" "$kit_dir" "agency-site-postgresql-data.sql")")"
  fi

  write_readme "$app" "$kit_dir" "$domain" "$db_name" "$db_user" "$db_pass" "$app_key" "$sql_file" "$source_file" "$storage_file"

  tar -czf "$kit_dir.tar.gz" -C "$BACKUP_DIR" "$(basename "$kit_dir")"

  echo "Created migration kit:"
  echo "  $kit_dir"
  echo "  $kit_dir.tar.gz"
}

case "$1" in
  all)
    make_kit "main-portal"
    make_kit "agency-site"
    ;;
  main-portal|agency-site)
    make_kit "$1"
    ;;
  *)
    usage
    exit 1
    ;;
esac
