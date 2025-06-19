<?php
// imdb_clone/models/Movie.php
namespace App\Models;

use PDO;
use PDOException;

class Movie extends BaseModel
{
    protected string $table = 'movies'; 

    public function getRecent(int $limit = 5): array
    {
        try {
            $stmt = $this->pdo->query("SELECT id, title, release_date FROM {$this->table} ORDER BY release_date DESC LIMIT {$limit}");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching recent movies in Movie model: " . $e->getMessage());
            return [];
        }
    }
}