<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$sqlitePath = $root . '/apps/main-portal/database/database.sqlite';
$backupDir = $root . '/backups';
$outputPath = $backupDir . '/main-portal-postgresql-restore-' . date('Ymd-His') . '.sql';

if (! is_file($sqlitePath)) {
    fwrite(STDERR, "SQLite database not found: {$sqlitePath}\n");
    exit(1);
}

if (! is_dir($backupDir) && ! mkdir($backupDir, 0755, true)) {
    fwrite(STDERR, "Could not create backup directory: {$backupDir}\n");
    exit(1);
}

$tables = [
    'migrations',
    'users',
    'password_reset_tokens',
    'sessions',
    'cache',
    'cache_locks',
    'jobs',
    'job_batches',
    'failed_jobs',
    'portal_agencies',
    'portal_properties',
    'sync_runs',
    'portal_enquiries',
    'portal_property_translations',
    'translation_provider_settings',
];

$booleanColumns = [
    'portal_properties' => ['online_offers_enabled'],
    'translation_provider_settings' => ['is_enabled'],
];

$sqlite = new PDO('sqlite:' . $sqlitePath);
$sqlite->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$sqlite->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

$handle = fopen($outputPath, 'wb');
if ($handle === false) {
    fwrite(STDERR, "Could not write restore file: {$outputPath}\n");
    exit(1);
}

writeLine($handle, '-- Main portal PostgreSQL restore file');
writeLine($handle, '-- Source: apps/main-portal/database/database.sqlite');
writeLine($handle, '-- Generated: ' . date(DATE_ATOM));
writeLine($handle, '');
writeLine($handle, 'BEGIN;');
writeLine($handle, 'SET client_encoding = \'UTF8\';');
writeLine($handle, 'SET standard_conforming_strings = on;');
writeLine($handle, 'SET check_function_bodies = false;');
writeLine($handle, 'SET client_min_messages = warning;');
writeLine($handle, 'SET search_path = public;');
writeLine($handle, '');
writeLine($handle, schemaSql());
writeLine($handle, '');

foreach ($tables as $table) {
    $columns = sqliteColumns($sqlite, $table);
    $rows = $sqlite->query('SELECT * FROM ' . quoteIdentifier($table));
    $count = 0;

    writeLine($handle, '-- Data for ' . $table);

    foreach ($rows as $row) {
        $values = [];

        foreach ($columns as $column) {
            $values[] = sqlValue($row[$column] ?? null, in_array($column, $booleanColumns[$table] ?? [], true));
        }

        writeLine($handle, sprintf(
            'INSERT INTO %s (%s) VALUES (%s);',
            quoteIdentifier($table),
            implode(', ', array_map('quoteIdentifier', $columns)),
            implode(', ', $values)
        ));

        $count++;
    }

    if ($count === 0) {
        writeLine($handle, '-- 0 rows');
    }

    writeLine($handle, '');
    echo sprintf("%-36s %d\n", $table, $count);
}

foreach (sequenceTables() as $table) {
    writeLine($handle, sprintf(
        "SELECT setval(pg_get_serial_sequence('%s', 'id'), COALESCE((SELECT MAX(id) FROM %s), 1), (SELECT COUNT(*) FROM %s) > 0);",
        $table,
        quoteIdentifier($table),
        quoteIdentifier($table)
    ));
}

writeLine($handle, 'COMMIT;');
fclose($handle);

echo "\nRestore file written:\n{$outputPath}\n";

