<?php

namespace NbaBelt\Database;

use PDO;
use PDOException;

class Connection
{
	private static ?PDO $instance = null;
	private string $dbPath;

	/**
	 * Private constructor. Prevents direct accesss
	 * @param string $dbPath
	 */
	private function __construct(string $dbPath)
	{
		$this->dbPath = $dbPath;
	}

	/**
	 * Get singleton PDO instance
	 * @param string|null $dbPath
	 * @return PDO
	 */
	public static function getInstance(?string $dbPath = null) : PDO
	{
		if (self::$instance === null) {
			if ($dbPath === null) {  // ← Check if dbPath is null, not instance!
				throw new \InvalidArgumentException('Database path must be provided on first call');
			}

			try {
				// Ensure database directory exists
				$dir = dirname($dbPath);
				if (!is_dir($dir)) {
					mkdir($dir, 0755, true);
				}

				// Create PDO connection
				self::$instance = new PDO(
					'sqlite:' . $dbPath,
					null,
					null,
					[
						PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
						PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
						PDO::ATTR_EMULATE_PREPARES => false,
					]
				);

				// Enable foreign keys
				self::$instance->exec('PRAGMA foreign_keys = ON');

			} catch (PDOException $e) {
				throw new PDOException('Database connection failed: ' . $e->getMessage());
			}
		}

		return self::$instance;
	}

	/**
	 * Run database migrations
	 * @param string $migrationsPath
	 * @return void
	 */
	public static function migrate(string $migrationsPath): void
	{
		$pdo = self::getInstance();

		// get all .sql files in migrations directory
		$migrations = glob($migrationsPath . '/*.sql');
		sort($migrations);

		foreach ($migrations as $migration) {
			echo "Running migration: " . basename($migration) . "\n";

			$sql = file_get_contents($migration);

			try {
				$pdo->exec($sql);
				echo "✓ Migration completed: " . basename($migration) . "\n";
			} catch (PDOException $e) {
				echo "✘ Migration failed: " . $e->getMessage() . "\n";
				throw $e;
			}
		}

		echo "All migrations completed successfully! \n";
	}

	public static function close(): void
	{
		self::$instance = null;
	}

	public static function beginTransaction(): bool
	{
		return self::getInstance()->beginTransaction();
	}

	public static function commit(): bool
	{
		return self::getInstance()->commit();
	}

	public static function rollback(): bool
	{
		return self::getInstance()->rollBack();
	}
}