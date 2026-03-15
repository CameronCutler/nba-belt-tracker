<?php

require_once __DIR__ . '/common.php';

use NbaBelt\Repositories\GameRepository;
use NbaBelt\Repositories\BeltHistoryRepository;
use NbaBelt\Services\BallDontLieClient;

echo " NBA Belt Tracker - Seed Games (Simple)\n";
echo "----------------------------------------\n\n";

try {
    // Initialize database connection
    $dbPath = db_get_path();
    echo "Database path: {$dbPath}\n";
    echo "Database file exists: " . (file_exists($dbPath) ? 'YES' : 'NO') . "\n";

    $pdo = db_init($dbPath);
    echo "Database connection established.\n";

    // Initialize repositories
    $gameRepository = new GameRepository();
    $beltRepository = new BeltHistoryRepository();

    // Current season (2025-2026)
    $currentSeason = $gameRepository->getCurrentSeasonYear();

    // Try to fetch real games from API, fall back to sample data if needed
    $games = [];
    $apiKey = $_ENV['BALLDONTLIE_API_KEY'] ?? 'c15f166e-08f8-4c3f-b96e-60b9cdd7a2a2';

    try {
        echo "Attempting to fetch real games from Ball Don't Lie API...\n";
        $ballDontLieClient = new BallDontLieClient($apiKey);

        // Only fetch new games that are not already in the database.
        // This keeps the build idempotent and avoids re-downloading the whole season every time.
        $lastGameDate = getLastSeededGameDate($pdo);
        if ($lastGameDate) {
            // Add one day so we don't reprocess the last inserted game
            $startDate = date('Y-m-d', strtotime($lastGameDate . ' +1 day'));
            echo "Fetching games starting from {$startDate} (last seeded: {$lastGameDate})\n";
        } else {
            $startDate = "{$currentSeason}-10-01";
            echo "No existing games found; fetching full season starting {$startDate}\n";
        }

        $endDate = date('Y-m-d');

        $apiGames = [];
        $cursor = null;

        // API rate limit: when we hit the limit, pause and retry (Ball Dont Lie rate-limits aggressively)
        $rateLimitSleep = 60; // seconds (1 minute)
        $maxRateLimitRetries = 10;
        $rateLimitRetries = 0;

        do {
            try {
                $response = $ballDontLieClient->getGames($startDate, $endDate, 100, $cursor);
            } catch (Exception $e) {
                // If we hit a rate limit, wait 1 minute and retry the same cursor.
                if (str_contains($e->getMessage(), 'HTTP 429') || str_contains(strtolower($e->getMessage()), 'rate limit')) {
                    $rateLimitRetries++;
                    if ($rateLimitRetries > $maxRateLimitRetries) {
                        throw new Exception("Rate limit hit too many times ({$rateLimitRetries}), aborting.");
                    }

                    echo "Rate limit reached; sleeping {$rateLimitSleep} seconds before retrying (retry #{$rateLimitRetries}, cursor: {$cursor})...\n";
                    sleep($rateLimitSleep);
                    continue;
                }

                // Re-throw non-rate-limit errors
                throw $e;
            }

            $rateLimitRetries = 0; // successful request; reset retry counter

            $pageGames = $response['data'] ?? [];

            if (!empty($pageGames)) {
                $apiGames = array_merge($apiGames, $pageGames);
            }

            $cursor = $response['meta']['next_cursor'] ?? null;
        } while ($cursor);

        if (!empty($apiGames)) {
            echo "Fetched " . count($apiGames) . " games from API.\n";
            $games = processApiGames($apiGames, $currentSeason);
        } else {
            throw new Exception("No games returned from API");
        }
    } catch (Exception $e) {
        echo "API fetch failed: " . $e->getMessage() . "\n";
        echo "Falling back to sample game data...\n";
        // $games = getSampleGames($currentSeason);
    }

    if (empty($games)) {
        throw new Exception("No games available for seeding");
    }

    echo "Processing " . count($games) . " games for belt logic...\n";

    // Insert games and handle belt transfers
    // $pdo is already initialized via db_init()
    $transferCount = 0;

    // If multiple runs happen, using the API game ID avoids duplicate insertion.
    // SQLite will replace on conflict because we use INSERT OR REPLACE.


    // Sort games by date to process them chronologically
    usort($games, function($a, $b) {
        return strtotime($a['game_date']) <=> strtotime($b['game_date']);
    });

    foreach ($games as $game) {
        // Determine if this game involves the current belt holder
        $currentHolder = getCurrentBeltHolderAtDate($pdo, $game['game_date']);
        if ($currentHolder) {
            $game['belt_involved'] = ($game['home_team_id'] == $currentHolder['team_id'] || $game['away_team_id'] == $currentHolder['team_id']) ? 1 : 0;
            echo "  Belt holder found for {$game['game_date']}: Team {$currentHolder['team_id']} - Belt involved: {$game['belt_involved']}\n";
        } else {
            $game['belt_involved'] = 1; // First belt game - assign initial holder
            echo "  No belt holder yet for {$game['game_date']} - this will be the first belt game\n";
        }

        // Insert the game
        $gameRepository->create($game);
        echo "Inserted game ID {$game['id']}: " . getTeamName($game['home_team_id']) . " vs " . getTeamName($game['away_team_id']) . " ({$game['game_date']})" . ($game['belt_involved'] ? " [BELT GAME]" : "") . "\n";

        // Check if this game involves belt transfer
        if ($game['belt_involved'] == 1) {
            if (handleBeltTransfer($game, $pdo)) {
                $transferCount++;
            }
        }
    }

    echo "Successfully seeded " . count($games) . " games.\n";
    echo "Belt has transferred {$transferCount} times during the season.\n";

    // Show final belt holder
    $currentHolder = $beltRepository->getCurrentBeltHolder();
    if ($currentHolder) {
        echo "Current belt holder: {$currentHolder['full_name']} (since {$currentHolder['acquired_date']})\n";
    }

} catch (Exception $e) {
    echo "Error in games seeding: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\nGames seeding completed.\n";

/**
 * Process games from Ball Don't Lie API into our format
 */
function processApiGames(array $apiGames, int $season): array
{
    $processedGames = [];

    foreach ($apiGames as $apiGame) {
        // Skip games that haven't been played yet or don't have scores
        if (empty($apiGame['home_team_score']) || empty($apiGame['visitor_team_score'])) {
            continue;
        }

        // Determine winner
        $winnerId = null;
        if ($apiGame['home_team_score'] > $apiGame['visitor_team_score']) {
            $winnerId = $apiGame['home_team']['id'];
        } elseif ($apiGame['visitor_team_score'] > $apiGame['home_team_score']) {
            $winnerId = $apiGame['visitor_team']['id'];
        }

        // Only include completed games with a winner
        if ($winnerId) {
            $processedGames[] = [
                // Use the Ball Don't Lie game ID so repeated runs don't create duplicates
                'id' => $apiGame['id'],
                'game_date' => $apiGame['date'],
                'home_team_id' => $apiGame['home_team']['id'],
                'away_team_id' => $apiGame['visitor_team']['id'],
                'home_score' => $apiGame['home_team_score'],
                'away_score' => $apiGame['visitor_team_score'],
                'season' => $season,
                'belt_involved' => 0, // Will be set dynamically based on belt holder
                'winner_team_id' => $winnerId
            ];
        }
    }

    return $processedGames;
}

/**
 * Get sample games data when API is unavailable
 */
function getSampleGames(int $season): array
{
    return [
        [
            'id' => 1,
            'game_date' => '2025-10-25',
            'home_team_id' => 21, // OKC Thunder (home)
            'away_team_id' => 8,  // Denver Nuggets
            'home_score' => 115,
            'away_score' => 108,
            'season' => $season,
            'belt_involved' => 0, // Will be set dynamically
            'winner_team_id' => 21 // OKC wins
        ],
        [
            'id' => 2,
            'game_date' => '2025-11-02',
            'home_team_id' => 14, // Lakers
            'away_team_id' => 21, // OKC Thunder (away)
            'home_score' => 122,
            'away_score' => 118,
            'season' => $season,
            'belt_involved' => 0, // Will be set dynamically
            'winner_team_id' => 14 // Lakers win - BELT TRANSFERS!
        ],
        [
            'id' => 3,
            'game_date' => '2025-11-15',
            'home_team_id' => 14, // Lakers (now belt holder)
            'away_team_id' => 10, // Warriors
            'home_score' => 130,
            'away_score' => 125,
            'season' => $season,
            'belt_involved' => 0, // Will be set dynamically
            'winner_team_id' => 14 // Lakers defend
        ],
        [
            'id' => 4,
            'game_date' => '2025-11-28',
            'home_team_id' => 23, // 76ers
            'away_team_id' => 14, // Lakers (away)
            'home_score' => 128,
            'away_score' => 120,
            'season' => $season,
            'belt_involved' => 0, // Will be set dynamically
            'winner_team_id' => 23 // 76ers win - BELT TRANSFERS!
        ],
        [
            'id' => 5,
            'game_date' => '2025-12-10',
            'home_team_id' => 23, // 76ers (now belt holder)
            'away_team_id' => 2,  // Celtics
            'home_score' => 119,
            'away_score' => 115,
            'season' => $season,
            'belt_involved' => 0, // Will be set dynamically
            'winner_team_id' => 23 // 76ers defend
        ],
        [
            'id' => 6,
            'game_date' => '2025-12-25',
            'home_team_id' => 17, // Bucks
            'away_team_id' => 23, // 76ers (away)
            'home_score' => 127,
            'away_score' => 122,
            'season' => $season,
            'belt_involved' => 0, // Will be set dynamically
            'winner_team_id' => 17 // Bucks win - BELT TRANSFERS!
        ],
        [
            'id' => 7,
            'game_date' => '2026-01-08',
            'home_team_id' => 17, // Bucks (now belt holder)
            'away_team_id' => 5,  // Bulls
            'home_score' => 118,
            'away_score' => 112,
            'season' => $season,
            'belt_involved' => 0, // Will be set dynamically
            'winner_team_id' => 17 // Bucks defend
        ],
        [
            'id' => 8,
            'game_date' => '2026-01-22',
            'home_team_id' => 21, // OKC Thunder
            'away_team_id' => 17, // Bucks (away)
            'home_score' => 124,
            'away_score' => 116,
            'season' => $season,
            'belt_involved' => 0, // Will be set dynamically
            'winner_team_id' => 21 // OKC wins - BELT TRANSFERS BACK!
        ],
        [
            'id' => 9,
            'game_date' => '2026-02-05',
            'home_team_id' => 21, // OKC Thunder (belt holder again)
            'away_team_id' => 7,  // Mavericks
            'home_score' => 117,
            'away_score' => 111,
            'season' => $season,
            'belt_involved' => 0, // Will be set dynamically
            'winner_team_id' => 21 // OKC defends
        ],
        [
            'id' => 10,
            'game_date' => '2026-02-18',
            'home_team_id' => 24, // Suns
            'away_team_id' => 21, // OKC Thunder (away)
            'home_score' => 129,
            'away_score' => 125,
            'season' => $season,
            'belt_involved' => 0, // Will be set dynamically
            'winner_team_id' => 24 // Suns win - BELT TRANSFERS!
        ]
    ];
}

/**
 * Handle belt transfer logic when a belt game is played
 * @return bool True if belt transferred, false otherwise
 */
function handleBeltTransfer(array $game, PDO $pdo): bool
{
    // Get current belt holder
    $stmt = $pdo->query("SELECT * FROM belt_history WHERE lost_date IS NULL ORDER BY acquired_date DESC LIMIT 1");
    $currentHolder = $stmt->fetch() ?: null;

    if (!$currentHolder) {
        // Initial belt assignment - no previous holder
        echo "  Initial belt assignment to winner: " . getTeamName($game['winner_team_id']) . "\n";

        // Insert new belt holder record
        $insertSql = "INSERT INTO belt_history (team_id, acquired_date, game_id, notes) VALUES (?, ?, ?, ?)";
        $insertStmt = $pdo->prepare($insertSql);
        $insertStmt->execute([
            $game['winner_team_id'],
            $game['game_date'],
            $game['id'],
            "Initial belt holder - won first game"
        ]);

        return true;
    }

    $currentHolderId = $currentHolder['team_id'];
    $gameWinnerId = $game['winner_team_id'];

    // Check if current holder was involved in this game
    $holderPlayed = ($game['home_team_id'] == $currentHolderId || $game['away_team_id'] == $currentHolderId);

    if (!$holderPlayed) {
        echo "  Belt holder not involved in this game\n";
        return false;
    }

    if ($gameWinnerId == $currentHolderId) {
        echo "  Belt holder " . getTeamName($currentHolderId) . " defended the belt!\n";
        return false;
    }

    // Belt transfers to the winner!
    echo "  BELT TRANSFERS from " . getTeamName($currentHolderId) . " to " . getTeamName($gameWinnerId) . "!\n";

    // Update current holder's record
    $updateSql = "UPDATE belt_history SET lost_date = ?, game_id = ? WHERE id = ?";
    $updateStmt = $pdo->prepare($updateSql);
    $updateStmt->execute([$game['game_date'], $game['id'], $currentHolder['id']]);

    // Insert new belt holder record
    $insertSql = "INSERT INTO belt_history (team_id, acquired_date, game_id, notes) VALUES (?, ?, ?, ?)";
    $insertStmt = $pdo->prepare($insertSql);
    $insertStmt->execute([
        $gameWinnerId,
        $game['game_date'],
        $game['id'],
        "Won belt from " . getTeamName($currentHolderId) . " in game #{$game['id']}"
    ]);

    return true;
}

/**
 * Get team name by ID (simple cache to avoid repeated queries)
 */
function getTeamName(int $teamId): string
{
    static $teamCache = [];

    if (!isset($teamCache[$teamId])) {
        $pdo = db_init();
        $stmt = $pdo->prepare("SELECT abbreviation FROM teams WHERE id = ?");
        $stmt->execute([$teamId]);
        $team = $stmt->fetch();
        $teamCache[$teamId] = $team ? $team['abbreviation'] : "Team{$teamId}";
    }

    return $teamCache[$teamId];
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
    $result = $stmt->fetch();

    if ($result) {
        echo "    Found belt holder for date {$date}: Team {$result['team_id']} (acquired {$result['acquired_date']})\n";
    } else {
        echo "    No belt holder found for date {$date}\n";
    }

    return $result === false ? null : $result;
}

function getLastSeededGameDate(PDO $pdo): ?string
{
    $stmt = $pdo->query('SELECT MAX(game_date) AS max_date FROM games');
    $row = $stmt->fetch();
    return $row ? $row['max_date'] : null;
}
