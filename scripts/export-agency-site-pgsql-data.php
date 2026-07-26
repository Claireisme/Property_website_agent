<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$sqlitePath = $root . '/apps/agency-site/database/database.sqlite';
$backupDir = $root . '/backups';
$outputPath = $backupDir . '/agency-site-postgresql-data-' . date('Ymd-His') . '.sql';

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
    'agencies',
    'team_members',
    'properties',
    'property_images',
    'property_translations',
    'enquiries',
    'valuation_requests',
    'feed_tokens',
    'buyer_access_requests',
    'offers',
    'offer_events',
    'email_settings',
    'email_notification_templates',
    'email_delivery_logs',
    'email_verification_codes',
];

$booleanColumns = [
    'buyer_access_requests' => ['consent_to_terms'],
    'email_notification_templates' => ['is_enabled'],
    'email_settings' => ['mail_enabled'],
    'feed_tokens' => ['is_active'],
    'offers' => ['consent_to_terms'],
    'properties' => ['online_offers_enabled'],
    'team_members' => ['is_active'],
];

$sqlite = new PDO('sqlite:' . $sqlitePath);
$sqlite->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$sqlite->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

$handle = fopen($outputPath, 'wb');
if ($handle === false) {
    fwrite(STDERR, "Could not write data file: {$outputPath}\n");
    exit(1);
}

writeLine($handle, '-- Agency site PostgreSQL data restore file');
writeLine($handle, '-- Run Laravel migrations on the target PostgreSQL database before importing this file.');
writeLine($handle, '-- Source: apps/agency-site/database/database.sqlite');
writeLine($handle, '-- Generated: ' . date(DATE_ATOM));
writeLine($handle, '');
writeLine($handle, 'BEGIN;');
writeLine($handle, 'SET client_encoding = \'UTF8\';');
writeLine($handle, 'SET standard_conforming_strings = on;');
writeLine($handle, '');
writeLine($handle, 'TRUNCATE TABLE ' . implode(', ', array_map('quoteIdentifier', array_reverse($tables))) . ' RESTART IDENTITY CASCADE;');
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

foreach (tablesWithIdColumn($sqlite, $tables) as $table) {
    writeLine($handle, sprintf(
        "SELECT setval(pg_get_serial_sequence('%s', 'id'), COALESCE((SELECT MAX(id) FROM %s), 1), (SELECT COUNT(*) FROM %s) > 0);",
        $table,
        quoteIdentifier($table),
        quoteIdentifier($table)
    ));
}

writeLine($handle, '');
writeLine($handle, 'COMMIT;');
fclose($handle);

echo "\nData restore file written:\n{$outputPath}\n";

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

function tablesWithIdColumn(PDO $sqlite, array $tables): array
{
    return array_values(array_filter($tables, static function (string $table) use ($sqlite): bool {
        $statement = $sqlite->query('PRAGMA table_info(' . quoteIdentifier($table) . ')');

        foreach ($statement as $column) {
            if ($column['name'] === 'id' && str_contains(strtolower((string) $column['type']), 'int')) {
                return true;
            }
        }

        return false;
    }));
}

function sqlValue(mixed $value, bool $isBoolean = false): string
{
    if ($value === null) {
        return 'NULL';
    }

    if ($isBoolean) {
        return ((int) $value) === 1 ? 'true' : 'false';
    }

    return "'" . str_replace(["\\", "'"], ["\\\\", "''"], (string) $value) . "'";
}

function quoteIdentifier(string $identifier): string
{
    return '"' . str_replace('"', '""', $identifier) . '"';
}

function writeLine($handle, string $line): void
{
    fwrite($handle, $line . PHP_EOL);
}
