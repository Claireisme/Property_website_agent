#!/usr/bin/env bash
set -euo pipefail

APP_NAME="agency-site"
PHP_REQUIRED_VERSION="8.4.0"
COMPOSER_REQUIRED_VERSION="2.2.0"
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"

if [ -f "$SCRIPT_DIR/artisan" ] || [ ! -d "$SCRIPT_DIR/../apps/$APP_NAME" ]; then
  APP_DIR="$SCRIPT_DIR"
  ROOT_DIR="$(cd "$APP_DIR/.." && pwd)"
  BACKUP_DIR="${BACKUP_DIR:-$APP_DIR/backups}"
else
  ROOT_DIR="$(cd "$SCRIPT_DIR/.." && pwd)"
  APP_DIR="$ROOT_DIR/apps/$APP_NAME"
  BACKUP_DIR="${BACKUP_DIR:-$ROOT_DIR/backups}"
fi

DEFAULT_NPM_BIN="npm"

detect_php_bin() {
  local candidate
  for candidate in /www/server/php/84/bin/php /www/server/php/83/bin/php /www/server/php/82/bin/php php; do
    if command -v "$candidate" >/dev/null 2>&1 && "$candidate" -r "exit(version_compare(PHP_VERSION, '$PHP_REQUIRED_VERSION', '>=') ? 0 : 1);" 2>/dev/null; then
      printf '%s\n' "$candidate"
      return
    fi
  done

  printf 'php\n'
}

detect_composer_bin() {
  if [ -f "$APP_DIR/composer.phar" ]; then
    printf '%s\n' "$APP_DIR/composer.phar"
    return
  fi

  command -v composer 2>/dev/null || printf 'composer\n'
}

DEFAULT_PHP_BIN="$(detect_php_bin)"
DEFAULT_COMPOSER_BIN="$(detect_composer_bin)"

PHP_BIN="${PHP_BIN:-$DEFAULT_PHP_BIN}"
COMPOSER_BIN="${COMPOSER_BIN:-$DEFAULT_COMPOSER_BIN}"
NPM_BIN="${NPM_BIN:-$DEFAULT_NPM_BIN}"

mkdir -p "$BACKUP_DIR"

line() {
  printf '%s\n' "------------------------------------------------------------"
}

info() {
  printf '[INFO] %s\n' "$*"
}

warn() {
  printf '[WARN] %s\n' "$*"
}

fail() {
  printf '[ERROR] %s\n' "$*" >&2
  exit 1
}

pause() {
  printf '\n按 Enter 返回菜单...'
  read -r _
}

confirm() {
  local prompt="$1"
  local answer
  printf '%s [y/N]: ' "$prompt"
  read -r answer
  case "$answer" in
    y|Y|yes|YES) return 0 ;;
    *) return 1 ;;
  esac
}

require_app_dir() {
  [ -d "$APP_DIR" ] || fail "找不到中介网站目录：$APP_DIR"
  [ -f "$APP_DIR/artisan" ] || fail "中介网站目录中找不到 artisan：$APP_DIR"
}

has_existing_app() {
  [ -d "$APP_DIR" ] && [ -f "$APP_DIR/artisan" ]
}

timestamp() {
  date +%Y%m%d-%H%M%S
}

latest_backup() {
  find "$BACKUP_DIR" "$APP_DIR" -maxdepth 1 -type f -name "${APP_NAME}-deploy-*.tar.gz" -print 2>/dev/null | sort | tail -1
}

