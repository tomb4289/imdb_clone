<?php
// classes/Person.php

class Person {
    private $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Creates a new person record.
     * @param string $name The name of the person.
     * @param int|null $birth_year The birth year of the person, can be null.
     * @return bool True on success, false on failure.
     */
    public function create($name, $birth_year) {
        $stmt = $this->pdo->prepare("INSERT INTO people (name, birth_year) VALUES (?, ?)");
        $name = htmlspecialchars(strip_tags($name));
        $birth_year = htmlspecialchars(strip_tags($birth_year)); // Sanitize
        return $stmt->execute([$name, $birth_year]);
    }

    /**
     * Retrieves all person records.
     * @return array An array of person records.
     */
    public function getAll() {
        $stmt = $this->pdo->query("SELECT id, name, birth_year FROM people ORDER BY name ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Retrieves a single person record by their ID.
     * @param int $id The ID of the person.
     * @return array|false The person record as an associative array, or false if not found.
     */
    public function getById($id) {
        $stmt = $this->pdo->prepare("SELECT id, name, birth_year FROM people WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Updates an existing person record.
     * @param int $id The ID of the person to update.
     * @param string $name The new name for the person.
     * @param int|null $birth_year The new birth year for the person.
     * @return bool True on success, false on failure.
     */
    public function update($id, $name, $birth_year) {
        $stmt = $this->pdo->prepare("UPDATE people SET name = ?, birth_year = ? WHERE id = ?");
        $name = htmlspecialchars(strip_tags($name));
        $birth_year = htmlspecialchars(strip_tags($birth_year));
        $id = htmlspecialchars(strip_tags($id));
        return $stmt->execute([$name, $birth_year, $id]);
    }

    /**
     * Deletes a person record.
     * Note: Due to ON DELETE SET NULL on the movies.director_id foreign key,
     * any movies directed by this person will have their director_id set to NULL,
     * rather than the movies themselves being deleted.
     * @param int $id The ID of the person to delete.
     * @return bool True on success, false on failure.
     */
    public function delete($id) {
        $stmt = $this->pdo->prepare("DELETE FROM people WHERE id = ?");
        return $stmt->execute([$id]);
    }
}