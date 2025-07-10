<?php
namespace App\Models;

use PDO;
use PDOException;

class User extends BaseModel
{
    protected string $table = 'users';

    public function __construct(PDO $pdo)
    {
        parent::__construct($pdo);
    }

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

    public function hashPassword(string $password, int $cost = 10): string
    {
        $options = [
            'cost' => $cost
        ];
        return password_hash($password, PASSWORD_BCRYPT, $options);
    }

    public function checkUser(string $username, string $password): bool
    {
        $user = $this->findByUsername($username);

        if ($user) {
            if (password_verify($password, $user['password'])) {
                session_regenerate_id(true);
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['privilege_id'] = $user['privilege_id'];
                $_SESSION['fingerPrint'] = md5($_SERVER['HTTP_USER_AGENT'] . $_SERVER['REMOTE_ADDR']);
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }
}