create_backup() {
  require_app_dir

  local stamp output manifest tmp_manifest
  stamp="$(timestamp)"
  output="$BACKUP_DIR/${APP_NAME}-deploy-${stamp}.tar.gz"
  manifest="$(mktemp)"
  tmp_manifest="$APP_DIR/.deploy-backup-manifest.txt"

  {
    printf 'app=%s\n' "$APP_NAME"
    printf 'created_at=%s\n' "$(date -u +%Y-%m-%dT%H:%M:%SZ)"
    printf 'root_dir=%s\n' "$ROOT_DIR"
    printf 'app_dir=%s\n' "$APP_DIR"
    printf 'php_version=%s\n' "$($PHP_BIN -r 'echo PHP_VERSION;' 2>/dev/null || true)"
    printf 'node_version=%s\n' "$(node -v 2>/dev/null || true)"
    printf 'npm_version=%s\n' "$($NPM_BIN -v 2>/dev/null || true)"
    printf 'composer_version=%s\n' "$($COMPOSER_BIN --version 2>/dev/null | head -1 || true)"
  } > "$manifest"

  cp "$manifest" "$tmp_manifest"

  info "开始打包中介网站：$APP_DIR"
  COPYFILE_DISABLE=1 tar \
    --exclude=".DS_Store" \
    --exclude="backups" \
    --exclude="$(basename "$0")" \
    --exclude=".phpunit.result.cache" \
    --exclude="node_modules" \
    --exclude="vendor" \
    --exclude="public/build" \
    --exclude="public/hot" \
    --exclude="public/storage" \
    --exclude="storage/framework/cache" \
    --exclude="storage/framework/sessions" \
    --exclude="storage/framework/testing" \
    --exclude="storage/framework/views" \
    --exclude="storage/logs" \
    -czf "$output" \
    -C "$APP_DIR" \
    .

  rm -f "$tmp_manifest" "$manifest"

  info "备份完成：$output"
  info "这个包包含中介网站源码、.env、database、storage/app 等业务数据；不包含 vendor/node_modules/build/cache/logs。"
}

run_if_available() {
  local title="$1"
  shift

  info "$title"
  "$@"
}

run_composer() {
  if [ -f "$COMPOSER_BIN" ]; then
    COMPOSER_ALLOW_SUPERUSER=1 "$PHP_BIN" "$COMPOSER_BIN" "$@"
  else
    COMPOSER_ALLOW_SUPERUSER=1 "$COMPOSER_BIN" "$@"
  fi
}

composer_version_number() {
  run_composer --version 2>/dev/null | sed -nE 's/.*[Vv]ersion[^0-9]*([0-9]+(\.[0-9]+){1,2}).*/\1/p' | head -1
}

install_local_composer() {
  local installer="$APP_DIR/composer-setup.php"

  info "安装当前目录专用 Composer：$APP_DIR/composer.phar"
  "$PHP_BIN" -r "copy('https://getcomposer.org/installer', '$installer');" \
    || fail "下载 Composer 安装器失败，请检查服务器网络。"
  "$PHP_BIN" "$installer" --install-dir="$APP_DIR" --filename=composer.phar
  rm -f "$installer"
  COMPOSER_BIN="$APP_DIR/composer.phar"
}

ensure_composer() {
  local current_version

  if [ -f "$COMPOSER_BIN" ] || command -v "$COMPOSER_BIN" >/dev/null 2>&1; then
    current_version="$(composer_version_number || true)"
    if [ -n "$current_version" ] && version_at_least "$current_version" "$COMPOSER_REQUIRED_VERSION"; then
      return
    fi
  fi

  warn "Composer 不存在或版本低于 $COMPOSER_REQUIRED_VERSION，将自动安装 composer.phar。"
  install_local_composer

  current_version="$(composer_version_number || true)"
  if [ -n "$current_version" ] && version_at_least "$current_version" "$COMPOSER_REQUIRED_VERSION"; then
    info "Composer 已就绪：$(run_composer --version | head -1)"
    return
  fi

  warn "Composer 验证失败，下面是实际执行输出："
  run_composer --version || true
  fail "Composer 安装后仍不可用，请检查 PHP 扩展 phar/openssl 或手动执行：$PHP_BIN $COMPOSER_BIN --version"
}

extract_backup_archive() {
  local backup_file="$1"

  if tar --version 2>/dev/null | grep -qi 'GNU tar'; then
    tar --warning=no-unknown-keyword -xzf "$backup_file" -C "$APP_DIR"
  else
    tar -xzf "$backup_file" -C "$APP_DIR"
  fi
}

