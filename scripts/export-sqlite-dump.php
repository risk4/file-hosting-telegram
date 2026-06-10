<?php
$sourcePath = __DIR__ . '/../database/database.sqlite';
$targetPath = __DIR__ . '/../database/telestore.sql';

if (!file_exists($sourcePath)) {
    fwrite(STDERR, "Source database not found: $sourcePath\n");
    exit(1);
}

$db = new SQLite3($sourcePath);

$dump = [];
$dump[] = 'PRAGMA foreign_keys=OFF;';
$dump[] = 'BEGIN TRANSACTION;';

// Export schema for tables, indexes, views and triggers.
$schema = $db->query("SELECT type, name, sql FROM sqlite_master WHERE sql NOT NULL AND name NOT LIKE 'sqlite_%' ORDER BY tbl_name, type DESC, name");
while ($row = $schema->fetchArray(SQLITE3_ASSOC)) {
    if ($row['type'] === 'table') {
        $dump[] = $row['sql'] . ';';
    } elseif ($row['type'] === 'index' || $row['type'] === 'trigger' || $row['type'] === 'view') {
        $dump[] = $row['sql'] . ';';
    }
}

// Export all table data.
$tables = $db->query("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%' ORDER BY name");
while ($tbl = $tables->fetchArray(SQLITE3_ASSOC)) {
    $name = $tbl['name'];
    $rows = $db->query("SELECT * FROM \"$name\"");
    while ($row = $rows->fetchArray(SQLITE3_ASSOC)) {
        $columns = array_map(function ($column) {
            return '"' . str_replace('"', '""', $column) . '"';
        }, array_keys($row));

        $values = array_map(function ($value) {
            if ($value === null) {
                return 'NULL';
            }
            if ($value === '') {
                return "''";
            }
            return "'" . str_replace("'", "''", $value) . "'";
        }, array_values($row));

        $dump[] = 'INSERT INTO "' . $name . '" (' . implode(', ', $columns) . ') VALUES (' . implode(', ', $values) . ');';
    }
}

$dump[] = 'COMMIT;';

file_put_contents($targetPath, implode("\n", $dump) . "\n");

echo "SQL dump written to $targetPath\n";
