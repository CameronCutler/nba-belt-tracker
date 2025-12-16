<?php

require __DIR__ . '/../vendor/autoload.php';

use NbaBelt\Database\Connection;

// Load environment
// $dotenv = \Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
// $dotenv->load();

// Get db path from env or use default
$dbPath = $_ENV['DB_PATH'] ?? __DIR__ . '/belt.db';

echo "database path: {$dbPath}\n";
echo "Running migrations ... \n\n";

try {
	// Initialize
	Connection::getInstance($dbPath);

	// Run migration
	Connection::migrate(__DIR__ . '/migrations');

	echo "\n✓ Database setup complete!\n";

} catch (Exception $e) {

	echo "\n X Error: " . $e->getMessage() . "\n";
	exit(1);
}