<?php

namespace NbaBelt\Http;

use Psr\Http\Message\ResponseInterface as Response;

class JsonResponse
{
	/**
	 * Return a successful JSON response
	 *
	 * @param Response $response
	 * @param mixed $data
	 * @param int $status
	 * @return Response
	 */
	public static function success(Response $response, mixed $data, int $status = 200): Response
	{
		$response->getBody()->write(json_encode($data, JSON_PRETTY_PRINT));
		return $response
			->withHeader('Content-Type', 'application/json')
			->withStatus($status);
	}

	/**
	 * Return an error JSON response
	 *
	 * @param Response $response
	 * @param string $message
	 * @param int $status
	 * @param array $details
	 * @return Response
	 */
	public static function error(Response $response, string $message, int $status = 500, array $details = []): Response
	{
		$error = [
			'error' => true,
			'message' => $message
		];

		if (!empty($details)) {
			$error['details'] = $details;
		}

		$response->getBody()->write(json_encode($error, JSON_PRETTY_PRINT));
		return $response
			->withHeader('Content-Type', 'application/json')
			->withStatus($status);
	}

	/**
	 * User submitted bad request. Returns 400
	 * @param Response $response
	 * @param string $message
	 * @return Response
	 */
	public static function badRequest(Response $response, string $message ): Response
	{
		return self::error($response, $message, 400);
	}

	/**
	 * Page not found. Returns 404
	 * @param Response $response
	 * @param string $message
	 * @return Response
	 */
	public static function notFound(Response $response, string $message ): Response
	{
		return self::error($response, $message, 404);
	}

	/**
	 * Return a paginated JSON response
	 *
	 * @param Response $response
	 * @param array $data
	 * @param array $meta
	 * @return Response
	 */
	public static function paginated(Response $response, array $data, array $meta) : Response
	{
		$result = [
			'data' => $data,
			'meta' => $meta
		];
		return self::success($response, $result);
	}
}