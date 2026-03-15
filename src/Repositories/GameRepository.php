<?php

namespace NbaBelt\Repositories;

use NbaBelt\Database\Connection;
use PDO;

class GameRepository
{
    private PDO $db;

    public function __construct()
    {
        $dbPath = $_ENV['DB_PATH'] ?? __DIR__ . '/../../database/belt.db';
        $this->db = Connection::getInstance($dbPath);
    }

    /**
     * Get all games from database
     *
     * @return array
     */
    public function getAll(): array
    {
        $stmt = $this->db->query('SELECT * FROM games ORDER BY game_date DESC');
        return $stmt->fetchAll();
    }

    /**
     * Find a game by ID
     *
     * @param int $id
     * @return array|null
     */
    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM games WHERE id = ?');
        $stmt->execute([$id]);
        $result = $stmt->fetch();

        return $result ?: null;
    }

    /**
     * Get games for a specific season
     *
     * @param int $season
     * @return array
     */
    public function getGamesBySeason(int $season): array
    {
        $stmt = $this->db->prepare('SELECT * FROM games WHERE season = ? ORDER BY game_date DESC');
        $stmt->execute([$season]);
        return $stmt->fetchAll();
    }

    /**
     * Get games by date range
     *
     * @param string $startDate
     * @param string $endDate
     * @return array
     */
    public function getGamesByDateRange(string $startDate, string $endDate): array
    {
        $stmt = $this->db->prepare('SELECT * FROM games WHERE game_date BETWEEN ? AND ? ORDER BY game_date DESC');
        $stmt->execute([$startDate, $endDate]);
        return $stmt->fetchAll();
    }

    /**
     * Create a new game
     *
     * @param array $data Game data
     * @return bool
     */
    public function create(array $data): bool
    {
        $sql = 'INSERT INTO games (id, game_date, home_team_id, away_team_id, home_score, away_score, season, belt_involved, winner_team_id)
                VALUES (:id, :game_date, :home_team_id, :away_team_id, :home_score, :away_score, :season, :belt_involved, :winner_team_id)';

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            ':id' => $data['id'],
            ':game_date' => $data['game_date'],
            ':home_team_id' => $data['home_team_id'],
            ':away_team_id' => $data['away_team_id'],
            ':home_score' => $data['home_score'] ?? null,
            ':away_score' => $data['away_score'] ?? null,
            ':season' => $data['season'],
            ':belt_involved' => $data['belt_involved'] ?? 0,
            ':winner_team_id' => $data['winner_team_id'] ?? null,
        ]);
    }

    /**
     * Update an existing game
     *
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function update(int $id, array $data): bool
    {
        $sql = 'UPDATE games
                SET game_date = :game_date,
                    home_team_id = :home_team_id,
                    away_team_id = :away_team_id,
                    home_score = :home_score,
                    away_score = :away_score,
                    season = :season,
                    belt_involved = :belt_involved,
                    winner_team_id = :winner_team_id,
                    created_at = datetime("now")
                WHERE id = :id';

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            ':id' => $id,
            ':game_date' => $data['game_date'],
            ':home_team_id' => $data['home_team_id'],
            ':away_team_id' => $data['away_team_id'],
            ':home_score' => $data['home_score'] ?? null,
            ':away_score' => $data['away_score'] ?? null,
            ':season' => $data['season'],
            ':belt_involved' => $data['belt_involved'] ?? 0,
            ':winner_team_id' => $data['winner_team_id'] ?? null,
        ]);
    }

    /**
     * Delete a game
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM games WHERE id = ?');
        return $stmt->execute([$id]);
    }

    /**
     * Bulk insert games (efficient for seeding)
     *
     * @param array $games Array of game data
     * @return bool
     */
    public function bulkInsert(array $games): bool
    {
        $this->db->beginTransaction();

        try {
            $sql = 'INSERT OR REPLACE INTO games (id, game_date, home_team_id, away_team_id, home_score, away_score, season, belt_involved, winner_team_id)
                    VALUES (:id, :game_date, :home_team_id, :away_team_id, :home_score, :away_score, :season, :belt_involved, :winner_team_id)';

            $stmt = $this->db->prepare($sql);

            foreach ($games as $game) {
                $stmt->execute([
                    ':id' => $game['id'],
                    ':game_date' => $game['game_date'],
                    ':home_team_id' => $game['home_team_id'],
                    ':away_team_id' => $game['away_team_id'],
                    ':home_score' => $game['home_score'] ?? null,
                    ':away_score' => $game['away_score'] ?? null,
                    ':season' => $game['season'],
                    ':belt_involved' => $game['belt_involved'] ?? 0,
                    ':winner_team_id' => $game['winner_team_id'] ?? null,
                ]);
            }

            $this->db->commit();
            return true;

        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * Get all belt games for the current NBA season
     * 
     * @return array
     */
    public function getCurrentSeasonBeltGames(): array
    {
        $currentSeason = $this->getCurrentSeasonYear();
        
        $stmt = $this->db->prepare('
            SELECT * FROM games 
            WHERE belt_involved = 1 
            AND season = ? 
            ORDER BY game_date DESC
        ');
        $stmt->execute([$currentSeason]);
        return $stmt->fetchAll();
    }

    /**
     * Get current season year
     *
     * @return int Current season start year
     */
    private function getCurrentSeasonYear(): int
    {
        $currentMonth = (int) date('n');
        $currentYear = (int) date('Y');

        // NBA season starts in October (10th month)
        // Off Season is July - September (7-9)

        if ($currentMonth >= 10) {
            return $currentYear;
        } else {
            // off season OR early months (Jan - June)
            // return the previous year
            return $currentYear - 1;
        }
    }
}