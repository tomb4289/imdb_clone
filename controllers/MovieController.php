<?php
namespace App\Controllers;

use PDO;
use Twig\Environment;
use App\Models\Movie;
use App\Models\Genre;
use App\Models\Person;
use App\Providers\Validator;
use App\Providers\View;
use App\Providers\Auth; // For privilege check

class MovieController extends BaseController
{
    protected Movie $movieModel;
    protected Genre $genreModel;
    protected Person $personModel;

    public function __construct(PDO $pdo, Environment $twig, array $config)
    {
        parent::__construct($pdo, $twig, $config);
        $this->movieModel = new Movie($pdo);
        $this->genreModel = new Genre($pdo);
        $this->personModel = new Person($pdo);
    }

    public function index()
    {
        $movies = $this->movieModel->getAllWithDetails();
        echo $this->twig->render('movies/index.html.twig', ['movies' => $movies]);
    }

    public function show(array $queryParams)
    {
        $id = $queryParams['id'] ?? null;
        if (!$id) {
            View::redirect('movies');
        }
        $movie = $this->movieModel->getByIdWithDetails($id);
        if (!$movie) {
            http_response_code(404);
            echo $this->twig->render('error/404.html.twig');
            return;
        }
        echo $this->twig->render('movies/show.html.twig', ['movie' => $movie]);
    }

    public function create()
    {
        if (!Auth::privilege(1)) { // Only admin (privilege_id 1) can create
            return;
        }
        $genres = $this->genreModel->getAll();
        $people = $this->personModel->getAll();
        echo $this->twig->render('movies/create.html.twig', ['genres' => $genres, 'people' => $people, 'errors' => [], 'old' => []]);
    }

    public function store(array $postData)
    {
        if (!Auth::privilege(1)) {
            return;
        }

        $validator = new Validator();
        $validator->field('title', $postData['title'])->required()->min(2)->max(255);
        $validator->field('release_date', $postData['release_date'])->required();
        $validator->field('runtime', $postData['runtime'])->required();
        $validator->field('description', $postData['description'])->required()->min(10);
        $validator->field('director_id', $postData['director_id']);
        $validator->field('genre_ids', $postData['genre_ids']);

        if ($validator->isSuccess()) {
            $movieData = [
                'title' => $postData['title'],
                'release_date' => $postData['release_date'],
                'runtime' => (int)$postData['runtime'],
                'description' => $postData['description'],
                'director_id' => !empty($postData['director_id']) ? (int)$postData['director_id'] : null,
                'poster_path' => $postData['poster_path'] ?? null
            ];

            $this->pdo->beginTransaction();
            try {
                $movieId = $this->movieModel->create($movieData);

                if ($movieId && !empty($postData['genre_ids'])) {
                    $genreStmt = $this->pdo->prepare("INSERT INTO movie_genres (movie_id, genre_id) VALUES (:movie_id, :genre_id)");
                    foreach ($postData['genre_ids'] as $genreId) {
                        $genreStmt->execute([':movie_id' => $movieId, ':genre_id' => (int)$genreId]);
                    }
                }
                $this->pdo->commit();
                View::redirect('movies');
            } catch (PDOException $e) {
                $this->pdo->rollBack();
                error_log("Movie creation error: " . $e->getMessage());
                $errors['database'] = "Failed to create movie. Please try again.";
                $genres = $this->genreModel->getAll();
                $people = $this->personModel->getAll();
                echo $this->twig->render('movies/create.html.twig', ['genres' => $genres, 'people' => $people, 'errors' => $errors, 'old' => $postData]);
            }
        } else {
            $errors = $validator->getErrors();
            $genres = $this->genreModel->getAll();
            $people = $this->personModel->getAll();
            echo $this->twig->render('movies/create.html.twig', ['genres' => $genres, 'people' => $people, 'errors' => $errors, 'old' => $postData]);
        }
    }

