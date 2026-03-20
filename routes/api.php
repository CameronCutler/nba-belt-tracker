<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use NbaBelt\Http\JsonResponse;
use NbaBelt\Repositories\BeltHistoryRepository;
use NbaBelt\Repositories\GameRepository;

// This file returns a function that registers routes
return function ($app, $ballDontLie) {
	// GET Current belt holder
	$app->get('/api/belt/holder', function (Request $request, Response $response) {
		try {
			$holder = (new BeltHistoryRepository())->getCurrentBeltHolder();
			if (!$holder) {
				return JsonResponse::notFound($response, 'No belt holder found');
			}
			return JsonResponse::success($response, $holder);
		} catch (Exception $e) {
			return JsonResponse::error($response, 'Failed to fetch belt holder: ' . $e->getMessage());
		}
	});

// GET Active Teams
	$app->get('/api/teams', function(Request $request, Response $response) use ($ballDontLie) {
		try {
			$teams = $ballDontLie->getActiveTeams();
			return JsonResponse::success($response, $teams);
		} catch (Exception $e) {
			return JsonResponse::error($response, 'Failed to fetch teams: ' . $e->getMessage());
		}
	});

// GET Games from today (live from BDL API, enriched with belt context)
	$app->get('/api/games/today', function (Request $request, Response $response) use ($ballDontLie) {
		try {
			$today = (new \DateTime('now', new \DateTimeZone('America/Los_Angeles')))->format('Y-m-d');
			$result = $ballDontLie->getGamesByDate($today);
			$games  = $result['data'] ?? [];

			$games = (new BeltHistoryRepository())->enrichGamesWithBeltContext($games);
			$result['data'] = $games;
			return JsonResponse::success($response, $result);
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
				$beltRepo = new BeltHistoryRepository();
				$currentHolder = $beltRepo->getCurrentBeltHolder();


				return JsonResponse::success($response, [
					'message' => 'No belt games found for current season',
					'debug' => [
						'current_season' => $currentSeason,
						'total_games_in_season' => $allGamesCount,
						'current_belt_holder' => $currentHolder ? $currentHolder['full_name'] : 'None',
						'sample_games' => array_slice($allGames, 0, 3)
					]
				]);
			}

			return JsonResponse::success($response, $beltGames);
		} catch (Exception $e) {
			return JsonResponse::error($response, 'Failed to fetch belt games: ' . $e->getMessage());
		}
	});

// GET Games from a given date
	$app->get('/api/games/{date}', function (Request $request, Response $response, array $args) {
		$date = $args['date'];
		try {
			if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
				return JsonResponse::badRequest($response, 'Invalid Date format. Use YYYY-mm-dd');
			}
			$dateTime = \DateTime::createFromFormat('Y-m-d', $date);
			if (!$dateTime || $dateTime->format('Y-m-d') != $date) {
				return JsonResponse::badRequest($response, 'Invalid Date. Please provide a valid calendar date');
			}

			$games = (new GameRepository())->getGamesByDate($date);
			return JsonResponse::success($response, $games);
		} catch (Exception $e) {
			return JsonResponse::error($response, 'Failed to fetch games from ' . $date . ': ' . $e->getMessage());
		}
	});
};