<?php
namespace Dz7\Database;

use PDO;
use PDOStatement;

class Create extends Connection {

	private PDO $connection;
	private PDOStatement $statement;

	private string $table;
	private array $data;
	private string $sql;
	private mixed $result;

	public function getResult(): mixed {
		return $this->result;
	}

	public function run(string $table, array $data): self {
		$this->table = $table;
		$this->data = $data;

		$this->syntax();
		$this->handle();
		return $this;
	}

	private function prepare(): void {
		$this->connection = parent::conn();
		$this->statement = $this->connection->prepare($this->sql);
	}

	private function syntax(): void {
		$fields = implode(', ', array_keys($this->data));
		$values = ':' . implode(', :', array_keys($this->data));
		$this->sql = 'INSERT INTO ' . $this->table . ' (' . $fields . ') VALUES (' . $values . ')';
	}

	private function handle(): void {
		$this->prepare();
		$this->connection->beginTransaction();
		try {
			$this->statement->execute($this->data);
			$this->result = $this->connection->lastInsertId();
			$this->connection->commit();
		} catch (\PDOException $e) {
			$this->result = null;
			var_dump($e);
			$this->connection->rollBack();
		}
	}
}
