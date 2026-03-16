<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use NbaBelt\Services\BallDontLieClient;

require __DIR__ . '/../vendor/autoload.php';

// Load .env file if present (skipped in production where env vars are injected directly)
$dotenv = \Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->safeLoad();

$app = AppFactory::create();

// Add error middleware — display errors in development only
$debug = ($_ENV['APP_DEBUG'] ?? 'false') === 'true';
$app->addErrorMiddleware($debug, true, true);

// Pull API key from environment
$apiKey = $_ENV['BALLDONTLIE_API_KEY'] ?? '';
if (empty($apiKey)) {
	die('Error: BALLDONTLIE_API_KEY environment variable is not set');
}
$ballDontLie = new BallDontLieClient($apiKey);

// Load routes
$webRoutes = require __DIR__ . '/../routes/web.php';
$apiRoutes = require __DIR__ . '/../routes/api.php';

$webRoutes($app);
$apiRoutes($app, $ballDontLie);


$app->run();