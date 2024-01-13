<?php
use App\Http\Controllers\HistoryController;
use App\Models\History;
use App\Models\SpotifyUser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class HistoryControllerTest extends TestCase
{
  use RefreshDatabase;

  /**
   * @test
   */
  public function 正しくフェッチされるか()
  {
    // Arrange
    $user = SpotifyUser::factory()->create();
    $controller = new HistoryController();
    $timestamp = strtotime('-5 minutes');
    $response = [
      'items' => [
        [
          'played_at' => '2022-01-01 12:00:00',
          'track' => [
            'id' => 'track_id_1',
          ],
        ],
        [
          'played_at' => '2022-01-01 12:05:00',
          'track' => [
            'id' => 'track_id_2',
          ],
        ],
      ],
    ];

    Http::fake([
      'https://api.spotify.com/v1/me/player/recently-played' => Http::response($response),
    ]);

    // Act
    $result = $controller->fetch($user);

    // Assert
    $this->assertTrue($result);
    $this->assertDatabaseCount('histories', 2);
    $this->assertDatabaseHas('histories', [
      'user_spotify_id' => $user->spotify_id,
      'played_at' => '2022-01-01 12:00:00',
      'track_spotify_id' => 'track_id_1',
    ]);
    $this->assertDatabaseHas('histories', [
      'user_spotify_id' => $user->spotify_id,
      'played_at' => '2022-01-01 12:05:00',
      'track_spotify_id' => 'track_id_2',
    ]);
  }
}
?>

