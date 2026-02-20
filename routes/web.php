<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LoginController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/', [LoginController::class, 'showLoginForm']);
Route::post('/login', [LoginController::class, 'login']);
Route::get('/users', [LoginController::class, 'users']);
Route::post('/users/{id}/update-login', [LoginController::class, 'updateLastLogin']);