    public function edit(array $queryParams)
    {
        if (!Auth::privilege(1)) {
            return;
        }
        $id = $queryParams['id'] ?? null;
        if (!$id) {
            View::redirect('movies');
        }
        $movie = $this->movieModel->getByIdWithDetails($id);
        if (!$movie) {
            http_response_code(404);
            echo $this->twig->render('error/404.html.twig');
            return;
        }
        $genres = $this->genreModel->getAll();
        $people = $this->personModel->getAll();

        $selectedGenres = explode(', ', $movie['genres'] ?? '');
        $selectedGenreIds = array_map(function($genreName) use ($genres) {
            foreach ($genres as $genre) {
                if ($genre['name'] === $genreName) {
                    return $genre['id'];
                }
            }
            return null;
        }, $selectedGenres);
        $selectedGenreIds = array_filter($selectedGenreIds);

        echo $this->twig->render('movies/edit.html.twig', [
            'movie' => $movie,
            'genres' => $genres,
            'people' => $people,
            'selectedGenreIds' => $selectedGenreIds,
            'errors' => [],
            'old' => $movie
        ]);
    }

    public function update(array $postData)
    {
        if (!Auth::privilege(1)) {
            return;
        }

        $id = $postData['id'] ?? null;
        if (!$id) {
            View::redirect('movies');
        }

        $validator = new Validator();
        $validator->field('title', $postData['title'])->required()->min(2)->max(255);
        $validator->field('release_date', $postData['release_date'])->required();
        $validator->field('runtime', $postData['runtime'])->required();
        $validator->field('description', $postData['description'])->required()->min(10);
        $validator->field('director_id', $postData['director_id']);
        $validator->field('genre_ids', $postData['genre_ids']);

        if ($validator->isSuccess()) {
            $movieData = [
                'title' => $postData['title'],
                'release_date' => $postData['release_date'],
                'runtime' => (int)$postData['runtime'],
                'description' => $postData['description'],
                'director_id' => !empty($postData['director_id']) ? (int)$postData['director_id'] : null,
                'poster_path' => $postData['poster_path'] ?? null
            ];

            $this->pdo->beginTransaction();
            try {
                $this->movieModel->update($id, $movieData);

                // Update movie_genres
                $deleteStmt = $this->pdo->prepare("DELETE FROM movie_genres WHERE movie_id = :movie_id");
                $deleteStmt->execute([':movie_id' => $id]);

                if (!empty($postData['genre_ids'])) {
                    $insertStmt = $this->pdo->prepare("INSERT INTO movie_genres (movie_id, genre_id) VALUES (:movie_id, :genre_id)");
                    foreach ($postData['genre_ids'] as $genreId) {
                        $insertStmt->execute([':movie_id' => $id, ':genre_id' => (int)$genreId]);
                    }
                }
                $this->pdo->commit();
                View::redirect('movies');
            } catch (PDOException $e) {
                $this->pdo->rollBack();
                error_log("Movie update error: " . $e->getMessage());
                $errors['database'] = "Failed to update movie. Please try again.";
                $genres = $this->genreModel->getAll();
                $people = $this->personModel->getAll();
                echo $this->twig->render('movies/edit.html.twig', ['movie' => $postData, 'genres' => $genres, 'people' => $people, 'errors' => $errors, 'old' => $postData]);
            }
        } else {
            $errors = $validator->getErrors();
            $genres = $this->genreModel->getAll();
            $people = $this->personModel->getAll();
            $movie = $this->movieModel->getByIdWithDetails($id);
            $selectedGenres = explode(', ', $movie['genres'] ?? '');
            $selectedGenreIds = array_map(function($genreName) use ($genres) {
                foreach ($genres as $genre) {
                    if ($genre['name'] === $genreName) {
                        return $genre['id'];
                    }
                }
                return null;
            }, $selectedGenres);
            $selectedGenreIds = array_filter($selectedGenreIds);

            echo $this->twig->render('movies/edit.html.twig', [
                'movie' => $postData,
                'genres' => $genres,
                'people' => $people,
                'selectedGenreIds' => $selectedGenreIds,
                'errors' => $errors,
                'old' => $postData
            ]);
        }
    }

    public function delete(array $postData)
    {
        if (!Auth::privilege(1)) {
            return;
        }
        $id = $postData['id'] ?? null;
        if (!$id) {
            View::redirect('movies');
        }
        $this->movieModel->delete($id);
        View::redirect('movies');
    }
}