<?php
// imdb_clone/models/User.php
namespace App\Models; 

use PDO; 
use PDOException;

class User extends BaseModel
{
    protected string $table = 'users'; 

    public function findByUsername(string $username)
    {
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM {$this->table} WHERE username = :username LIMIT 1");
            $stmt->execute([':username' => $username]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error in User::findByUsername: " . $e->getMessage());
            return false;
        }
    }
}