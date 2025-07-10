<?php
namespace App\Controllers;

use PDO;
use Twig\Environment;
use App\Models\User;
use App\Models\ActivityLog;
use App\Providers\View;
use App\Providers\Validator;
use App\Providers\Auth;

class AuthController extends BaseController
{
    protected User $userModel;
    protected ActivityLog $activityLogModel;

    public function __construct(PDO $pdo, Environment $twig, array $config)
    {
        parent::__construct($pdo, $twig, $config);
        $this->userModel = new User($pdo);
        $this->activityLogModel = new ActivityLog($pdo);
    }

    public function login()
    {
        echo $this->twig->render('auth/login.html.twig', ['errors' => []]);
    }

    public function authenticate(array $postData)
    {
        $validator = new Validator;
        $validator->field('username', $postData['username'])->required()->min(2);
        $validator->field('password', $postData['password'])->required()->min(6);

        if ($validator->isSuccess()) {
            $username = $postData['username'];
            $password = $postData['password'];

            $checkUser = $this->userModel->checkUser($username, $password);

            if ($checkUser) {
                $this->activityLogModel->logActivity(
                    $_SESSION['user_id'] ?? null,
                    $_SESSION['user_name'] ?? null,
                    $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN',
                    $_SERVER['REQUEST_URI'] ?? 'UNKNOWN',
                    $_SERVER['REQUEST_METHOD'] ?? 'UNKNOWN',
                    $_SERVER['HTTP_USER_AGENT'] ?? null
                );
                View::redirect('home');
            } else {
                $errors['message'] = "Invalid username or password.";
                echo $this->twig->render('auth/login.html.twig', ['errors' => $errors, 'old' => $postData]);
            }
        } else {
            $errors = $validator->getErrors();
            echo $this->twig->render('auth/login.html.twig', ['errors' => $errors, 'old' => $postData]);
        }
    }

    public function logout()
    {
        if (Auth::check()) {
            $this->activityLogModel->logActivity(
                $_SESSION['user_id'] ?? null,
                $_SESSION['user_name'] ?? null,
                $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN',
                $_SERVER['REQUEST_URI'] ?? 'UNKNOWN',
                $_SERVER['REQUEST_METHOD'] ?? 'UNKNOWN',
                $_SERVER['HTTP_USER_AGENT'] ?? null
            );
        }
        session_destroy();
        View::redirect('login');
    }

    public function register()
    {
        echo $this->twig->render('users/create.html.twig', ['errors' => [], 'old' => []]);
    }

    public function store(array $postData)
    {
        $validator = new Validator;
        $validator->field('name', $postData['name'])->required()->min(2)->max(50);
        $validator->field('username', $postData['username'])->required()->min(2)->max(50)->email();
        $validator->field('email', $postData['email'])->required()->email()->unique($this->userModel, 'email');
        $validator->field('password', $postData['password'])->required()->min(6)->max(20);
        $validator->field('confirm_password', $postData['confirm_password'])->required()->matches($postData['password'], 'Password');


        if ($validator->isSuccess()) {
            $userData = [
                'name' => $postData['name'],
                'username' => $postData['username'],
                'email' => $postData['email'],
                'password' => $this->userModel->hashPassword($postData['password']),
                'privilege_id' => $postData['privilege_id'] ?? 2
            ];

            try {
                $userId = $this->userModel->create($userData);
                if ($userId) {
                    $this->activityLogModel->logActivity(
                        $userId,
                        $postData['username'],
                        $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN',
                        $_SERVER['REQUEST_URI'] ?? 'UNKNOWN',
                        $_SERVER['REQUEST_METHOD'] ?? 'UNKNOWN',
                        $_SERVER['HTTP_USER_AGENT'] ?? null
                    );
                    View::redirect('login');
                } else {
                    $errors['database'] = "Failed to create user. Please try again.";
                    echo $this->twig->render('users/create.html.twig', ['errors' => $errors, 'old' => $postData]);
                }
            } catch (\PDOException $e) {
                error_log("User creation PDO error: " . $e->getMessage());
                $errors['database'] = "A database error occurred. Please try again.";
                echo $this->twig->render('users/create.html.twig', ['errors' => $errors, 'old' => $postData]);
            }
        } else {
            $errors = $validator->getErrors();
            echo $this->twig->render('users/create.html.twig', ['errors' => $errors, 'old' => $postData]);
        }
    }
}