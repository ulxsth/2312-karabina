<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

Route::get('/auth/spotify', function (Request $request) {
  $authController = new AuthController();
  $authController->redirectToSpotify();
});

Route::get(trim(str_replace("auth/", "", env("SPOTIFY_REDIRECT_URI"))), function (Request $request) {
  $authController = new AuthController();
  $authController->handleSpotifyCallback($request);
});
?>
