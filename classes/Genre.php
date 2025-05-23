<?php
// classes/Genre.php

class Genre {
    private $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Creates a new genre record.
     * @param string $name The name of the genre.
     * @return bool True on success, false on failure (e.g., if genre name already exists due to UNIQUE constraint).
     */
    public function create($name) {
        $stmt = $this->pdo->prepare("INSERT INTO genres (name) VALUES (?)");
        $name = htmlspecialchars(strip_tags($name)); // Sanitize
        return $stmt->execute([$name]);
    }

    /**
     * Retrieves all genre records.
     * @return array An array of genre records.
     */
    public function getAll() {
        $stmt = $this->pdo->query("SELECT id, name FROM genres ORDER BY name ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Retrieves a single genre record by its ID.
     * @param int $id The ID of the genre.
     * @return array|false The genre record as an associative array, or false if not found.
     */
    public function getById($id) {
        $stmt = $this->pdo->prepare("SELECT id, name FROM genres WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Updates an existing genre record.
     * @param int $id The ID of the genre to update.
     * @param string $name The new name for the genre.
     * @return bool True on success, false on failure.
     */
    public function update($id, $name) {
        $stmt = $this->pdo->prepare("UPDATE genres SET name = ? WHERE id = ?");
        $name = htmlspecialchars(strip_tags($name));
        $id = htmlspecialchars(strip_tags($id));
        return $stmt->execute([$name, $id]);
    }

    /**
     * Deletes a genre record.
     * Note: Due to ON DELETE CASCADE on movie_genres, any associated movie-genre entries will also be deleted.
     * @param int $id The ID of the genre to delete.
     * @return bool True on success, false on failure.
     */
    public function delete($id) {
        $stmt = $this->pdo->prepare("DELETE FROM genres WHERE id = ?");
        return $stmt->execute([$id]);
    }
}