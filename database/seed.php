<?php

require __DIR__ . '/../vendor/autoload.php';

use NbaBelt\Services\BallDontLieClient;
use NbaBelt\Database\Connection;
use NbaBelt\Repositories\TeamRepository;

echo " NBA Belt Tracker - Database Seed Script\n";
echo "----------------------------------------\n\n";

try {
    // Initialize database connection
    $dbPath = $_ENV['DB_PATH'] ?? __DIR__ . '/belt.db';
    $apiKey = $_ENV['BALLDONTLIE_API_KEY'] ?? null;
    Connection::getInstance($dbPath);
    echo "Database connection established at: $dbPath\n";

    // Initialize API client
    $ballDontLieClient = new BallDontLieClient($apiKey);
    echo "Ball Don't Lie API client initialized.\n";

    // Initialize Team Repository
    $teamRepository = new TeamRepository();
    echo "Team Repository initialized.\n";  

    // Check if teams already exist
    if (!$teamRepository->isEmpty()) {
        echo "Teams already exist in the database.\n";
        echo "Do you want to reseed? (yes:no): ";
        $handle = fopen ("php://stdin","r");
        $line = trim(fgets($handle));
        if ($line !== "yes") {
            echo "Skipping reseeding.\n";
            exit(0);
        }
        echo "\n";
    }

    // Fetch teams from the API
    echo "Fetching teams from Ball Don't Lie API...\n";
    $response = $ballDontLieClient->getActiveTeams();
    $teams = $response['data'] ?? [];
    if (empty($teams)) {
        throw new Exception("No teams data retrieved from the API.");
    }

    echo "Fetched " . count($teams) . " teams from the API.\n";

    // Insert teams into the database
    echo "Seeding teams into the database...\n";
    $teamRepository->bulkInsert($teams);

    echo "Successfully seeded " . count($teams) . " teams into the database.\n";

    // Display inserted teams
    echo "Teams in the database:\n";
    echo str_repeat("=", 50) . "\n";

    $allTeams = $teamRepository->getAll();
    foreach ($allTeams as $team) {
        echo sprintf(
            "ID: %d | Name: %s | City: %s | Conference: %s | Division: %s\n",
            $team['id'],
            $team['full_name'],
            $team['city'],
            $team['conference'],
            $team['division']
        );
    }

    echo str_repeat("=", 50) . "\n";
    echo "Team seeding completed successfully.\n";

} catch (Exception $e) {
    echo "An error occurred during the seeding process: " . $e->getMessage() . "\n";
    exit(1);    
}

echo "\nDatabase seeding completed.\n";

