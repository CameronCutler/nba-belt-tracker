<?php

/**
 * Shared helpers for database scripts.
 *
 * This file is intended for use by scripts under the `database/` folder to
 * centralize common behavior (autoloading, DB path resolution, logging).
 */

declare(strict_types=1);

use NbaBelt\Database\Connection;

/**
 * Ensure Composer autoload is loaded.
 */
function db_autoload(): void
{
    static $loaded = false;
    if ($loaded) {
        return;
    }

    require_once __DIR__ . '/../vendor/autoload.php';
    $loaded = true;
}

/**
 * Resolve the SQLite database path.
 */
function db_get_path(): string
{
    return $_ENV['DB_PATH'] ?? __DIR__ . '/belt.db';
}

/**
 * Initialize (or return existing) PDO connection.
 */
function db_init(?string $dbPath = null): PDO
{
    db_autoload();
    $dbPath ??= db_get_path();
    return Connection::getInstance($dbPath);
}