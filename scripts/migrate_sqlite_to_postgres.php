<?php
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

require_once dirname(__DIR__) . '/db.php';
require_once dirname(__DIR__) . '/auth.php';

$sourcePath = $argv[1] ?? getenv('MITAS_SQLITE_PATH') ?: '';
if ($sourcePath === '' || !is_file($sourcePath)) {
    fwrite(STDERR, "Set MITAS_SQLITE_PATH or pass the path to the existing SQLite database.\n");
    exit(2);
}
if (trim((string)getenv('DATABASE_URL')) === '') {
    fwrite(STDERR, "Set DATABASE_URL to the Supabase Postgres connection URL.\n");
    exit(2);
}

try {
    $source = new PDO('sqlite:' . $sourcePath, null, null, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    $target = mitas_db();
} catch (Throwable $e) {
    fwrite(STDERR, "Could not connect to the source or target database: " . $e->getMessage() . "\n");
    exit(1);
}

if ($target->getAttribute(PDO::ATTR_DRIVER_NAME) !== 'pgsql') {
    fwrite(STDERR, "DATABASE_URL did not create a PostgreSQL connection.\n");
    exit(2);
}

$existingTables = (int)$target->query(
    "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = current_schema() AND table_type = 'BASE TABLE'"
)->fetchColumn();
if ($existingTables !== 0) {
    fwrite(STDERR, "Import stopped: the Supabase public schema is not empty. No data was changed.\n");
    exit(2);
}

$quoteIdentifier = static function (string $identifier): string {
    return '"' . str_replace('"', '""', $identifier) . '"';
};
$sourceTables = $source->query(
    "SELECT name, sql FROM sqlite_master WHERE type = 'table' AND name NOT LIKE 'sqlite_%' AND sql IS NOT NULL ORDER BY name"
)->fetchAll();
if (!$sourceTables) {
    fwrite(STDERR, "The SQLite file contains no application tables.\n");
    exit(2);
}

try {
    $target->beginTransaction();
    foreach ($sourceTables as $table) {
        $target->exec((string)$table['sql']);
    }

    foreach ($source->query(
        "SELECT name, sql FROM sqlite_master WHERE type = 'index' AND sql IS NOT NULL ORDER BY name"
    )->fetchAll() as $index) {
        $target->exec((string)$index['sql']);
    }

    mitas_ensure_user_schema($target);
    $target->exec('CREATE TABLE IF NOT EXISTS mitas_sessions (
        id TEXT PRIMARY KEY,
        session_data TEXT NOT NULL,
        last_activity BIGINT NOT NULL
    )');

    $totalRows = 0;
    foreach ($sourceTables as $table) {
        $tableName = (string)$table['name'];
        if (in_array($tableName, ['mitas_sessions', 'account_email_tokens'], true)) {
            continue;
        }

        $sourceColumns = $source->query('PRAGMA table_info(' . $quoteIdentifier($tableName) . ')')->fetchAll();
        $targetColumnStmt = $target->prepare(
            'SELECT column_name FROM information_schema.columns WHERE table_schema = current_schema() AND table_name = ? ORDER BY ordinal_position'
        );
        $targetColumnStmt->execute([$tableName]);
        $targetColumns = $targetColumnStmt->fetchAll(PDO::FETCH_COLUMN);
        $targetColumnSet = array_fill_keys($targetColumns, true);
        $columns = array_values(array_filter(
            array_column($sourceColumns, 'name'),
            static fn($column) => isset($targetColumnSet[$column])
        ));
        if (!$columns) {
            continue;
        }

        $quotedColumns = implode(', ', array_map($quoteIdentifier, $columns));
        $placeholders = implode(', ', array_fill(0, count($columns), '?'));
        $insert = $target->prepare(
            'INSERT INTO ' . $quoteIdentifier($tableName) . ' (' . $quotedColumns . ') VALUES (' . $placeholders . ')'
        );
        $rows = $source->query('SELECT ' . $quotedColumns . ' FROM ' . $quoteIdentifier($tableName));
        $tableRows = 0;
        while ($row = $rows->fetch(PDO::FETCH_NUM)) {
            $insert->execute($row);
            $tableRows++;
        }
        $totalRows += $tableRows;

        if (in_array('id', $columns, true)) {
            $sequenceStmt = $target->prepare("SELECT pg_get_serial_sequence(?, 'id')");
            $sequenceStmt->execute([$tableName]);
            $sequence = $sequenceStmt->fetchColumn();
            if (is_string($sequence) && $sequence !== '') {
                $maxId = $target->query('SELECT MAX(' . $quoteIdentifier('id') . ') FROM ' . $quoteIdentifier($tableName))->fetchColumn();
                $setSequence = $target->prepare('SELECT setval(CAST(? AS regclass), ?, ?)');
                $setSequence->execute([$sequence, $maxId === false || $maxId === null ? 1 : (int)$maxId, $maxId !== false && $maxId !== null]);
            }
        }

        fwrite(STDOUT, $tableName . ': ' . $tableRows . " rows copied\n");
    }
    $target->commit();
    fwrite(STDOUT, "Import complete. {$totalRows} rows copied. Old sessions and email tokens were not imported.\n");
} catch (Throwable $e) {
    if ($target->inTransaction()) {
        $target->rollBack();
    }
    fwrite(STDERR, "Import failed and row inserts were rolled back: " . $e->getMessage() . "\n");
    exit(1);
}
