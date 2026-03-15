<?php

require_once __DIR__ . '/common.php';

use NbaBelt\Database\Connection;

/**
 * Migrate the database schema.
 */
function runMigrations(): void
{
    $dbPath = db_get_path();
    $output = [];

    $output[] = "Running migrations...";
    $output[] = "Database path: {$dbPath}";
    $output[] = "Database file exists: " . (file_exists($dbPath) ? 'YES' : 'NO');

    db_init($dbPath);
    $output[] = "Connected to database.";

    $migrationsDir = __DIR__ . '/migrations';
    $output[] = "Applying migrations in: {$migrationsDir}";

    Connection::migrate($migrationsDir);
    $output[] = "✓ Database schema is up to date.";

    $message = implode("\n", $output) . "\n";
    echo $message;
}

runMigrations();
