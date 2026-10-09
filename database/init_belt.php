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

    $championName = $_ENV['OFFSEASON_CHAMPION'] ?? 'New York Knicks';
    $openingNight = $_ENV['NEXT_SEASON_START'] ?? '2026-10-20';
    $seasonStartDate = DateTimeImmutable::createFromFormat('!Y-m-d', $openingNight);

    if (!$seasonStartDate || $seasonStartDate->format('Y-m-d') !== $openingNight) {
        throw new InvalidArgumentException('NEXT_SEASON_START must be a valid date in YYYY-MM-DD format.');
    }
    $seasonStart = $openingNight;

    $teamStmt = $pdo->prepare('SELECT id FROM teams WHERE full_name = ?');
    $teamStmt->execute([$championName]);
    $championTeamId = $teamStmt->fetchColumn();

    if ($championTeamId === false) {
        throw new RuntimeException("OFFSEASON_CHAMPION '{$championName}' does not match a seeded team.");
    }

    $championData = [
        'team_id' => (int) $championTeamId,
        'acquired_date' => $seasonStart,
        'notes' => 'Reigning NBA champion - opening belt holder'
    ];

    $existing = $pdo->query("SELECT COUNT(*) FROM belt_history")->fetchColumn();

    if ($existing > 0) {
        $legacyStartDate = $seasonStartDate->format('Y') . '-10-01';
        $bootstrapStmt = $pdo->prepare(
            'SELECT id FROM belt_history
             WHERE team_id = ? AND acquired_date = ? AND lost_date IS NULL
               AND game_id IS NULL AND notes = ?
             ORDER BY id LIMIT 1'
        );
        $bootstrapStmt->execute([
            $championData['team_id'],
            $legacyStartDate,
            'Reigning NBA champion - opening belt holder',
        ]);
        $bootstrapId = $bootstrapStmt->fetchColumn();

        if ($bootstrapId !== false) {
            $updateStmt = $pdo->prepare('UPDATE belt_history SET acquired_date = ? WHERE id = ?');
            $updateStmt->execute([$seasonStart, $bootstrapId]);
            echo "Updated opening belt date to {$seasonStart}.\n";
            return;
        }

        echo "Belt history already initialized ({$existing} records found), skipping.\n";
        return;
    }

    // Only initialize if belt_history is empty — skips on subsequent deploys
    echo "Initializing belt history with {$championName} ({$seasonStart})...\n";

    $sql = "INSERT INTO belt_history (team_id, acquired_date, notes) VALUES (?, ?, ?)";
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
        $stmt = $pdo->query("SELECT * FROM belt_history WHERE team_id = {$championData['team_id']}");
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