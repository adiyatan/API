<?php

use App\Http\Controllers\BookstubesController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DogController;
use App\Http\Controllers\MemberTubesController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::group(['prefix' => 'lazyDog'], function () {
    Route::get('/', [DogController::class, 'lazyDog']);
    Route::get('/{id}', [DogController::class, 'getDogById']);
    Route::post('dogs', [DogController::class, 'add']);
    Route::put('dogs/{id}', [DogController::class, 'update']);
    Route::patch('dogs/{id}', [DogController::class, 'update']);
    Route::delete('dogs/{id}', [DogController::class, 'destroy']);
});

Route::group(['prefix' => 'mobiletubes'], function () {
    //member
    Route::get('/member', [MemberTubesController::class, 'index']);
    Route::get('/member/{id}', [MemberTubesController::class, 'getById']);
    Route::post('/member', [MemberTubesController::class, 'create']);
    Route::post('/member/{id}', [MemberTubesController::class, 'update']);
    Route::delete('/member/{id}', [MemberTubesController::class, 'delete']);
    //book
    Route::get('/book', [BookstubesController::class, 'index']);
    Route::get('/book/{id}', [BookstubesController::class, 'getById']);
    Route::post('/book', [BookstubesController::class, 'create']);
    Route::post('/book/{id}', [BookstubesController::class, 'update']);
    Route::delete('/book/{id}', [BookstubesController::class, 'delete']);
});
