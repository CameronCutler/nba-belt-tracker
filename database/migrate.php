<?php

echo "Starting migrate.php...\n";
echo "Current working directory: " . getcwd() . "\n";
echo "Script directory: " . __DIR__ . "\n";

require __DIR__ . '/../vendor/autoload.php';
echo "Autoload loaded\n";

use NbaBelt\Database\Connection;

// Load environment
// $dotenv = \Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
// $dotenv->load();

// Get db path from env or use default
$dbPath = $_ENV['DB_PATH'] ?? __DIR__ . '/belt.db';

echo "database path: {$dbPath}\n";
echo "Database file exists: " . (file_exists($dbPath) ? 'YES' : 'NO') . "\n";
echo "Running migrations ... \n\n";

try {
	// Initialize
	echo "Initializing database connection...\n";
	Connection::getInstance($dbPath);
	echo "Database connection established\n";

	// Run migration
	echo "Running migrations from: " . __DIR__ . '/migrations' . "\n";
	Connection::migrate(__DIR__ . '/migrations');

	echo "\n✓ Database setup complete!\n";

} catch (Exception $e) {

	echo "\n X Error: " . $e->getMessage() . "\n";
	exit(1);
}