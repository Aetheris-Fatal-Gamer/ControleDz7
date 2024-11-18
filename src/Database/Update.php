<?php
namespace Dz7\Database;

use \PDO;
use \PDOStatement;

class Update extends Connection {
	
	private PDO $connection;
	private PDOStatement $statement;

	private string $table;
	private string $query;
	private string $where;
	private array $data;
	private array $places;
	private mixed $result;

	public function getResult(): mixed {
		return $this->result;
	}

	public function getNumberRows(): int {
		return $this->statement->rowCount();
	}

	public function run(string $table, array $data, string $where, array $places): self {
		$this->table = $table;
		$this->data = $data;
		$this->where = $where;
		$this->places = $places;

		$this->syntax();
		$this->handle();
		return $this;
	}

	private function syntax(): void {
		$dataPlaces = [];
		foreach ($this->data as $key => $value) {
			$dataPlaces[] = $key . ' = :' . $key;
		}
		$dataPlaces = implode(', ', $dataPlaces);
		$this->query = 'UPDATE ' . $this->table . ' SET ' . $dataPlaces . ' ' . $this->where;
	}

	private function prepare(): void {
		$this->connection = parent::conn();
		$this->statement = $this->connection->prepare($this->query);
	}

	private function handle(): void {
		$this->prepare();
		$this->connection->beginTransaction();
		try {
			$this->statement->execute(array_merge($this->data, $this->places));
			$this->result = true;
			$this->connection->commit();
		} catch (\PDOException $e) {
			$this->result = null;
			$this->connection->rollBack();
		}
	}
}
