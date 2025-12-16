<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use NbaBelt\Http\JsonResponse;

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