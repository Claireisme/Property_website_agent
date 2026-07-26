<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$appDir = $root . '/apps/main-portal';
$sqlitePath = $appDir . '/database/database.sqlite';
$envPath = $appDir . '/.env';

if (! in_array('--yes', $argv, true)) {
    fwrite(STDERR, "Refusing to import without --yes. Target PostgreSQL tables will be truncated.\n");
    exit(1);
}

if (! is_file($sqlitePath)) {
    fwrite(STDERR, "SQLite database not found: {$sqlitePath}\n");
    exit(1);
}

$env = readEnvFile($envPath);

if (($env['DB_CONNECTION'] ?? null) !== 'pgsql') {
    fwrite(STDERR, "apps/main-portal/.env DB_CONNECTION must be pgsql before import.\n");
    exit(1);
}

$required = ['DB_HOST', 'DB_PORT', 'DB_DATABASE', 'DB_USERNAME', 'DB_PASSWORD'];
foreach ($required as $key) {
    if (! array_key_exists($key, $env)) {
        fwrite(STDERR, "Missing {$key} in apps/main-portal/.env\n");
        exit(1);
    }
}

$tables = [
    'users',
    'password_reset_tokens',
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
    'sessions',
];

$booleanColumns = [
    'portal_properties' => ['online_offers_enabled'],
    'translation_provider_settings' => ['is_enabled'],
];

$sqlite = new PDO('sqlite:' . $sqlitePath);
$sqlite->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$sqlite->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

$pgsql = new PDO(
    sprintf(
        'pgsql:host=%s;port=%s;dbname=%s',
        $env['DB_HOST'],
        $env['DB_PORT'],
        $env['DB_DATABASE']
    ),
    $env['DB_USERNAME'],
    $env['DB_PASSWORD'],
    [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]
);

$pgsql->beginTransaction();

try {
    $quotedTables = array_map(static fn (string $table): string => quoteIdentifier($table), array_reverse($tables));
    $pgsql->exec('TRUNCATE TABLE ' . implode(', ', $quotedTables) . ' RESTART IDENTITY CASCADE');

    foreach ($tables as $table) {
        $columns = sqliteColumns($sqlite, $table);
        $quotedColumns = array_map(static fn (string $column): string => quoteIdentifier($column), $columns);
        $placeholders = array_map(static fn (string $column): string => ':' . $column, $columns);

        $insert = $pgsql->prepare(sprintf(
            'INSERT INTO %s (%s) VALUES (%s)',
            quoteIdentifier($table),
            implode(', ', $quotedColumns),
            implode(', ', $placeholders)
        ));

        $count = 0;
        $rows = $sqlite->query('SELECT * FROM ' . quoteIdentifier($table));

        foreach ($rows as $row) {
            foreach ($booleanColumns[$table] ?? [] as $column) {
                if (array_key_exists($column, $row) && $row[$column] !== null) {
                    $row[$column] = ((int) $row[$column]) === 1 ? 'true' : 'false';
                }
            }

            foreach ($columns as $column) {
                $insert->bindValue(':' . $column, $row[$column] ?? null);
            }

            $insert->execute();
            $count++;
        }

        resetSequence($pgsql, $table);
        echo sprintf("%-36s %d\n", $table, $count);
    }

    $pgsql->commit();
    echo "Import complete.\n";
} catch (Throwable $e) {
    $pgsql->rollBack();
    fwrite(STDERR, "Import failed: {$e->getMessage()}\n");
    exit(1);
}

function readEnvFile(string $path): array
{
    if (! is_file($path)) {
        return [];
    }

    $values = [];
    foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $line = trim($line);

        if ($line === '' || str_starts_with($line, '#') || ! str_contains($line, '=')) {
            continue;
        }

        [$key, $value] = explode('=', $line, 2);
        $value = trim($value);

        if (
            strlen($value) >= 2
            && (($value[0] === '"' && $value[-1] === '"') || ($value[0] === "'" && $value[-1] === "'"))
        ) {
            $value = substr($value, 1, -1);
        }

        $values[trim($key)] = $value;
    }

    return $values;
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

function resetSequence(PDO $pgsql, string $table): void
{
    $sequence = $pgsql
        ->query("SELECT pg_get_serial_sequence('{$table}', 'id') AS sequence_name")
        ->fetchColumn();

    if ($sequence === false || $sequence === null) {
        return;
    }

    $pgsql->exec(sprintf(
        "SELECT setval(%s, COALESCE((SELECT MAX(id) FROM %s), 1), (SELECT COUNT(*) FROM %s) > 0)",
        $pgsql->quote($sequence),
        quoteIdentifier($table),
        quoteIdentifier($table)
    ));
}

function quoteIdentifier(string $identifier): string
{
    return '"' . str_replace('"', '""', $identifier) . '"';
}
