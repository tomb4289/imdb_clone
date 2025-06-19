<?php
// imdb_clone/controllers/HomeController.php
namespace App\Controllers;

use PDO;
use Twig\Environment;
use App\Models\Movie;

class HomeController
{
    protected PDO $pdo;
    protected Environment $twig;

    public function __construct(PDO $pdo, Environment $twig)
    {
        $this->pdo = $pdo;
        $this->twig = $twig;
    }

    public function index()
    {
        $movieModel = new Movie($this->pdo);

        $recentMovies = $movieModel->getRecent(5); // Get the 5 most recent movies

        // Render the home Twig template, passing the fetched movies data
        echo $this->twig->render('Layouts/home.html.twig', [
            'recent_movies' => $recentMovies
        ]);
    }
}