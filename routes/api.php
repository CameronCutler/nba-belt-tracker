<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use NbaBelt\Http\JsonResponse;
use NbaBelt\Repositories\GameRepository;

// This file returns a function that registers routes
return function ($app, $ballDontLie) {
	// GET Active Teams
	$app->get('/api/teams', function(Request $request, Response $response) use ($ballDontLie) {
		try {
			$teams = $ballDontLie->getActiveTeams();
			return JsonResponse::success($response, $teams);
		} catch (Exception $e) {
			return JsonResponse::error($response, 'Failed to fetch teams: ' . $e->getMessage());
		}
	});

// GET Games from today
	$app->get('/api/games/today', function (Request $request, Response $response) use ($ballDontLie) {
		try {
			$today = date('Y-m-d');
			$games = $ballDontLie->getGamesByDate($today);
			return JsonResponse::success($response, $games);
		} catch (Exception $e) {
			return JsonResponse::error($response, 'Failed to fetch games for today: ' . $e->getMessage());
		}
	});

// GET Belt games for current season
	$app->get('/api/games/belt', function (Request $request, Response $response) {
		try {
			$gameRepo = new GameRepository();
			$beltGames = $gameRepo->getCurrentSeasonBeltGames();

			// If no belt games found, return a helpful message with debug info
			if (empty($beltGames)) {
				$currentSeason = $gameRepo->getCurrentSeasonYear();

				// Check if there are any games at all
				$allGames = $gameRepo->getGamesBySeason($currentSeason);
				$allGamesCount = count($allGames);

				// Check belt history
				$beltRepo = new NbaBelt\Repositories\BeltHistoryRepository();
				$currentHolder = $beltRepo->getCurrentBeltHolder();

				// Check for seeding logs
				$logs = [];
				$logFiles = [
					'migrate.log',
					'seed_teams.log', 
					'init_belt.log',
					'seed_games.log'
				];
				foreach ($logFiles as $logFile) {
					$logPath = "/var/www/database/{$logFile}";
					if (file_exists($logPath)) {
						$logs[$logFile] = file_get_contents($logPath);
					} else {
						$logs[$logFile] = "Log file not found: {$logPath}";
					}
				}

				return JsonResponse::success($response, [
					'message' => 'No belt games found for current season',
					'debug' => [
						'current_season' => $currentSeason,
						'total_games_in_season' => $allGamesCount,
						'current_belt_holder' => $currentHolder ? $currentHolder['full_name'] : 'None',
						'sample_games' => array_slice($allGames, 0, 3),
						'seeding_logs' => $logs
					]
				]);
			}

			return JsonResponse::success($response, $beltGames);
		} catch (Exception $e) {
			return JsonResponse::error($response, 'Failed to fetch belt games: ' . $e->getMessage());
		}
	});

// POST endpoint to manually seed games (for testing)
	$app->post('/api/admin/seed-games', function (Request $request, Response $response) {
		try {
			// Run the seeding scripts in order
			$scripts = [
				'database/migrate.php',
				'database/seed_teams_simple.php', 
				'database/init_belt.php',
				'database/seed_games_simple.php'
			];
			
			$output = [];
			foreach ($scripts as $script) {
				$cmdOutput = shell_exec("cd /var/www && php {$script} 2>&1");
				$output[] = [
					'script' => $script,
					'output' => $cmdOutput
				];
			}
			
			return JsonResponse::success($response, [
				'message' => 'Seeding completed',
				'results' => $output
			]);
		} catch (Exception $e) {
			return JsonResponse::error($response, 'Failed to seed: ' . $e->getMessage());
		}
	});

// GET Games from a given date
	$app->get('/api/games/{date}', function (Request $request, Response $response, array $args) use ($ballDontLie) {
		$date = $args['date'];
		try {
			if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
				return JsonResponse::badRequest($response, 'Invalid Date format. Use YYYY-mm-dd');
			}
			$dateTime = \DateTime::createFromFormat('Y-m-d', $date);
			if (!$dateTime || $dateTime->format('Y-m-d') != $date) {
				return JsonResponse::badRequest($response, 'Invalid Date. Please provide a valid calendar date');
			}

			$games = $ballDontLie->getGamesByDate($date);
			return JsonResponse::success($response, $games);
		} catch (Exception $e) {
			return JsonResponse::error($response, 'Failed to fetch games from ' . $date . ': ' . $e->getMessage());
		}
	});
};