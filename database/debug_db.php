<?php

// Debug script to check database contents
require __DIR__ . '/../vendor/autoload.php';

use NbaBelt\Database\Connection;
use NbaBelt\Repositories\GameRepository;
use NbaBelt\Repositories\BeltHistoryRepository;

echo "Database Debug Info\n";
echo "===================\n\n";

try {
    $dbPath = __DIR__ . '/belt.db';
    Connection::getInstance($dbPath);
    $pdo = Connection::getInstance();

    // Check games table
    $stmt = $pdo->query('SELECT COUNT(*) as count FROM games');
    $gamesCount = $stmt->fetch()['count'];
    echo "Total games in database: {$gamesCount}\n";

    if ($gamesCount > 0) {
        // Check belt games
        $stmt = $pdo->query('SELECT COUNT(*) as count FROM games WHERE belt_involved = 1');
        $beltGamesCount = $stmt->fetch()['count'];
        echo "Belt games (belt_involved=1): {$beltGamesCount}\n";

        // Check current season
        $gameRepo = new GameRepository();
        $currentSeason = $gameRepo->getCurrentSeasonYear();
        echo "Current season year: {$currentSeason}\n";

        // Check belt games for current season
        $stmt = $pdo->prepare('SELECT COUNT(*) as count FROM games WHERE belt_involved = 1 AND season = ?');
        $stmt->execute([$currentSeason]);
        $currentSeasonBeltGames = $stmt->fetch()['count'];
        echo "Belt games for current season ({$currentSeason}): {$currentSeasonBeltGames}\n";

        // Show sample games
        echo "\nSample games:\n";
        $stmt = $pdo->query('SELECT id, game_date, home_team_id, away_team_id, belt_involved, season FROM games LIMIT 5');
        $games = $stmt->fetchAll();
        foreach ($games as $game) {
            echo "  ID {$game['id']}: {$game['game_date']} - Belt: {$game['belt_involved']} - Season: {$game['season']}\n";
        }
    }

    // Check belt history
    $stmt = $pdo->query('SELECT COUNT(*) as count FROM belt_history');
    $beltHistoryCount = $stmt->fetch()['count'];
    echo "\nBelt history records: {$beltHistoryCount}\n";

    if ($beltHistoryCount > 0) {
        // Show all belt history records
        $stmt = $pdo->query('SELECT * FROM belt_history ORDER BY acquired_date');
        $history = $stmt->fetchAll();
        echo "Belt history:\n";
        foreach ($history as $record) {
            echo "  Team {$record['team_id']}: acquired {$record['acquired_date']}, lost " . ($record['lost_date'] ?: 'N/A') . "\n";
        }

        $beltRepo = new BeltHistoryRepository();
        $currentHolder = $beltRepo->getCurrentBeltHolder();
        if ($currentHolder) {
            echo "Current belt holder: {$currentHolder['full_name']} (since {$currentHolder['acquired_date']})\n";
        }

        // Test belt holder lookup for a specific date
        $testDate = '2025-10-25'; // First game date
        $holderAtDate = getCurrentBeltHolderAtDate($pdo, $testDate);
        if ($holderAtDate) {
            echo "Belt holder on {$testDate}: Team {$holderAtDate['team_id']} (acquired {$holderAtDate['acquired_date']})\n";
        } else {
            echo "No belt holder found for date {$testDate}\n";
        }
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

/**
 * Get the belt holder at a specific date (for chronological processing)
 */
function getCurrentBeltHolderAtDate(PDO $pdo, string $date): ?array
{
    $stmt = $pdo->prepare("
        SELECT * FROM belt_history
        WHERE acquired_date <= ?
        AND (lost_date IS NULL OR lost_date > ?)
        ORDER BY acquired_date DESC
        LIMIT 1
    ");
    $stmt->execute([$date, $date]);
    return $stmt->fetch();
}