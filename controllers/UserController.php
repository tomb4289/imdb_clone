<?php
// imdb_clone/controllers/UserController.php
namespace App\Controllers;

use PDO;
use Twig\Environment;
use App\Models\User;

class UserController
{
    protected PDO $pdo;
    protected Environment $twig;
    protected User $userModel;

    public function __construct(PDO $pdo, Environment $twig)
    {
        $this->pdo = $pdo;
        $this->twig = $twig;
        $this->userModel = new User($this->pdo);
    }


    public function index()
    {
        $users = $this->userModel->all(); 
        echo $this->twig->render('users/index.html.twig', [
            'users' => $users,
            'base_url' => BASE
        ]);
    }


    public function show($queryParams = [])
    {
        $userId = $queryParams['id'] ?? null;

        if (empty($userId)) {
            http_response_code(404);
            echo $this->twig->render('error/404.html.twig', ['base_url' => BASE]);
            return;
        }

        try {
            $user = $this->userModel->find($userId); 

            if (!$user) {
                http_response_code(404);
                echo $this->twig->render('error/404.html.twig', ['base_url' => BASE]);
                return;
            }

            echo $this->twig->render('users/show.html.twig', ['user' => $user, 'base_url' => BASE]);

        } catch (\PDOException $e) {
            error_log("Database Error in UserController::show: " . $e->getMessage());
            http_response_code(500);
            echo $this->twig->render('error/500.html.twig', ['base_url' => BASE]);
            return;
        }
    }


    public function create()
    {
        echo $this->twig->render('users/create.html.twig', ['base_url' => BASE]);
    }


    public function store($postData)
    {
        try {
            $username = $postData['username'] ?? null;
            $email = $postData['email'] ?? null;
            $password = $postData['password'] ?? null;

            if (!$username || !$email || !$password) {
                http_response_code(400);
                echo $this->twig->render('error/400.html.twig', ['message' => 'Username, email, and password are required for user creation.', 'base_url' => BASE]);
                return;
            }

            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            $data = [
                'username' => $username,
                'email' => $email,
                'password_hash' => $hashedPassword,
                'created_at' => date('Y-m-d H:i:s') 
            ];

            if ($this->userModel->create($data)) {
                header('Location: ' . BASE . '/users');
                exit();
            } else {
                error_log("Error creating user via BaseModel::create.");
                http_response_code(500);
                echo $this->twig->render('error/500.html.twig', ['base_url' => BASE, 'message' => 'Failed to create user.']);
            }

        } catch (\PDOException $e) {
            error_log("Database Error in UserController::store: " . $e->getMessage());
            http_response_code(500);
            echo $this->twig->render('error/500.html.twig', ['base_url' => BASE]);
            return;
        }
    }


    public function edit($queryParams = [])
    {
        $userId = $queryParams['id'] ?? null;

        if (empty($userId)) {
            http_response_code(404);
            echo $this->twig->render('error/404.html.twig', ['base_url' => BASE]);
            return;
        }

        try {
            $user = $this->userModel->find($userId); 

            if (!$user) {
                http_response_code(404);
                echo $this->twig->render('error/404.html.twig', ['base_url' => BASE]);
                return;
            }

            echo $this->twig->render('users/edit.html.twig', [
                'user' => $user,
                'base_url' => BASE
            ]);

        } catch (\PDOException $e) {
            error_log("Database Error in UserController::edit: " . $e->getMessage());
            http_response_code(500);
            echo $this->twig->render('error/500.html.twig', ['base_url' => BASE]);
            return;
        }
    }

    public function update($postData)
    {
        $userId = $postData['id'] ?? null;
        $username = $postData['username'] ?? null;
        $email = $postData['email'] ?? null;
        $password = $postData['password'] ?? null;

        if (empty($userId) || !$username || !$email) {
            http_response_code(400);
            echo $this->twig->render('error/400.html.twig', ['message' => 'User ID, username, and email are required for update.', 'base_url' => BASE]);
            return;
        }

        try {
            $data = [
                'username' => $username,
                'email' => $email,
            ];

            if (!empty($password)) {
                $data['password_hash'] = password_hash($password, PASSWORD_DEFAULT);
            }

            if ($this->userModel->update($userId, $data)) {
                header('Location: ' . BASE . '/users/show?id=' . $userId);
                exit();
            } else {
                error_log("Error updating user via BaseModel::update.");
                http_response_code(500);
                echo $this->twig->render('error/500.html.twig', ['base_url' => BASE, 'message' => 'Failed to update user.']);
            }

        } catch (\PDOException $e) {
            error_log("Database Error in UserController::update: " . $e->getMessage());
            http_response_code(500);
            echo $this->twig->render('error/500.html.twig', ['base_url' => BASE]);
            return;
        }
    }

    public function delete($postData)
    {
        $userId = $postData['id'] ?? null;

        if (empty($userId)) {
            http_response_code(400);
            echo $this->twig->render('error/400.html.twig', ['message' => 'User ID is required for deletion.', 'base_url' => BASE]);
            return;
        }

        try {
            if ($this->userModel->delete($userId)) {
                header('Location: ' . BASE . '/users');
                exit();
            } else {
                error_log("Error deleting user via BaseModel::delete.");
                http_response_code(500);
                echo $this->twig->render('error/500.html.twig', ['base_url' => BASE, 'message' => 'Failed to delete user.']);
            }
        } catch (\PDOException $e) {
            error_log("Database Error in UserController::delete: " . $e->getMessage());
            http_response_code(500);
            echo $this->twig->render('error/500.html.twig', ['base_url' => BASE]);
            return;
        }
    }
}