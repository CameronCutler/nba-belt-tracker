<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use NbaBelt\Services\BallDontLieClient;

require __DIR__ . '/../vendor/autoload.php';

// Allows access to .env
// $dotenv = \Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
// $dotenv->load();


$app = AppFactory::create();


// Add error middleware
// TODO turn off first true in production
$app->addErrorMiddleware(true, true, true);


// Pull in ENV value and initialise client class
// $apiKey = $_ENV['BALLDONTLIE_API_KEY'] ?? '';
$apiKey = 'c15f166e-08f8-4c3f-b96e-60b9cdd7a2a2';
if (empty($apiKey)) {
	die('Error: API Key not valid');
}
$ballDontLie = new BallDontLieClient($apiKey);

// Load routes
$webRoutes = require __DIR__ . '/../routes/web.php';
$apiRoutes = require __DIR__ . '/../routes/api.php';

$webRoutes($app);
$apiRoutes($app, $ballDontLie);


$app->run();