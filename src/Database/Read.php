<?php
namespace Dz7\Database;

use \PDO;
use \PDOStatement;

class Read extends Connection {

	private PDO $connection;
	private PDOStatement $statement;

	private string $query;
	private array $places;
	private mixed $result;

	public function getResult(): mixed {
		return $this->result;
	}

	public function getNumberRows(): int {
		return $this->statement->rowCount();
	}

	public function run(string $query, array $places = []): self {
		$this->query = $query;
		$this->places = $places;
		$this->handle();
		return $this;
	}

	private function syntax(): void {
		if (empty($this->places)) {
			return;
		}
		foreach ($this->places as $key => $value) {
			if ($key == 'limit' OR $key == 'offset') {
				$value = (int) $value;
			}
			$this->statement->bindValue(':' . $key, $value, (is_int($value) ? \PDO::PARAM_INT : \PDO::PARAM_STR));
		}
	}

	private function prepare(): void {
		$this->connection = parent::conn();
		$this->statement = $this->connection->prepare($this->query);
		$this->statement->setFetchMode(\PDO::FETCH_OBJ);
	}

	private function handle(): void {
		$this->prepare();
		try {
			$this->syntax();
			$this->statement->execute();
			$this->result = $this->statement->fetchAll();
		} catch (\PDOException $e) {
			$this->result = null;
		}
	}
}
