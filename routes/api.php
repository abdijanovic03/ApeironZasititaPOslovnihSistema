<?php

use App\Http\Controllers\Api\ApiController;
use App\Http\Controllers\Api\BlogController;
use Illuminate\Support\Facades\Route;

Route::post('register', [ApiController::class, 'register']);
Route::post('login', [ApiController::class, 'login']);

Route::group([
    'middleware' => ['auth:api'],
], function () {

    Route::get('profile', [ApiController::class, 'profile']);
    Route::get('refresh-token', [ApiController::class, 'refreshToken']);
    Route::get('logout', [ApiController::class, 'logout']);

    Route::post('create-blog', [BlogController::class, 'createBlog']);
    Route::get('blogs', [BlogController::class, 'listBlog']);
    Route::get('my-blogs', [BlogController::class, 'myBlog']);
    Route::get('blog/{id}', [BlogController::class, 'getById']);
    Route::post('update-blog', [BlogController::class, 'updateBlog']);
    Route::delete('delete-blog/{id}', [BlogController::class, 'deleteBlog']);
});
