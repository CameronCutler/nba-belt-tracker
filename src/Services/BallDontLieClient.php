<?php

namespace NbaBelt\Services;

use Exception;

class BallDontLieClient
{
	private string $apiKey;
	private string $baseUrl = 'https://api.balldontlie.io/v1';

	public function __construct(string $apiKey)
	{
		$this->apiKey = $apiKey;
	}

	/**
	 * GET request to API
	 * @param string $endpoint API endpoint path
	 * @param array $params Query parameters
	 * @return array JSON response that has been decoded
	 * @throws Exception When cURL call fails
	 * @noinspection PhpComposerExtensionStubsInspection
	 */
	private function request(string $endpoint, array $params = []): array
	{
		$url = $this->baseUrl . $endpoint;

		if (!empty($params)) {
			$url .= '?' . http_build_query($params);
		}

		$ch = curl_init();
		curl_setopt_array($ch, [
			CURLOPT_URL => $url,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_HTTPHEADER => [
				'Authorization: ' . $this->apiKey,
				'Accept: application/json',
			],
			CURLOPT_TIMEOUT => 30,
			// TODO Remove the SSL cert workaround in favor of CA certificates made on build time
			CURLOPT_SSL_VERIFYPEER => false,
			CURLOPT_SSL_VERIFYHOST => false
		]);

		$response = curl_exec($ch);
		$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		$error = curl_error($ch);
		curl_close($ch);

		if ($error) {
			throw new Exception("cURL Error: {$error}");
		}

		if ($httpCode != 200) {
			throw new Exception("API Error: HTTP {$httpCode} - {$response}");
		}

		$data = json_decode($response, true);

		if (json_last_error() != JSON_ERROR_NONE) {
			throw new Exception("JSON Decode Error: " . json_last_error_msg());
		}

		return $data;
	}

	/**
	 * Get all active NBA teams
	 *
	 * @return array List of teams
	 * @throws Exception When cURL fails
	 */
	public function getActiveTeams(): array
	{
		$response = $this->request('/teams');
		$allTeams = $response['data'];

		// Filter only the active teams using division data
		$activeTeams = array_filter($allTeams, function($team) {
			return !empty($team['division']);
		});

		return [
			'data' => array_values($activeTeams),
			'meta' => $response['meta'] ?? []
		];
	}

	/**
	 * Get a specific team by ID
	 *
	 * @param int $teamId
	 * @return array Team Data
	 * @throws Exception When API request fails
	 */
	public function getTeam(int $teamId): array
	{
		return $this->request("/teams/{$teamId}");
	}

	/**
	 * Gets array of games give a data range
	 *
	 * @param string $startDate
	 * @param string $endDate
	 * @param int $perPage
	 * @param string|null $cursor
	 * @return array
	 * @throws Exception
	 */
	public function getGames(
		string $startDate,
		string $endDate,
		int $perPage = 25,
		?string $cursor = null
	): array {
		$params = [
			'start_date' => $startDate,
			'end_date' => $endDate,
			'per_page' => min($perPage, 100), // API max is 100
		];

		if ($cursor) {
			$params['cursor'] = $cursor;
		}

		return $this->request('/games', $params);
	}

	/**
	 * Gets array of all the games on a given date. Uses the getGames function
	 *
	 * @param string $gameDate Date (YYY-MM-DD)
	 * @return array Games data
	 * @throws Exception
	 */
	public function getGamesByDate(string $gameDate): array
	{
		return $this->getGames($gameDate, $gameDate, 100);
	}

	/**
	 * Get games for a specific team
	 *
	 * @param int $teamId
	 * @param string $startDate
	 * @param string $endDate
	 * @return array
	 * @throws Exception
	 */
	public function getTeamGames(int $teamId, string $startDate, string $endDate): array
	{
		$params = [
			'team_ids[]' => $teamId,
			'start_date' => $startDate,
			'end_date' => $endDate,
			'per_page' => 100,
		];

		return $this->request('/games', $params);
	}

	/**
	 * Get current season year
	 *
	 * @return int Current season start year
	 */
	public function getCurrentSeasonYear(): int
	{
		$currentMonth = (int) date('n');
		$currentYear = (int) date('Y');

		// NBA season starts in October (10th month)
		// Off Season is July - September (7-9)

		if ($currentMonth >= 10) {
			return $currentYear;
		} else  {
			// off season OR early months (Jan - June)
			// return the previous year
			return $currentYear - 1;
		}
	}


} // class end