install_dependencies() {
  require_app_dir
  cd "$APP_DIR"

  command -v "$PHP_BIN" >/dev/null 2>&1 || fail "找不到 PHP：$PHP_BIN"
  command -v "$NPM_BIN" >/dev/null 2>&1 || fail "找不到 npm：$NPM_BIN"

  "$PHP_BIN" -r "exit(version_compare(PHP_VERSION, '$PHP_REQUIRED_VERSION', '>=') ? 0 : 1);" \
    || fail "当前命令行 PHP 版本不足。正在使用：$("$PHP_BIN" -v | head -1)。请设置 PHP_BIN=/www/server/php/84/bin/php 后重试。"
  ensure_composer

  [ -f ".env" ] || cp ".env.example" ".env"
  [ -f "database/database.sqlite" ] || touch "database/database.sqlite"

  run_if_available "安装 PHP 依赖 composer install --no-dev" \
    run_composer install --no-dev --prefer-dist --optimize-autoloader

  if [ -f "package-lock.json" ]; then
    run_if_available "安装前端依赖 npm ci" "$NPM_BIN" ci
  else
    run_if_available "安装前端依赖 npm install" "$NPM_BIN" install
  fi

  run_if_available "构建前端资源 npm run build" "$NPM_BIN" run build

  if ! grep -Eq '^APP_KEY=.+$' ".env"; then
    run_if_available "生成 Laravel APP_KEY" "$PHP_BIN" artisan key:generate --force
  fi

  run_if_available "执行数据库迁移 php artisan migrate --force" "$PHP_BIN" artisan migrate --force
  run_if_available "创建 storage 链接 php artisan storage:link" "$PHP_BIN" artisan storage:link || true
  run_if_available "清理缓存 php artisan optimize:clear" "$PHP_BIN" artisan optimize:clear
  run_if_available "生成生产缓存 php artisan optimize" "$PHP_BIN" artisan optimize
}

restore_backup() {
  local backup_file
  backup_file="$(latest_backup || true)"

  printf '请输入要还原的备份包路径'
  if [ -n "$backup_file" ]; then
    printf '（直接 Enter 使用最新：%s）' "$backup_file"
  fi
  printf '：'
  read -r input_backup

  if [ -n "${input_backup:-}" ]; then
    backup_file="$input_backup"
  fi

  [ -n "$backup_file" ] || fail "没有找到可还原的备份包。"
  [ -f "$backup_file" ] || fail "备份包不存在：$backup_file"

  line
  warn "即将把备份包还原到：$APP_DIR"
  warn "还原会覆盖同名文件，并随后安装依赖、构建前端、执行数据库迁移。"
  line
  confirm "确认继续还原？" || return 0

  mkdir -p "$APP_DIR"

  info "解压备份包：$backup_file"
  extract_backup_archive "$backup_file"

  install_dependencies

  info "还原和环境部署完成。"
}

version_at_least() {
  local current="$1"
  local required="$2"
  [ "$(printf '%s\n%s\n' "$required" "$current" | sort -V | head -1)" = "$required" ]
}

check_command() {
  local name="$1"
  local cmd="$2"
  local version_cmd="$3"
  local required="${4:-}"
  local version status

  if ! command -v "$cmd" >/dev/null 2>&1; then
    printf 'FAIL  %-12s 未安装或不在 PATH 中\n' "$name"
    return 1
  fi

  version="$($version_cmd 2>/dev/null | head -1 || true)"
  status="OK"

  if [ -n "$required" ]; then
    local clean_version
    clean_version="$(printf '%s' "$version" | sed -E 's/[^0-9.]*([0-9]+(\.[0-9]+){0,2}).*/\1/')"
    if [ -n "$clean_version" ] && ! version_at_least "$clean_version" "$required"; then
      status="WARN"
    fi
  fi

  printf '%-5s %-12s %s\n' "$status" "$name" "$version"
}

