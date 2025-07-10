<?php
namespace App\Controllers;

use PDO;
use Twig\Environment;
use App\Models\Movie;

class HomeController extends BaseController
{
    protected Movie $movieModel;

    public function __construct(PDO $pdo, Environment $twig, array $config)
    {
        parent::__construct($pdo, $twig, $config);
        $this->movieModel = new Movie($pdo);
    }

    public function index()
    {
        $recentMovies = $this->movieModel->getRecent(5);

        echo $this->twig->render('Layouts/home.html.twig', [
            'recent_movies' => $recentMovies
        ]);
    }
}