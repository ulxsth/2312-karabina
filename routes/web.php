<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PlaylistCreateController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});
Route::get('/auth/spotify', [AuthController::class, 'redirectToSpotify']);
Route::get('/auth/spotify/callback', [AuthController::class, 'handleSpotifyCallback']);
Route::get('/home', function () {
    return view('welcome');
});
// Test route for PlaylistCreateController
Route::get('/home/testCreatePlaylist', [PlaylistCreateController::class, 'createPlaylistTest']);

// テスト用のViewを表示するためのルート
Route::get('/home/createplaylisttest', function () {
    return view('createPlaylist'); // createPlaylistTest.blade.php はテスト用のViewのファイル名です
});

Route::post('home/createPlaylist/result', [PlaylistCreateController::class, 'createPlaylist'])->name('createPlaylist');