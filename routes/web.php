<?php
// imdb_clone/routes/web.php

use App\Routes\Route;
use App\Controllers\HomeController;
use App\Controllers\MovieController;
use App\Controllers\GenreController;
use App\Controllers\PersonController;
use App\Controllers\UserController;

// Homepage Routes
Route::get('/', 'HomeController@index');
Route::get('/home', 'HomeController@index');

// Movie Routes
Route::get('/movies', 'MovieController@index');
Route::get('/movies/show', 'MovieController@show');
Route::get('/movies/create', 'MovieController@create');
Route::post('/movies/store', 'MovieController@store');
Route::get('/movies/edit', 'MovieController@edit');
Route::post('/movies/update', 'MovieController@update');
Route::post('/movies/delete', 'MovieController@delete');

// Genre Routes
Route::get('/genres', 'GenreController@index');
Route::get('/genres/show', 'GenreController@show');
Route::get('/genres/create', 'GenreController@create');
Route::post('/genres/store', 'GenreController@store');
Route::get('/genres/edit', 'GenreController@edit');
Route::post('/genres/update', 'GenreController@update');
Route::post('/genres/delete', 'GenreController@delete');

// Person Routes
Route::get('/people', 'PersonController@index');
Route::get('/people/show', 'PersonController@show');
Route::get('/people/create', 'PersonController@create');
Route::post('/people/store', 'PersonController@store');
Route::get('/people/edit', 'PersonController@edit');
Route::post('/people/update', 'PersonController@update');
Route::post('/people/delete', 'PersonController@delete');

// User Routes
Route::get('/users', 'UserController@index');
Route::get('/users/show', 'UserController@show');
Route::get('/users/create', 'UserController@create');
Route::post('/users/store', 'UserController@store');
Route::get('/users/edit', 'UserController@edit'); 
Route::post('/users/update', 'UserController@update');
Route::post('/users/delete', 'UserController@delete'); 

Route::dispatch($pdo, $twig);