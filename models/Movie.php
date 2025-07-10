<?php
namespace App\Models;

use PDO;
use PDOException;

class Movie extends BaseModel
{
    protected string $table = 'movies';

    public function __construct(PDO $pdo)
    {
        parent::__construct($pdo);
    }

    public function getRecent(int $limit = 5)
    {
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM {$this->table} ORDER BY release_date DESC LIMIT :limit");
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching recent movies: " . $e->getMessage());
            return [];
        }
    }

    public function getAllWithDetails()
    {
        try {
            $sql = "SELECT
                        m.id,
                        m.title,
                        m.release_date,
                        m.description,
                        m.runtime,
                        m.poster_path,
                        p.name AS director_name,
                        GROUP_CONCAT(DISTINCT g.name SEPARATOR ', ') AS genres
                    FROM
                        movies m
                    LEFT JOIN
                        people p ON m.director_id = p.id
                    LEFT JOIN
                        movie_genres mg ON m.id = mg.movie_id
                    LEFT JOIN
                        genres g ON mg.genre_id = g.id
                    GROUP BY
                        m.id
                    ORDER BY
                        m.title";
            $stmt = $this->pdo->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching all movies with details: " . $e->getMessage());
            return [];
        }
    }

    public function getByIdWithDetails(int $id)
    {
        try {
            $sql = "SELECT
                        m.id,
                        m.title,
                        m.release_date,
                        m.description,
                        m.runtime,
                        m.poster_path,
                        p.name AS director_name,
                        GROUP_CONCAT(DISTINCT g.name SEPARATOR ', ') AS genres
                    FROM
                        movies m
                    LEFT JOIN
                        people p ON m.director_id = p.id
                    LEFT JOIN
                        movie_genres mg ON m.id = mg.movie_id
                    LEFT JOIN
                        genres g ON mg.genre_id = g.id
                    WHERE
                        m.id = :id
                    GROUP BY
                        m.id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':id' => $id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching movie by ID with details: " . $e->getMessage());
            return false;
        }
    }
}