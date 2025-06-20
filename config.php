<?php
// imdb_clone/config.php

define('BASE', '/imdb_clone'); 


define('ASSET', '/imdb_clone/public/'); 

return [
    'app' => [
        'name' => 'IMDb Clone',
        'debug' => true, 
    ],
    'database' => [
        'host' => 'localhost',
        'dbname' => 'e2496310',
        'user' => 'e2496310',
        'password' => 'Qs6RfaEwkd2Ca2ZWWl2J', 
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
];