<?php
// classes/Movie.php

class Movie {
    private $pdo; // Stores the PDO database connection

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Creates a new movie record in the database.
     * @param string $title
     * @param string $release_date
     * @param string $description
     * @param int $runtime
     * @param int|null $director_id ID of the director from the people table, can be null.
     * @return bool True on success, false on failure.
     */
    public function create($title, $release_date, $description, $runtime, $director_id) {
        $stmt = $this->pdo->prepare("INSERT INTO movies (title, release_date, description, runtime, director_id) VALUES (?, ?, ?, ?, ?)");
        // Using htmlspecialchars and strip_tags for basic sanitation for text inputs
        $title = htmlspecialchars(strip_tags($title));
        $release_date = htmlspecialchars(strip_tags($release_date));
        $description = htmlspecialchars(strip_tags($description));
        $runtime = htmlspecialchars(strip_tags($runtime));
        $director_id = htmlspecialchars(strip_tags($director_id)); // Sanitize, but ensure it's treated as int later

        return $stmt->execute([$title, $release_date, $description, $runtime, $director_id]);
    }

    /**
     * Retrieves all movies from the database.
     * Includes director's name by joining the people table.
     * @return array An array of movie records.
     */
    public function getAll() {
        $stmt = $this->pdo->query("SELECT m.*, p.name AS director_name FROM movies m LEFT JOIN people p ON m.director_id = p.id ORDER BY m.title ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Retrieves a single movie record by its ID.
     * Includes director's name and ID.
     * @param int $id The ID of the movie to retrieve.
     * @return array|false The movie record as an associative array, or false if not found.
     */
    public function getById($id) {
        $stmt = $this->pdo->prepare("SELECT m.*, p.name AS director_name FROM movies m LEFT JOIN people p ON m.director_id = p.id WHERE m.id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Updates an existing movie record in the database.
     * @param int $id The ID of the movie to update.
     * @param string $title
     * @param string $release_date
     * @param string $description
     * @param int $runtime
     * @param int|null $director_id
     * @return bool True on success, false on failure.
     */
    public function update($id, $title, $release_date, $description, $runtime, $director_id) {
        $stmt = $this->pdo->prepare("UPDATE movies SET title = ?, release_date = ?, description = ?, runtime = ?, director_id = ? WHERE id = ?");
        // Sanitize data
        $title = htmlspecialchars(strip_tags($title));
        $release_date = htmlspecialchars(strip_tags($release_date));
        $description = htmlspecialchars(strip_tags($description));
        $runtime = htmlspecialchars(strip_tags($runtime));
        $director_id = htmlspecialchars(strip_tags($director_id));
        $id = htmlspecialchars(strip_tags($id)); // Ensure ID is sanitized too

        return $stmt->execute([$title, $release_date, $description, $runtime, $director_id, $id]);
    }

    /**
     * Deletes a movie record from the database.
     * Note: Due to ON DELETE CASCADE on movie_genres, associated genre entries will also be deleted.
     * @param int $id The ID of the movie to delete.
     * @return bool True on success, false on failure.
     */
    public function delete($id) {
        $stmt = $this->pdo->prepare("DELETE FROM movies WHERE id = ?");
        return $stmt->execute([$id]);
    }

    // --- Relationship Methods (Movie-Genre) ---

    /**
     * Gets all genres associated with a specific movie.
     * @param int $movieId The ID of the movie.
     * @return array An array of genre records.
     */
    public function getMovieGenres($movieId) {
        $stmt = $this->pdo->prepare("SELECT g.id, g.name FROM genres g JOIN movie_genres mg ON g.id = mg.genre_id WHERE mg.movie_id = ?");
        $stmt->execute([$movieId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Adds a single genre to a movie.
     * Checks for duplicates before inserting.
     * @param int $movieId
     * @param int $genreId
     * @return bool True on success, false if already exists or fails.
     */
    private function addMovieGenre($movieId, $genreId) {
        // Prevent duplicate entries
        $checkStmt = $this->pdo->prepare("SELECT COUNT(*) FROM movie_genres WHERE movie_id = ? AND genre_id = ?");
        $checkStmt->execute([$movieId, $genreId]);
        if ($checkStmt->fetchColumn() > 0) {
            return false; // Already exists
        }

        $stmt = $this->pdo->prepare("INSERT INTO movie_genres (movie_id, genre_id) VALUES (?, ?)");
        return $stmt->execute([$movieId, $genreId]);
    }

    /**
     * Updates all genres for a movie by first removing all existing associations
     * and then adding the new ones.
     * This is useful for checkboxes where you send all selected genres.
     * @param int $movieId The ID of the movie.
     * @param array $genreIds An array of genre IDs to associate with the movie.
     * @return bool True on success, false on failure (database transaction rolls back).
     */
    public function updateMovieGenres($movieId, array $genreIds) {
        try {
            $this->pdo->beginTransaction(); // Start a transaction

            // Delete all existing genres for this movie
            $deleteStmt = $this->pdo->prepare("DELETE FROM movie_genres WHERE movie_id = ?");
            $deleteStmt->execute([$movieId]);

            // Add new genres
            foreach ($genreIds as $genreId) {
                // We don't need to check for duplicates here because we just deleted everything.
                // Just try to insert, if it fails, the transaction will rollback.
                $insertStmt = $this->pdo->prepare("INSERT INTO movie_genres (movie_id, genre_id) VALUES (?, ?)");
                if (!$insertStmt->execute([$movieId, $genreId])) {
                    throw new Exception("Failed to insert genre ID: " . $genreId);
                }
            }

            $this->pdo->commit(); // Commit the transaction
            return true;
        } catch (Exception $e) {
            $this->pdo->rollBack(); // Rollback on any error
            error_log("Error updating movie genres for movie ID " . $movieId . ": " . $e->getMessage());
            return false;
        }
    }
}