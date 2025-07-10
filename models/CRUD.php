<?php
namespace App\Models;

use PDO;
use PDOException;

class CRUD
{
    protected PDO $pdo;
    protected string $table;
    protected string $primaryKey = 'id';
    protected array $fillable = [];

    public function __construct(PDO $pdo, string $table, string $primaryKey = 'id', array $fillable = [])
    {
        $this->pdo = $pdo;
        $this->table = $table;
        $this->primaryKey = $primaryKey;
        $this->fillable = $fillable;
    }

    public function all()
    {
        try {
            $stmt = $this->pdo->query("SELECT * FROM {$this->table}");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error in CRUD::all for {$this->table}: " . $e->getMessage());
            return [];
        }
    }

    public function get($id)
    {
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM {$this->table} WHERE {$this->primaryKey} = :id");
            $stmt->execute([':id' => $id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error in CRUD::get for {$this->table}: " . $e->getMessage());
            return false;
        }
    }

    public function unique(string $column, $value)
    {
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM {$this->table} WHERE {$column} = :value LIMIT 1");
            $stmt->execute([':value' => $value]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error in CRUD::unique for {$this->table} on {$column}: " . $e->getMessage());
            return false;
        }
    }

    public function insert(array $data)
    {
        $filteredData = array_intersect_key($data, array_flip($this->fillable));
        if (empty($filteredData)) {
            error_log("Error: No fillable data provided for insert into {$this->table}.");
            return false;
        }

        $fields = implode(', ', array_keys($filteredData));
        $placeholders = ':' . implode(', :', array_keys($filteredData));

        try {
            $stmt = $this->pdo->prepare("INSERT INTO {$this->table} ({$fields}) VALUES ({$placeholders})");
            foreach ($filteredData as $key => $value) {
                $stmt->bindValue(':' . $key, $value);
            }
            $stmt->execute();
            return $this->pdo->lastInsertId();
        } catch (PDOException $e) {
            error_log("Error in CRUD::insert for {$this->table}: " . $e->getMessage());
            return false;
        }
    }

    public function updateRecord(int $id, array $data)
    {
        $filteredData = array_intersect_key($data, array_flip($this->fillable));
        if (empty($filteredData)) {
            error_log("Error: No fillable data provided for update in {$this->table}.");
            return false;
        }

        $setClauses = [];
        foreach ($filteredData as $key => $value) {
            $setClauses[] = "{$key} = :{$key}";
        }
        $setClause = implode(', ', $setClauses);
        $filteredData['id'] = $id;

        try {
            $stmt = $this->pdo->prepare("UPDATE {$this->table} SET {$setClause} WHERE {$this->primaryKey} = :id");
            foreach ($filteredData as $key => $value) {
                $stmt->bindValue(':' . $key, $value);
            }
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error in CRUD::updateRecord for {$this->table}: " . $e->getMessage());
            return false;
        }
    }

    public function deleteRecord(int $id)
    {
        try {
            $stmt = $this->pdo->prepare("DELETE FROM {$this->table} WHERE {$this->primaryKey} = :id");
            return $stmt->execute([':id' => $id]);
        } catch (PDOException $e) {
            error_log("Error in CRUD::deleteRecord for {$this->table}: " . $e->getMessage());
            return false;
        }
    }
}