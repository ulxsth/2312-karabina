<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

Route::get('/spotify', function (Request $request) {
  $authController = new AuthController();
  $authController->redirectToSpotify();
});

Route::get(env("SPOTIFY_REDIRECT_URI"), function (Request $request) {
  $authController = new AuthController();
  $authController->handleSpotifyCallback($request);
});
?>
