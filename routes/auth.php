<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

Route::get('/spotify', function (Request $request) {
  $authController = new AuthController();
  $authController->redirectToSpotify();
});

Route::get('/spotify/callback', function (Request $request) {
  $authController = new AuthController();
  $authController->handleSpotifyCallback($request);
});
?>
