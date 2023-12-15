<?php
namespace Tests\Unit;

use Tests\TestCase;
use App\Http\Controllers\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class AuthControllerTest extends TestCase {
  /** @test */
  public function Spotifyの認証ページにリダイレクトするか() {
    $response = $this->get('/auth/spotify');

    $response->assertStatus(302);
    $response->assertRedirectContains('https://accounts.spotify.com/authorize');
  }

  /** @test */
  public function 認証成功時に問題なく処理しているか() {
    $authController = new AuthController();
    $request = Request::create('/auth/spotify/callback', 'GET', ['code' => 'test_code']);
    Http::fake([
      'https://accounts.spotify.com/api/token' => Http::response(['access_token' => 'test_token'], 200),
      'https://api.spotify.com/v1/me' => Http::response(['user_id' => 'test_user'], 200),
    ]);

    // Act
    $response = $authController->handleSpotifyCallback($request);

    // Assert
    $response->assertRedirect('/home');
  }
}
?>

