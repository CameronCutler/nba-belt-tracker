<?php

namespace NbaBelt\Repositories;

use NbaBelt\Database\Connection;
use PDO;

class TeamRepository
{
    private PDO $db;

    public function __construct()
    {
        $dbPath = $_ENV['DB_PATH'] ?? __DIR__ . '/../../database/belt.db';
        $this->db = Connection::getInstance($dbPath);
    }

    /**
     * Get all teams from database
     * 
     * @return array
     */
    public function getAll(): array
    {
        $stmt = $this->db->query('SELECT * FROM teams ORDER BY full_name');
        return $stmt->fetchAll();
    }

    /**
     * Find a team by ID
     * 
     * @param int $id
     * @return array|null
     */
    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM teams WHERE id = ?');
        $stmt->execute([$id]);
        $result = $stmt->fetch();
        
        return $result ?: null;
    }

    /**
     * Find a team by abbreviation
     * 
     * @param string $abbreviation
     * @return array|null
     */
    public function findByAbbreviation(string $abbreviation): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM teams WHERE abbreviation = ?');
        $stmt->execute([$abbreviation]);
        $result = $stmt->fetch();
        
        return $result ?: null;
    }

    /**
     * Create a new team
     * 
     * @param array $data Team data
     * @return bool
     */
    public function create(array $data): bool
    {
        $sql = 'INSERT INTO teams (id, name, full_name, abbreviation, city, conference, division) 
                VALUES (:id, :name, :full_name, :abbreviation, :city, :conference, :division)';
        
        $stmt = $this->db->prepare($sql);
        
        return $stmt->execute([
            ':id' => $data['id'],
            ':name' => $data['name'],
            ':full_name' => $data['full_name'],
            ':abbreviation' => $data['abbreviation'],
            ':city' => $data['city'],
            ':conference' => $data['conference'],
            ':division' => $data['division'],
        ]);
    }

    /**
     * Update an existing team
     * 
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function update(int $id, array $data): bool
    {
        $sql = 'UPDATE teams 
                SET name = :name,
                    full_name = :full_name,
                    abbreviation = :abbreviation,
                    city = :city,
                    conference = :conference,
                    division = :division,
                    updated_at = datetime("now")
                WHERE id = :id';
        
        $stmt = $this->db->prepare($sql);
        
        return $stmt->execute([
            ':id' => $id,
            ':name' => $data['name'],
            ':full_name' => $data['full_name'],
            ':abbreviation' => $data['abbreviation'],
            ':city' => $data['city'],
            ':conference' => $data['conference'],
            ':division' => $data['division'],
        ]);
    }

    /**
     * Delete a team
     * 
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM teams WHERE id = ?');
        return $stmt->execute([$id]);
    }

    /**
     * Get teams by conference
     * 
     * @param string $conference
     * @return array
     */
    public function getByConference(string $conference): array
    {
        $stmt = $this->db->prepare('SELECT * FROM teams WHERE conference = ? ORDER BY full_name');
        $stmt->execute([$conference]);
        return $stmt->fetchAll();
    }

    /**
     * Check if teams table is empty
     * 
     * @return bool
     */
    public function isEmpty(): bool
    {
        $stmt = $this->db->query('SELECT COUNT(*) as count FROM teams');
        $result = $stmt->fetch();
        return $result['count'] === 0;
    }

    /**
     * Bulk insert teams (efficient for seeding)
     * 
     * @param array $teams Array of team data
     * @return bool
     */
    public function bulkInsert(array $teams): bool
    {
        $this->db->beginTransaction();
        
        try {
            $sql = 'INSERT OR REPLACE INTO teams (id, name, full_name, abbreviation, city, conference, division) 
                    VALUES (:id, :name, :full_name, :abbreviation, :city, :conference, :division)';
            
            $stmt = $this->db->prepare($sql);
            
            foreach ($teams as $team) {
                $stmt->execute([
                    ':id' => $team['id'],
                    ':name' => $team['name'],
                    ':full_name' => $team['full_name'],
                    ':abbreviation' => $team['abbreviation'],
                    ':city' => $team['city'],
                    ':conference' => $team['conference'],
                    ':division' => $team['division'],
                ]);
            }
            
            $this->db->commit();
            return true;
            
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
}