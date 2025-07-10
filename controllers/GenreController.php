<?php
namespace App\Controllers;

use PDO;
use Twig\Environment;
use App\Models\Genre;
use App\Providers\Auth;

class GenreController
{
    protected PDO $pdo;
    protected Environment $twig;
    protected Genre $genreModel;

    public function __construct(PDO $pdo, Environment $twig, array $config)
    {
        $this->pdo = $pdo;
        $this->twig = $twig;
        $this->genreModel = new Genre($this->pdo);
    }

    public function index()
    {
        Auth::session();
        $genres = $this->genreModel->all();
        echo $this->twig->render('genres/index.html.twig', [
            'genres' => $genres,
            'base_url' => BASE
        ]);
    }

    public function show($queryParams = [])
    {
        Auth::session();
        $genreId = $queryParams['id'] ?? null;
        if (empty($genreId)) {
            http_response_code(404);
            echo $this->twig->render('error/404.html.twig', ['base_url' => BASE]);
            return;
        }
        try {
            $genre = $this->genreModel->find($genreId);
            if (!$genre) {
                http_response_code(404);
                echo $this->twig->render('error/404.html.twig', ['base_url' => BASE]);
                return;
            }
            echo $this->twig->render('genres/show.html.twig', ['genre' => $genre, 'base_url' => BASE]);
        } catch (\PDOException $e) {
            error_log("Database Error in GenreController::show: " . $e->getMessage());
            http_response_code(500);
            echo $this->twig->render('error/500.html.twig', ['base_url' => BASE]);
            return;
        }
    }

    public function create() {
        Auth::session();
        Auth::privilege(1);
        echo $this->twig->render('genres/create.html.twig', ['base_url' => BASE]);
    }

    public function store($postData) {
        Auth::session();
        Auth::privilege(1);
        try {
            $name = $postData['name'] ?? null;
            if (empty($name)) {
                http_response_code(400);
                echo $this->twig->render('error/400.html.twig', ['message' => 'Genre name is required.', 'base_url' => BASE]);
                return;
            }
            if ($this->genreModel->create(['name' => $name])) {
                header('Location: ' . BASE . '/genres');
                exit();
            } else {
                error_log("Error creating genre via BaseModel::create.");
                http_response_code(500);
                echo $this->twig->render('error/500.html.twig', ['base_url' => BASE, 'message' => 'Failed to create genre.']);
            }
        } catch (\PDOException $e) {
            error_log("Database Error in GenreController::store: " . $e->getMessage());
            http_response_code(500);
            echo $this->twig->render('error/500.html.twig', ['base_url' => BASE]);
            return;
        }
    }

    public function edit($queryParams = [])
    {
        Auth::session();
        Auth::privilege(1);
        $genreId = $queryParams['id'] ?? null;
        if (empty($genreId)) {
            http_response_code(404);
            echo $this->twig->render('error/404.html.twig', ['base_url' => BASE]);
            return;
        }
        try {
            $genre = $this->genreModel->find($genreId);
            if (!$genre) {
                http_response_code(404);
                echo $this->twig->render('error/404.html.twig', ['base_url' => BASE]);
                return;
            }
            echo $this->twig->render('genres/edit.html.twig', [
                'genre' => $genre,
                'base_url' => BASE
            ]);
        } catch (\PDOException $e) {
            error_log("Database Error in GenreController::edit: " . $e->getMessage());
            http_response_code(500);
            echo $this->twig->render('error/500.html.twig', ['base_url' => BASE]);
            return;
        }
    }

    public function update($postData)
    {
        Auth::session();
        Auth::privilege(1);
        $genreId = $postData['id'] ?? null;
        $name = $postData['name'] ?? null;

        if (empty($genreId) || empty($name)) {
            http_response_code(400);
            echo $this->twig->render('error/400.html.twig', ['message' => 'Genre ID and name are required for update.', 'base_url' => BASE]);
            return;
        }

        try {
            if ($this->genreModel->update($genreId, ['name' => $name])) {
                header('Location: ' . BASE . '/genres/show?id=' . $genreId);
                exit();
            } else {
                error_log("Error updating genre via BaseModel::update.");
                http_response_code(500);
                echo $this->twig->render('error/500.html.twig', ['base_url' => BASE, 'message' => 'Failed to update genre.']);
            }

        } catch (\PDOException $e) {
            error_log("Database Error in GenreController::update: " . $e->getMessage());
            http_response_code(500);
            echo $this->twig->render('error/500.html.twig', ['base_url' => BASE]);
            return;
        }
    }

    public function delete($postData)
    {
        Auth::session();
        Auth::privilege(1);
        $genreId = $postData['id'] ?? null;

        if (empty($genreId)) {
            http_response_code(400);
            echo $this->twig->render('error/400.html.twig', ['message' => 'Genre ID is required for deletion.', 'base_url' => BASE]);
            return;
        }

        try {
            if ($this->genreModel->delete($genreId)) {
                header('Location: ' . BASE . '/genres');
                exit();
            } else {
                error_log("Error deleting genre via BaseModel::delete.");
                http_response_code(500);
                echo $this->twig->render('error/500.html.twig', ['base_url' => BASE, 'message' => 'Failed to delete genre.']);
            }
        } catch (\PDOException $e) {
            error_log("Database Error in GenreController::delete: " . $e->getMessage());
            http_response_code(500);
            echo $this->twig->render('error/500.html.twig', ['base_url' => BASE]);
            return;
        }
    }
}