function schemaSql(): string
{
    return <<<'SQL'
DROP TABLE IF EXISTS "portal_property_translations" CASCADE;
DROP TABLE IF EXISTS "portal_enquiries" CASCADE;
DROP TABLE IF EXISTS "sync_runs" CASCADE;
DROP TABLE IF EXISTS "portal_properties" CASCADE;
DROP TABLE IF EXISTS "portal_agencies" CASCADE;
DROP TABLE IF EXISTS "translation_provider_settings" CASCADE;
DROP TABLE IF EXISTS "failed_jobs" CASCADE;
DROP TABLE IF EXISTS "job_batches" CASCADE;
DROP TABLE IF EXISTS "jobs" CASCADE;
DROP TABLE IF EXISTS "cache_locks" CASCADE;
DROP TABLE IF EXISTS "cache" CASCADE;
DROP TABLE IF EXISTS "sessions" CASCADE;
DROP TABLE IF EXISTS "password_reset_tokens" CASCADE;
DROP TABLE IF EXISTS "users" CASCADE;
DROP TABLE IF EXISTS "migrations" CASCADE;

CREATE TABLE "migrations" (
    "id" bigserial PRIMARY KEY,
    "migration" varchar(255) NOT NULL,
    "batch" integer NOT NULL
);

CREATE TABLE "users" (
    "id" bigserial PRIMARY KEY,
    "name" varchar(255) NOT NULL,
    "email" varchar(255) NOT NULL,
    "email_verified_at" timestamp(0) without time zone NULL,
    "password" varchar(255) NOT NULL,
    "remember_token" varchar(100) NULL,
    "created_at" timestamp(0) without time zone NULL,
    "updated_at" timestamp(0) without time zone NULL
);
CREATE UNIQUE INDEX "users_email_unique" ON "users" ("email");

CREATE TABLE "password_reset_tokens" (
    "email" varchar(255) PRIMARY KEY,
    "token" varchar(255) NOT NULL,
    "created_at" timestamp(0) without time zone NULL
);

CREATE TABLE "sessions" (
    "id" varchar(255) PRIMARY KEY,
    "user_id" bigint NULL,
    "ip_address" varchar(45) NULL,
    "user_agent" text NULL,
    "payload" text NOT NULL,
    "last_activity" integer NOT NULL
);
CREATE INDEX "sessions_user_id_index" ON "sessions" ("user_id");
CREATE INDEX "sessions_last_activity_index" ON "sessions" ("last_activity");

CREATE TABLE "cache" (
    "key" varchar(255) PRIMARY KEY,
    "value" text NOT NULL,
    "expiration" bigint NOT NULL
);
CREATE INDEX "cache_expiration_index" ON "cache" ("expiration");

CREATE TABLE "cache_locks" (
    "key" varchar(255) PRIMARY KEY,
    "owner" varchar(255) NOT NULL,
    "expiration" bigint NOT NULL
);
CREATE INDEX "cache_locks_expiration_index" ON "cache_locks" ("expiration");

CREATE TABLE "jobs" (
    "id" bigserial PRIMARY KEY,
    "queue" varchar(255) NOT NULL,
    "payload" text NOT NULL,
    "attempts" smallint NOT NULL,
    "reserved_at" integer NULL,
    "available_at" integer NOT NULL,
    "created_at" integer NOT NULL
);
CREATE INDEX "jobs_queue_index" ON "jobs" ("queue");

CREATE TABLE "job_batches" (
    "id" varchar(255) PRIMARY KEY,
    "name" varchar(255) NOT NULL,
    "total_jobs" integer NOT NULL,
    "pending_jobs" integer NOT NULL,
    "failed_jobs" integer NOT NULL,
    "failed_job_ids" text NOT NULL,
    "options" text NULL,
    "cancelled_at" integer NULL,
    "created_at" integer NOT NULL,
    "finished_at" integer NULL
);

CREATE TABLE "failed_jobs" (
    "id" bigserial PRIMARY KEY,
    "uuid" varchar(255) NOT NULL,
    "connection" text NOT NULL,
    "queue" text NOT NULL,
    "payload" text NOT NULL,
    "exception" text NOT NULL,
    "failed_at" timestamp(0) without time zone NOT NULL DEFAULT CURRENT_TIMESTAMP
);
CREATE UNIQUE INDEX "failed_jobs_uuid_unique" ON "failed_jobs" ("uuid");

CREATE TABLE "portal_agencies" (
    "id" bigserial PRIMARY KEY,
    "name" varchar(255) NOT NULL,
    "website_url" varchar(255) NULL,
    "feed_url" varchar(255) NOT NULL,
    "api_token_encrypted" text NOT NULL,
    "status" varchar(255) NOT NULL DEFAULT 'active',
    "last_synced_at" timestamp(0) without time zone NULL,
    "last_sync_status" varchar(255) NULL,
    "last_error_message" text NULL,
    "created_at" timestamp(0) without time zone NULL,
    "updated_at" timestamp(0) without time zone NULL
);
CREATE INDEX "portal_agencies_status_index" ON "portal_agencies" ("status");

CREATE TABLE "portal_properties" (
    "id" bigserial PRIMARY KEY,
    "portal_agency_id" bigint NOT NULL,
    "external_listing_id" varchar(255) NOT NULL,
    "source_url" varchar(255) NULL,
    "title" varchar(255) NOT NULL,
    "slug" varchar(255) NOT NULL,
    "status" varchar(255) NOT NULL,
    "transaction_type" varchar(255) NULL,
    "property_type" varchar(255) NULL,
    "price" bigint NULL,
    "bedrooms" smallint NULL,
    "bathrooms" smallint NULL,
    "floor_area_m2" numeric(8, 2) NULL,
    "ber_rating" varchar(255) NULL,
    "address_summary" varchar(255) NULL,
    "town" varchar(255) NULL,
    "county" varchar(255) NULL,
    "eircode_hash" varchar(255) NULL,
    "latitude" numeric(10, 7) NULL,
    "longitude" numeric(10, 7) NULL,
    "description" text NULL,
    "images" json NULL,
    "features" json NULL,
    "source_updated_at" timestamp(0) without time zone NULL,
    "first_synced_at" timestamp(0) without time zone NULL,
    "last_synced_at" timestamp(0) without time zone NULL,
    "created_at" timestamp(0) without time zone NULL,
    "updated_at" timestamp(0) without time zone NULL,
    "online_offers_enabled" boolean NOT NULL DEFAULT false,
    "facilities" json NULL
);
CREATE UNIQUE INDEX "portal_properties_portal_agency_id_external_listing_id_unique" ON "portal_properties" ("portal_agency_id", "external_listing_id");
CREATE UNIQUE INDEX "portal_properties_slug_unique" ON "portal_properties" ("slug");
CREATE INDEX "portal_properties_status_transaction_type_index" ON "portal_properties" ("status", "transaction_type");
CREATE INDEX "portal_properties_town_county_index" ON "portal_properties" ("town", "county");

CREATE TABLE "sync_runs" (
    "id" bigserial PRIMARY KEY,
    "portal_agency_id" bigint NOT NULL,
    "status" varchar(255) NOT NULL DEFAULT 'success',
    "started_at" timestamp(0) without time zone NOT NULL,
    "finished_at" timestamp(0) without time zone NULL,
    "listings_seen" integer NOT NULL DEFAULT 0,
    "listings_created" integer NOT NULL DEFAULT 0,
    "listings_updated" integer NOT NULL DEFAULT 0,
    "listings_removed" integer NOT NULL DEFAULT 0,
    "error_message" text NULL,
    "created_at" timestamp(0) without time zone NULL,
    "updated_at" timestamp(0) without time zone NULL
);
CREATE INDEX "sync_runs_portal_agency_id_started_at_index" ON "sync_runs" ("portal_agency_id", "started_at");

CREATE TABLE "portal_enquiries" (
    "id" bigserial PRIMARY KEY,
    "portal_agency_id" bigint NOT NULL,
    "portal_property_id" bigint NULL,
    "name" varchar(255) NOT NULL,
    "email" varchar(255) NOT NULL,
    "phone" varchar(255) NULL,
    "message" text NULL,
    "source" varchar(255) NOT NULL DEFAULT 'main_portal',
    "status" varchar(255) NOT NULL DEFAULT 'new',
    "forwarded_at" timestamp(0) without time zone NULL,
    "created_at" timestamp(0) without time zone NULL,
    "updated_at" timestamp(0) without time zone NULL
);
CREATE INDEX "portal_enquiries_portal_agency_id_status_index" ON "portal_enquiries" ("portal_agency_id", "status");
CREATE INDEX "portal_enquiries_source_created_at_index" ON "portal_enquiries" ("source", "created_at");

CREATE TABLE "portal_property_translations" (
    "id" bigserial PRIMARY KEY,
    "portal_property_id" bigint NOT NULL,
    "locale" varchar(8) NOT NULL,
    "status" varchar(255) NOT NULL DEFAULT 'machine_translated',
    "title" varchar(255) NOT NULL,
    "description" text NULL,
    "features" json NULL,
    "source_hash" varchar(64) NOT NULL,
    "error_message" text NULL,
    "translated_at" timestamp(0) without time zone NULL,
    "created_at" timestamp(0) without time zone NULL,
    "updated_at" timestamp(0) without time zone NULL
);
CREATE UNIQUE INDEX "portal_property_translations_portal_property_id_locale_unique" ON "portal_property_translations" ("portal_property_id", "locale");
CREATE INDEX "portal_property_translations_locale_status_index" ON "portal_property_translations" ("locale", "status");

CREATE TABLE "translation_provider_settings" (
    "id" bigserial PRIMARY KEY,
    "provider" varchar(255) NOT NULL,
    "is_enabled" boolean NOT NULL DEFAULT false,
    "api_key" text NULL,
    "base_url" varchar(255) NOT NULL DEFAULT 'https://api.deepseek.com',
    "model" varchar(255) NOT NULL DEFAULT 'deepseek-chat',
    "timeout_seconds" integer NOT NULL DEFAULT 90,
    "created_at" timestamp(0) without time zone NULL,
    "updated_at" timestamp(0) without time zone NULL
);
CREATE UNIQUE INDEX "translation_provider_settings_provider_unique" ON "translation_provider_settings" ("provider");

ALTER TABLE "portal_properties" ADD CONSTRAINT "portal_properties_portal_agency_id_foreign" FOREIGN KEY ("portal_agency_id") REFERENCES "portal_agencies" ("id") ON DELETE CASCADE;
ALTER TABLE "sync_runs" ADD CONSTRAINT "sync_runs_portal_agency_id_foreign" FOREIGN KEY ("portal_agency_id") REFERENCES "portal_agencies" ("id") ON DELETE CASCADE;
ALTER TABLE "portal_enquiries" ADD CONSTRAINT "portal_enquiries_portal_agency_id_foreign" FOREIGN KEY ("portal_agency_id") REFERENCES "portal_agencies" ("id") ON DELETE CASCADE;
ALTER TABLE "portal_enquiries" ADD CONSTRAINT "portal_enquiries_portal_property_id_foreign" FOREIGN KEY ("portal_property_id") REFERENCES "portal_properties" ("id") ON DELETE SET NULL;
ALTER TABLE "portal_property_translations" ADD CONSTRAINT "portal_property_translations_portal_property_id_foreign" FOREIGN KEY ("portal_property_id") REFERENCES "portal_properties" ("id") ON DELETE CASCADE;
SQL;
}

function sqliteColumns(PDO $sqlite, string $table): array
{
    $statement = $sqlite->query('PRAGMA table_info(' . quoteIdentifier($table) . ')');
    $columns = [];

    foreach ($statement as $column) {
        $columns[] = $column['name'];
    }

    if ($columns === []) {
        throw new RuntimeException("No columns found for {$table}");
    }

    return $columns;
}

function sequenceTables(): array
{
    return [
        'migrations',
        'users',
        'jobs',
        'failed_jobs',
        'portal_agencies',
        'portal_properties',
        'sync_runs',
        'portal_enquiries',
        'portal_property_translations',
        'translation_provider_settings',
    ];
}

function sqlValue(mixed $value, bool $isBoolean = false): string
{
    if ($value === null) {
        return 'NULL';
    }

    if ($isBoolean) {
        return ((int) $value) === 1 ? 'true' : 'false';
    }

    return "'" . str_replace("'", "''", (string) $value) . "'";
}

function quoteIdentifier(string $identifier): string
{
    return '"' . str_replace('"', '""', $identifier) . '"';
}

function writeLine($handle, string $line): void
{
    fwrite($handle, $line . PHP_EOL);
}
