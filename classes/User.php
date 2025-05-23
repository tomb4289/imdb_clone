<?php
// classes/User.php

class User {
    private $pdo;
    private $table_name = "users";

    public $id;
    public $username;
    public $email;
    public $password_hash;
    public $created_at;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    // CREATE User
    public function create($username, $email, $password_hash) {
        $query = "INSERT INTO " . $this->table_name . "
                SET
                    username=:username,
                    email=:email,
                    password_hash=:password_hash";

        $stmt = $this->pdo->prepare($query);

        // Sanitize data
        $this->username = htmlspecialchars(strip_tags($username));
        $this->email = htmlspecialchars(strip_tags($email));
        $this->password_hash = $password_hash; // Hashing should be done before passing to this method

        // Bind values
        $stmt->bindParam(":username", $this->username);
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":password_hash", $this->password_hash);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    // READ (Get all users)
    public function getAll() {
        $query = "SELECT id, username, email, created_at FROM " . $this->table_name . " ORDER BY username ASC";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // READ (Get single user by ID)
    public function getById($id) {
        $query = "SELECT id, username, email, created_at FROM " . $this->table_name . " WHERE id = ? LIMIT 0,1";
        $stmt = $this->pdo->prepare($query);
        $stmt->bindParam(1, $id);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // UPDATE User
    public function update($id, $username, $email, $password_hash = null) {
        $query = "UPDATE " . $this->table_name . "
                SET
                    username=:username,
                    email=:email
                    " . ($password_hash ? ", password_hash=:password_hash" : "") . "
                WHERE
                    id = :id";

        $stmt = $this->pdo->prepare($query);

        // Sanitize data
        $this->username = htmlspecialchars(strip_tags($username));
        $this->email = htmlspecialchars(strip_tags($email));
        $this->id = htmlspecialchars(strip_tags($id));

        // Bind values
        $stmt->bindParam(':username', $this->username);
        $stmt->bindParam(':email', $this->email);
        $stmt->bindParam(':id', $this->id);
        if ($password_hash) {
            $stmt->bindParam(':password_hash', $password_hash);
        }

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    // DELETE User
    public function delete($id) {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = ?";
        $stmt = $this->pdo->prepare($query);
        $this->id = htmlspecialchars(strip_tags($id));
        $stmt->bindParam(1, $this->id);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }
}
?>