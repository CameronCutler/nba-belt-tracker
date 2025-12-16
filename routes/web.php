<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

return function ($app) {
	// Home route
	$app->get('/', function (Request $request, Response $response) {
		ob_start();
		require __DIR__ . '/../views/home.php';
		$html = ob_get_clean();

		$response->getBody()->write($html);
		return $response->withHeader('Content-Type', 'text/html');
	});

	// GET Health Check
	$app->get('/health-check', function (Request $request, Response $response, $args) {
		$response->getBody()->write("Hello World! The NBA Belt Tracker is running...");
		return $response;
	});
};