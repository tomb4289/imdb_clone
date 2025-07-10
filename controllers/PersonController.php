<?php
namespace App\Controllers;

use PDO;
use Twig\Environment;
use App\Models\Person;
use App\Providers\Auth;

class PersonController extends BaseController
{
    protected Person $personModel;

    public function __construct(PDO $pdo, Environment $twig, array $config)
    {
        parent::__construct($pdo, $twig, $config);
        $this->personModel = new Person($this->pdo);
    }

    public function index()
    {
        $people = $this->personModel->all();
        echo $this->twig->render('people/index.html.twig', [
            'people' => $people,
            'base_url' => BASE
        ]);
    }

    public function show($queryParams = [])
    {
        $personId = $queryParams['id'] ?? null;

        if (empty($personId)) {
            http_response_code(404);
            echo $this->twig->render('error/404.html.twig', ['base_url' => BASE]);
            return;
        }

        try {
            $person = $this->personModel->find($personId);

            if (!$person) {
                http_response_code(404);
                echo $this->twig->render('error/404.html.twig', ['base_url' => BASE]);
                return;
            }

            echo $this->twig->render('people/show.html.twig', ['person' => $person, 'base_url' => BASE]);

        } catch (\PDOException $e) {
            error_log("Database Error in PersonController::show: " . $e->getMessage());
            http_response_code(500);
            echo $this->twig->render('error/500.html.twig', ['base_url' => BASE]);
            return;
        }
    }

    public function create() {
        Auth::session();
        Auth::privilege(1);
        echo $this->twig->render('people/create.html.twig', ['base_url' => BASE]);
    }

    public function store($postData) {
        Auth::session();
        Auth::privilege(1);
        try {
            $name = $postData['name'] ?? null;
            $birth_year = $postData['birth_year'] ?? null;

            if (empty($name)) {
                http_response_code(400);
                echo $this->twig->render('error/400.html.twig', ['message' => 'Person name is required.', 'base_url' => BASE]);
                return;
            }

            $data = [
                'name' => $name,
                'birth_year' => !empty($birth_year) ? $birth_year : null
            ];

            if ($this->personModel->create($data)) {
                header('Location: ' . BASE . '/people');
                exit();
            } else {
                error_log("Error creating person via Person model::create.");
                http_response_code(500);
                echo $this->twig->render('error/500.html.twig', ['base_url' => BASE, 'message' => 'Failed to create person.']);
            }
        } catch (\PDOException $e) {
            error_log("Database Error in PersonController::store: " . $e->getMessage());
            http_response_code(500);
            echo $this->twig->render('error/500.html.twig', ['base_url' => BASE]);
            return;
        }
    }

    public function edit($queryParams = [])
    {
        Auth::session();
        Auth::privilege(1);
        $personId = $queryParams['id'] ?? null;

        if (empty($personId)) {
            http_response_code(404);
            echo $this->twig->render('error/404.html.twig', ['base_url' => BASE]);
            return;
        }

        try {
            $person = $this->personModel->find($personId);

            if (!$person) {
                http_response_code(404);
                echo $this->twig->render('error/404.html.twig', ['base_url' => BASE]);
                return;
            }

            echo $this->twig->render('people/edit.html.twig', [
                'person' => $person,
                'base_url' => BASE
            ]);

        } catch (\PDOException $e) {
            error_log("Database Error in PersonController::edit: " . $e->getMessage());
            http_response_code(500);
            echo $this->twig->render('error/500.html.twig', ['base_url' => BASE]);
            return;
        }
    }

    public function update($postData)
    {
        Auth::session();
        Auth::privilege(1);
        $personId = $postData['id'] ?? null;
        $name = $postData['name'] ?? null;
        $birth_year = $postData['birth_year'] ?? null;

        if (empty($personId) || empty($name)) {
            http_response_code(400);
            echo $this->twig->render('error/400.html.twig', ['message' => 'Person ID and name are required for update.', 'base_url' => BASE]);
            return;
        }

        try {
            $data = [
                'name' => $name,
                'birth_year' => !empty($birth_year) ? $birth_year : null
            ];

            if ($this->personModel->update($personId, $data)) {
                header('Location: ' . BASE . '/people/show?id=' . $personId);
                exit();
            } else {
                error_log("Error updating person via BaseModel::update.");
                http_response_code(500);
                echo $this->twig->render('error/500.html.twig', ['base_url' => BASE, 'message' => 'Failed to update person.']);
            }

        } catch (\PDOException $e) {
            error_log("Database Error in PersonController::update: " . $e->getMessage());
            http_response_code(500);
            echo $this->twig->render('error/500.html.twig', ['base_url' => BASE]);
            return;
        }
    }

    public function delete($postData)
    {
        Auth::session();
        Auth::privilege(1);
        $personId = $postData['id'] ?? null;

        if (empty($personId)) {
            http_response_code(400);
            echo $this->twig->render('error/400.html.twig', ['message' => 'Person ID is required for deletion.', 'base_url' => BASE]);
            return;
        }

        try {
            if ($this->personModel->delete($personId)) {
                header('Location: ' . BASE . '/people');
                exit();
            } else {
                error_log("Error deleting person via BaseModel::delete.");
                http_response_code(500);
                echo $this->twig->render('error/500.html.twig', ['base_url' => BASE, 'message' => 'Failed to delete person.']);
            }
        } catch (\PDOException $e) {
            error_log("Database Error in PersonController::delete: " . $e->getMessage());
            http_response_code(500);
            echo $this->twig->render('error/500.html.twig', ['base_url' => BASE]);
            return;
        }
    }
}