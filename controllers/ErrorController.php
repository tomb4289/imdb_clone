<?php
namespace App\Controllers;

use PDO;
use Twig\Environment;

class ErrorController extends BaseController
{
    public function __construct(PDO $pdo, Environment $twig, array $config)
    {
        parent::__construct($pdo, $twig, $config);
    }

    public function forbidden()
    {
        header("HTTP/1.0 403 Forbidden");
        echo $this->twig->render('error/403.html.twig');
        exit();
    }

    public function notFound()
    {
        header("HTTP/1.0 404 Not Found");
        echo $this->twig->render('error/404.html.twig');
        exit();
    }
}