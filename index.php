<?php

session_start();

require_once __DIR__ . '/vendor/autoload.php';

$config = require_once __DIR__ . '/config.php';

if ($config['app']['debug']) {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    ini_set('display_startup_errors', 0);
    error_reporting(0);
}

try {
    $dsn = "mysql:host={$config['database']['host']};dbname={$config['database']['dbname']};charset={$config['database']['charset']}";
    $pdo = new PDO($dsn, $config['database']['user'], $config['database']['password']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

$loader = new \Twig\Loader\FilesystemLoader($config['paths']['templates']);
$twig = new \Twig\Environment($loader, [
    'cache' => $config['app']['debug'] ? false : $config['paths']['root'] . 'var/cache/twig',
    'debug' => $config['app']['debug'],
]);

$twig->addGlobal('BASE', BASE);
$twig->addGlobal('ASSET', ASSET);

if ($config['app']['debug']) {
    $twig->addExtension(new \Twig\Extension\DebugExtension());
}

require_once __DIR__ . '/routes/web.php';

\App\Routes\Route::dispatch($pdo, $twig, $config);