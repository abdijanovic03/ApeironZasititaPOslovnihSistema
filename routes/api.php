<?php

use App\Http\Controllers\Api\ApiController;
use App\Http\Controllers\Api\BlogController;
use Illuminate\Support\Facades\Route;

Route::post('user/register', [ApiController::class, 'register'])->middleware('throttle:auth');
Route::post('user/login', [ApiController::class, 'login'])->middleware('throttle:auth');

Route::group([
    'middleware' => ['auth:api', 'throttle:api'],
], function () {

    Route::get('user/me', [ApiController::class, 'profile']);
    Route::get('user/refresh-token', [ApiController::class, 'refreshToken']);
    Route::get('user/logout', [ApiController::class, 'logout']);

    Route::post('blog', action: [BlogController::class, 'createBlog']);
    Route::get('blog', [BlogController::class, 'listBlog']);
    Route::get('blog/me', [BlogController::class, 'myBlog']);
    Route::get('blog/{id}', [BlogController::class, 'getById']);
    Route::put('blog', [BlogController::class, 'updateBlog']);
    Route::delete('blog/{id}', [BlogController::class, 'deleteBlog']);
});
