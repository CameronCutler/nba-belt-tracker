<?php

require_once __DIR__ . '/common.php';

echo " NBA Belt Tracker - Initialize Belt History (Offline)\n";
echo "-----------------------------------------------------\n\n";

try {
    // Initialize database connection
    $dbPath = db_get_path();
    echo "Database path: {$dbPath}\n";
    echo "Database file exists: " . (file_exists($dbPath) ? 'YES' : 'NO') . "\n";

    $pdo = db_init($dbPath);
    echo "Database connection established.\n";

    // Hardcoded champion data - OKC Thunder won 2024-2025 NBA Finals
    $championData = [
        'team_id' => 21, // OKC Thunder ID
        'acquired_date' => '2025-06-17', // NBA Finals date
        'notes' => '2024-2025 NBA Champion - Initial belt holder'
    ];

    echo "Initializing belt history with OKC Thunder (2024-2025 champions)...\n";

    // Insert directly into database
    $sql = "INSERT OR REPLACE INTO belt_history (id, team_id, acquired_date, notes) VALUES (1, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    $result = $stmt->execute([
        $championData['team_id'],
        $championData['acquired_date'],
        $championData['notes']
    ]);

    if ($result) {
        echo "Successfully inserted belt history record!\n";

        // Verify the record was inserted
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM belt_history");
        $count = $stmt->fetch()['count'];
        echo "Total belt history records: {$count}\n";

        // Show the record
        $stmt = $pdo->query("SELECT * FROM belt_history WHERE team_id = 21");
        $record = $stmt->fetch();
        if ($record) {
            echo "Belt record: Team {$record['team_id']}, acquired {$record['acquired_date']}\n";
        } else {
            echo "ERROR: Belt record not found after insertion!\n";
        }
    } else {
        echo "ERROR: Failed to insert belt history record!\n";
    }

} catch (Exception $e) {
    echo "Error initializing belt history: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\nBelt history initialization completed.\n";