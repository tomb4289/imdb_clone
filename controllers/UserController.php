<?php
namespace App\Controllers;

use App\Providers\View;
use App\Models\User;
use App\Providers\Validator;
use App\Providers\Auth;
use PDO;
use Twig\Environment;

class UserController extends BaseController
{
    protected User $userModel;

    public function __construct(PDO $pdo, Environment $twig, array $config)
    {
        parent::__construct($pdo, $twig, $config);
        $this->userModel = new User($pdo);
    }

    public function index() {
        Auth::session();
        Auth::privilege(1);
        $users = $this->userModel->all();
        echo $this->twig->render('users/index.html.twig', ['users' => $users]);
    }

    public function show(array $queryParams) {
        Auth::session();
        Auth::privilege(1);
        $id = $queryParams['id'] ?? null;
        if (!$id) {
            View::redirect('users');
        }
        $user = $this->userModel->find($id);
        if (!$user) {
            http_response_code(404);
            echo $this->twig->render('error/404.html.twig');
            return;
        }
        echo $this->twig->render('users/show.html.twig', ['user' => $user]);
    }

    public function edit(array $queryParams) {
        Auth::session();
        Auth::privilege(1);
        $id = $queryParams['id'] ?? null;
        if (!$id) {
            View::redirect('users');
        }
        $user = $this->userModel->find($id);
        if (!$user) {
            http_response_code(404);
            echo $this->twig->render('error/404.html.twig');
            return;
        }
        echo $this->twig->render('users/edit.html.twig', ['user' => $user, 'errors' => [], 'old' => $user]);
    }

    public function update(array $postData) {
        Auth::session();
        Auth::privilege(1);
        $id = $postData['id'] ?? null;
        if (!$id) {
            View::redirect('users');
        }

        $validator = new Validator;
        $validator->field('name', $postData['name'])->required()->min(2)->max(50);
        $validator->field('username', $postData['username'])->required()->min(2)->max(50);
        $validator->field('email', $postData['email'])->required()->email();
        $validator->field('privilege_id', $postData['privilege_id'])->required();

        if ($validator->isSuccess()) {
            $userData = [
                'name' => $postData['name'],
                'username' => $postData['username'],
                'email' => $postData['email'],
                'privilege_id' => $postData['privilege_id']
            ];

            if (!empty($postData['password'])) {
                $validator->field('password', $postData['password'])->min(6)->max(20);
                $validator->field('confirm_password', $postData['confirm_password'])->matches($postData['password'], 'Password');
                if ($validator->isSuccess()) {
                    $userData['password'] = $this->userModel->hashPassword($postData['password']);
                } else {
                    $errors = $validator->getErrors();
                    echo $this->twig->render('users/edit.html.twig', ['user' => $postData, 'errors' => $errors, 'old' => $postData]);
                    return;
                }
            }

            try {
                $updated = $this->userModel->update($id, $userData);
                if ($updated) {
                    View::redirect('users');
                } else {
                    $errors['database'] = "Failed to update user. Please try again.";
                    echo $this->twig->render('users/edit.html.twig', ['user' => $postData, 'errors' => $errors, 'old' => $postData]);
                }
            } catch (PDOException $e) {
                error_log("User update PDO error: " . $e->getMessage());
                $errors['database'] = "A database error occurred. Please try again.";
                echo $this->twig->render('users/edit.html.twig', ['user' => $postData, 'errors' => $errors, 'old' => $postData]);
            }
        } else {
            $errors = $validator->getErrors();
            echo $this->twig->render('users/edit.html.twig', ['user' => $postData, 'errors' => $errors, 'old' => $postData]);
        }
    }

    public function delete(array $postData) {
        Auth::session();
        Auth::privilege(1);
        $id = $postData['id'] ?? null;
        if (!$id) {
            View::redirect('users');
        }
        $this->userModel->delete($id);
        View::redirect('users');
    }
}