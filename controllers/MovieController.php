<?php
// imdb_clone/controllers/MovieController.php
namespace App\Controllers;

use PDO;
use Twig\Environment;
use App\Models\Movie;
use App\Models\Person;

class MovieController
{
    protected PDO $pdo;
    protected Environment $twig;
    protected Movie $movieModel;
    protected Person $personModel;

    public function __construct(PDO $pdo, Environment $twig)
    {
        $this->pdo = $pdo;
        $this->twig = $twig;
        $this->movieModel = new Movie($this->pdo);
        $this->personModel = new Person($this->pdo);
    }

    public function index()
    {
        $movies = $this->movieModel->all();
        echo $this->twig->render('movies/index.html.twig', [
            'movies' => $movies,
            'base_url' => BASE
        ]);
    }

    public function show($queryParams = [])
    {
        $movieId = $queryParams['id'] ?? null;

        if (empty($movieId)) {
            http_response_code(404);
            echo $this->twig->render('error/404.html.twig', ['base_url' => BASE]);
            return;
        }

        try {
            $movie = $this->movieModel->find($movieId);

            if (!$movie) {
                http_response_code(404);
                echo $this->twig->render('error/404.html.twig', ['base_url' => BASE]);
                return;
            }

            echo $this->twig->render('movies/show.html.twig', ['movie' => $movie, 'base_url' => BASE]);

        } catch (\PDOException $e) {
            error_log("Database Error in MovieController::show: " . $e->getMessage());
            http_response_code(500);
            echo $this->twig->render('error/500.html.twig', ['base_url' => BASE]);
            return;
        }
    }


    public function create()
    {

        $directors = $this->personModel->all(); 

        echo $this->twig->render('movies/create.html.twig', [
            'base_url' => BASE,
            'directors' => $directors 
        ]);
    }

    public function store($postData)
    {
        $title = $postData['title'] ?? null;
        $release_date = $postData['release_date'] ?? null;
        $description = $postData['description'] ?? null;
        $runtime = $postData['runtime'] ?? null;
        $director_id = $postData['director_id'] ?? null;

        // Basic validation
        if (empty($title) || empty($release_date) || empty($description) || empty($runtime)) {
            http_response_code(400);
            echo $this->twig->render('error/400.html.twig', ['message' => 'Movie title, release date, description, and runtime are required.', 'base_url' => BASE]);
            return;
        }

        try {

            $data = [
                'title' => $title,
                'release_date' => $release_date,
                'description' => $description,
                'runtime' => $runtime,
                'director_id' => !empty($director_id) ? $director_id : null 
            ];

            if ($this->movieModel->create($data)) {
                header('Location: ' . BASE . '/movies');
                exit();
            } else {
                error_log("Error creating movie via BaseModel::create.");
                http_response_code(500);
                echo $this->twig->render('error/500.html.twig', ['base_url' => BASE, 'message' => 'Failed to create movie.']);
            }

        } catch (\PDOException $e) {
            error_log("Database Error in MovieController::store: " . $e->getMessage());
            http_response_code(500);
            echo $this->twig->render('error/500.html.twig', ['base_url' => BASE]);
            return;
        }
    }


    public function edit($queryParams = [])
    {
        $movieId = $queryParams['id'] ?? null;

        if (empty($movieId)) {
            http_response_code(404);
            echo $this->twig->render('error/404.html.twig', ['base_url' => BASE]);
            return;
        }

        try {
            $movie = $this->movieModel->find($movieId);
            $directors = $this->personModel->all(); // Get all people for director dropdown

            if (!$movie) {
                http_response_code(404);
                echo $this->twig->render('error/404.html.twig', ['base_url' => BASE]);
                return;
            }

            echo $this->twig->render('movies/edit.html.twig', [
                'movie' => $movie,
                'base_url' => BASE,
                'directors' => $directors
            ]);

        } catch (\PDOException $e) {
            error_log("Database Error in MovieController::edit: " . $e->getMessage());
            http_response_code(500);
            echo $this->twig->render('error/500.html.twig', ['base_url' => BASE]);
            return;
        }
    }

    public function update($postData)
    {
        $movieId = $postData['id'] ?? null;
        $title = $postData['title'] ?? null;
        $release_date = $postData['release_date'] ?? null;
        $description = $postData['description'] ?? null;
        $runtime = $postData['runtime'] ?? null;
        $director_id = $postData['director_id'] ?? null;

        if (empty($movieId) || empty($title) || empty($release_date) || empty($description) || empty($runtime)) {
            http_response_code(400);
            echo $this->twig->render('error/400.html.twig', ['message' => 'All movie fields (except director) are required for update.', 'base_url' => BASE]);
            return;
        }

        try {
            $data = [
                'title' => $title,
                'release_date' => $release_date,
                'description' => $description,
                'runtime' => $runtime,
                'director_id' => !empty($director_id) ? $director_id : null
            ];

            if ($this->movieModel->update($movieId, $data)) {
                header('Location: ' . BASE . '/movies/show?id=' . $movieId);
                exit();
            } else {
                error_log("Error updating movie via BaseModel::update.");
                http_response_code(500);
                echo $this->twig->render('error/500.html.twig', ['base_url' => BASE, 'message' => 'Failed to update movie.']);
            }

        } catch (\PDOException $e) {
            error_log("Database Error in MovieController::update: " . $e->getMessage());
            http_response_code(500);
            echo $this->twig->render('error/500.html.twig', ['base_url' => BASE]);
            return;
        }
    }

    public function delete($postData)
    {
        $movieId = $postData['id'] ?? null;

        if (empty($movieId)) {
            http_response_code(400);
            echo $this->twig->render('error/400.html.twig', ['message' => 'Movie ID is required for deletion.', 'base_url' => BASE]);
            return;
        }

        try {
            if ($this->movieModel->delete($movieId)) {
                header('Location: ' . BASE . '/movies');
                exit();
            } else {
                error_log("Error deleting movie via BaseModel::delete.");
                http_response_code(500);
                echo $this->twig->render('error/500.html.twig', ['base_url' => BASE, 'message' => 'Failed to delete movie.']);
            }
        } catch (\PDOException $e) {
            error_log("Database Error in MovieController::delete: " . $e->getMessage());
            http_response_code(500);
            echo $this->twig->render('error/500.html.twig', ['base_url' => BASE]);
            return;
        }
    }
}