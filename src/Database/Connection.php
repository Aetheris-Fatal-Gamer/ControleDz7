<?php
namespace Dz7\Database;

use PDO;

abstract class Connection {

	private ?PDO $connection = null;
	private array $options = [
		\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
		\PDO::ATTR_PERSISTENT => false,
	];

	public function __destruct() {
		$this->connection = null;
	}

	private function driver(): PDO {
		if ($this->connection != null) {
			return $this->connection;
		}
		try {
			$dsn = 'mysql:host=' . $_ENV['MYSQL_HOST'] . ';dbname=' . $_ENV['MYSQL_DATABASE'] . ';charset=utf8mb4';	
			$this->connection = new \PDO($dsn, $_ENV['MYSQL_USER'], $_ENV['MYSQL_PASSWORD'], $this->options);
		} catch (\PDOException $e) {
			die($e->getMessage());
		}
		return $this->connection;
	}

	protected function conn(): PDO {
		return self::driver();
	}
}