check_php_extension() {
  local extension="$1"
  if "$PHP_BIN" -r "exit(extension_loaded('$extension') ? 0 : 1);" 2>/dev/null; then
    printf 'OK    PHP ext      %s\n' "$extension"
  else
    printf 'FAIL  PHP ext      缺少 %s\n' "$extension"
    return 1
  fi
}

check_environment() {
  require_app_dir

  local failures=0
  line
  printf '中介网站部署环境检测：%s\n' "$APP_DIR"
  line

  check_command "PHP" "$PHP_BIN" "$PHP_BIN -v" "8.4" || failures=$((failures + 1))
  if [ -f "$COMPOSER_BIN" ] || command -v "$COMPOSER_BIN" >/dev/null 2>&1; then
    printf 'OK    Composer     %s\n' "$(run_composer --version 2>/dev/null | head -1 || printf '无法通过当前 PHP 执行')"
  else
    printf 'FAIL  Composer     未安装或不在 PATH 中\n'
    failures=$((failures + 1))
  fi
  check_command "Node" "node" "node -v" "20.0" || true
  check_command "npm" "$NPM_BIN" "$NPM_BIN -v" || failures=$((failures + 1))
  check_command "tar" "tar" "tar --version" || failures=$((failures + 1))

  if command -v "$PHP_BIN" >/dev/null 2>&1; then
    check_php_extension "ctype" || failures=$((failures + 1))
    check_php_extension "curl" || failures=$((failures + 1))
    check_php_extension "dom" || failures=$((failures + 1))
    check_php_extension "fileinfo" || failures=$((failures + 1))
    check_php_extension "mbstring" || failures=$((failures + 1))
    check_php_extension "openssl" || failures=$((failures + 1))
    check_php_extension "phar" || failures=$((failures + 1))
    check_php_extension "pdo" || failures=$((failures + 1))
    check_php_extension "pdo_sqlite" || failures=$((failures + 1))
    check_php_extension "sqlite3" || failures=$((failures + 1))
    check_php_extension "tokenizer" || failures=$((failures + 1))
    check_php_extension "xml" || failures=$((failures + 1))
  fi

  [ -w "$APP_DIR" ] && printf 'OK    Writable     %s\n' "$APP_DIR" || { printf 'FAIL  Writable     %s 不可写\n' "$APP_DIR"; failures=$((failures + 1)); }
  [ -w "$BACKUP_DIR" ] && printf 'OK    Writable     %s\n' "$BACKUP_DIR" || { printf 'FAIL  Writable     %s 不可写\n' "$BACKUP_DIR"; failures=$((failures + 1)); }
  [ -f "$APP_DIR/.env" ] && printf 'OK    Laravel      .env 存在\n' || printf 'WARN  Laravel      .env 不存在，还原/部署时会从 .env.example 创建\n'
  [ -f "$APP_DIR/database/database.sqlite" ] && printf 'OK    Database     SQLite 文件存在\n' || printf 'WARN  Database     SQLite 文件不存在，还原/部署时会创建\n'

  line
  if [ "$failures" -eq 0 ]; then
    printf '检测结果：通过，可以进行备份/还原/部署。\n'
  else
    printf '检测结果：有 %s 个必须处理的问题，请先安装或修复后再部署。\n' "$failures"
  fi
}

show_menu() {
  clear || true
  line
  printf '中介网站一键备份 / 迁移 / 环境检测\n'
  printf '项目：%s\n' "$APP_NAME"
  printf '目录：%s\n' "$APP_DIR"
  line
  printf '1、打包备份网站及部署环境\n'
  printf '2、还原网站数据及环境部署\n'
  printf '3、网站部署所需环境检测\n'
  printf '0、退出\n'
  line
  printf '请选择：'
}

main() {
  while true; do
    show_menu
    read -r choice
    case "$choice" in
      1)
        create_backup
        pause
        ;;
      2)
        restore_backup
        pause
        ;;
      3)
        check_environment
        pause
        ;;
      0)
        exit 0
        ;;
      *)
        warn "无效选择：$choice"
        pause
        ;;
    esac
  done
}

main "$@"
