<?php
namespace App\Models;

use PDO;
use PDOException;

abstract class BaseModel
{
    protected PDO $pdo;
    protected string $table; 

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }


    public function find(int $id)
    {
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM {$this->table} WHERE id = ?");
            $stmt->execute([$id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error in BaseModel::find for table {$this->table}: " . $e->getMessage());
            return false;
        }
    }


    public function all(): array
    {
        try {
            $stmt = $this->pdo->query("SELECT * FROM {$this->table}");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error in BaseModel::all for table {$this->table}: " . $e->getMessage());
            return [];
        }
    }

    public function create(array $data): bool
    {
        if (empty($data)) {
            return false;
        }

        $columns = implode(', ', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));

        $sql = "INSERT INTO {$this->table} ({$columns}) VALUES ({$placeholders})";

        try {
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute($data);
        } catch (PDOException $e) {
            error_log("Error in BaseModel::create for table {$this->table}: " . $e->getMessage());
            return false;
        }
    }


    public function update(int $id, array $data): bool
    {
        if (empty($data)) {
            return false;
        }

        $setParts = [];
        foreach ($data as $key => $value) {
            $setParts[] = "{$key} = :{$key}";
        }
        $setClause = implode(', ', $setParts);

        $sql = "UPDATE {$this->table} SET {$setClause} WHERE id = :id";
        $data['id'] = $id; 

        try {
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute($data);
        } catch (PDOException $e) {
            error_log("Error in BaseModel::update for table {$this->table}: " . $e->getMessage());
            return false;
        }
    }


    public function delete(int $id): bool
    {
        $sql = "DELETE FROM {$this->table} WHERE id = :id";

        try {
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute(['id' => $id]);
        } catch (PDOException $e) {
            error_log("Error in BaseModel::delete for table {$this->table}: " . $e->getMessage());
            return false;
        }
    }
}