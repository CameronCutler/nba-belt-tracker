<?php

namespace NbaBelt\Repositories;

use NbaBelt\Database\Connection;
use PDO;

class BeltHistoryRepository
{
    private PDO $db;

    public function __construct()
    {
        $dbPath = $_ENV['DB_PATH'] ?? __DIR__ . '/../../database/belt.db';
        $this->db = Connection::getInstance($dbPath);
    }

    /**
     * Get the current belt holder
     * 
     * @return array|null
     */
    public function getCurrentBeltHolder(): ?array
    {
        $sql = "SELECT bh.*, t.full_name, t.abbreviation AS team_name
                FROM belt_history bh
                JOIN teams t ON bh.team_id = t.id
                WHERE bh.lost_date IS NULL
                ORDER BY bh.acquired_date DESC
                LIMIT 1";
        $stmt = $this->db->query($sql);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    /**
     * Get full belt history
     * 
     * @return array|null
     */
    public function getBeltHistory(?string $limit): ?array
    {
        $sql = "SELECT bh.*, t.full_name, t.abbreviation AS team_name
                FROM belt_history bh
                JOIN teams t ON bh.team_id = t.id
                ORDER BY bh.acquired_date DESC";

        if ($limit) {
            $sql .= " LIMIT " . (int)$limit;
        }
        
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    /**
     * Enrich an array of API games with belt context.
     * Adds `belt_on_the_line` (bool) and `belt_holder_team_id` (int|null) to each game.
     *
     * @param array $games Games from the Ball Don't Lie API
     * @return array
     */
    public function enrichGamesWithBeltContext(array $games): array
    {
        $holder = $this->getCurrentBeltHolder();
        $holderTeamId = $holder ? (int) $holder['team_id'] : null;

        return array_map(function (array $game) use ($holderTeamId) {
            $homeId = (int) ($game['home_team']['id'] ?? 0);
            $awayId = (int) ($game['visitor_team']['id'] ?? 0);

            $beltOnTheLine = $holderTeamId !== null
                && ($homeId === $holderTeamId || $awayId === $holderTeamId);

            $game['belt_on_the_line'] = $beltOnTheLine;
            $game['belt_holder_team_id'] = $holderTeamId;

            return $game;
        }, $games);
    }
}