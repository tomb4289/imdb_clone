<?php
// imdb_clone/config.php

define('BASE', '/imdb_clone'); 


define('ASSET', '/imdb_clone/public/'); 

return [
    'app' => [
        'name' => 'IMDb Clone',
        'debug' => true, 
        'base_url' => '/imdb_clone',
        'asset_url' => '/imdb_clone/public/',
    ],
    'database' => [
        'host' => 'localhost',
        'dbname' => 'imdb_clone',
        'user' => 'root',
        'password' => 'admin', 
        'charset' => 'utf8mb4',
    ],
    'paths' => [

        'root' => __DIR__ . '/',         
        'templates' => __DIR__ . '/views',
        'controllers' => __DIR__ . '/controllers/',
        'models' => __DIR__ . '/models/',
        'routes' => __DIR__ . '/routes/',
        'cache' => __DIR__ . '/var/cache', 
    ],
    'resend' => [
        'api_key' => '',
    ],
];

