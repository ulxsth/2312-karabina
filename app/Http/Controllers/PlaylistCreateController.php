<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use App\Models\Playlist;
use App\Models\Track;
use App\Models\History;
use App\Models\SpotifyUser;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;

class PlaylistCreateController extends Controller
{
    // プレイリストを作成するメソッド
    public function createPlaylist(Request $request)
    {
        DB::enableQueryLog();

        // 1. ユーザーのトークンを確認して、期限切れなら更新
        $user = SpotifyUser::find(auth()->id());
        \Log::info('User ID for Playlist:', ['user_id' => auth()->id()]);

        if ($user && $user->isTokenExpired()) {
            // トークンを更新する処理
            app(AuthController::class)->refreshToken($user);
        }

        // 2. Get Recently Played Tracksを使用して最近再生したトラックを取得する
        $recentlyPlayedTracks = $this->getRecentlyPlayedTracks($user);

        // エラーハンドリング - トラックが取得できない場合
        if ($recentlyPlayedTracks === null || count($recentlyPlayedTracks) === 0) {
            return response()->json(['error' => 'No recently played tracks found.'], 500);
        }

        // 3. 最近再生したトラックからランダムに1つ選ぶ
        $randomTrack = $recentlyPlayedTracks[rand(0, count($recentlyPlayedTracks) - 1)];

        // 4. 新しい History レコードを作成
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        History::create([
            'track_id' => $randomTrack['track']['id'],
            'played_at' => Carbon::parse($startDate),
            'play_count' => 1,  // 適切な再生回数をセット
            'enddate' => Carbon::parse($endDate),  // 例えば、7日後の日時をセット
        ]);

        // 5. History モデルから指定期間内のデータを取得
        $historyData = History::inPeriod($startDate, $endDate)
            ->orderBy('play_count', 'desc')
            ->orderBy('played_at')
            ->get();

        \Log::info('SQL Query: ' . json_encode(DB::getQueryLog()));

        if ($historyData->isEmpty()) {
            // エラーハンドリング: 履歴データが取得できなかった場合
            \Log::error('Failed to retrieve history data.');
            return response()->json(['error' => 'Failed to retrieve history data.'], 500);
        }

        // 6. 空のプレイリストを作成する
        $playlist = new Playlist();
        $playlist->user_id = auth()->id();
        $playlist->name = $request->input('playlist_name'); // 適当にplaylist nameを入れる
        $playlist->save(); // プレイリストを保存してIDを取得

        // 7. Spotify プレイリストの作成(非公開)
        $spotifyPlaylistId = $this->createPlaylistOnSpotify($playlist->name, false, $user);

        if ($spotifyPlaylistId) {
            // プレイリストが正常に作成された場合、ローカルのデータベースから削除
            $playlist->delete();
        } else {
            // エラー処理
            return response()->json(['error' => 'Failed to create Spotify playlist.'], 500);
        }

        // 8. 取得した履歴データを元に曲をプレイリストに追加する
        foreach ($historyData as $history) {
            $track = Track::firstOrCreate(['name' => $history->track_name, 'artist' => $history->artist_name]);
            \Log::info('Track Data: ', ['name' => $track->name, 'artist' => $track->artist]);
            $playlist->tracks()->attach($track->id, ['playlist_id' => $playlist->id]);
        }

        // 9. 選択したランダムな楽曲をプレイリストに追加する
        $playlist->tracks()->create([
            'track_id' => $randomTrack['track']['id'],
            'name' => $randomTrack['track']['name'],
            'artist' => implode(', ', array_column($randomTrack['track']['artists'], 'name'))
        ]);

        // 10. ユーザが決めたプレイリスト名を受け取る
        $playlistName = $request->input('playlist_name');

        // 11. Spotifyリンクの取得
        $spotifyPlaylistLink = $this->generateSpotifyPlaylistLink($spotifyPlaylistId);

        // 12. プレイリスト作成が成功した場合、Viewを返す
        return view('createPlaylistResult', [
            'playlistName' => $playlistName,
            'spotifyPlaylistLink' => $spotifyPlaylistLink,
        ]);
    }

    /*
     * ちょっと前のトラックを取得して返すメソッド
     */
    private function getRecentlyPlayedTracks($user)
    {
        // Spotify APIエンドポイント
        $endpoint = 'https://api.spotify.com/v1/me/player/recently-played';

        // Spotify APIリクエストに必要なヘッダー
        $headers = [
            'Authorization' => 'Bearer ' . $user->access_token,
            'Content-Type' => 'application/json',
        ];

        // Spotify APIに最近再生したトラックを取得するリクエストを送信
        $response = Http::withHeaders($headers)->get($endpoint);

        // エラーハンドリング - レスポンスがエラーを含む場合
        if ($response->failed()) {
            Log::error('Failed to get recently played tracks. Response: ' . $response->body());
            return null;
        }

        // レスポンスが JSON データを含む場合
        $responseData = $response->json();

        // トラック情報が含まれているか確認
        if (isset($responseData['items']) && count($responseData['items']) > 0) {
            return $responseData['items'];
        } else {
            Log::error('No recently played tracks found in Spotify API response.');
            return null;
        }
    }
    /*
     * リンク生成メソッド
     */
    private function generateSpotifyPlaylistLink($playlistId)
    {
        $baseUrl = 'https://open.spotify.com/playlist/';
        return $baseUrl . $playlistId;
    }

    /*
     * プレイリスト作成メソッド
     */
    private function createPlaylistOnSpotify($playlistName, $isPublic, $user)
    {
        // Spotify APIエンドポイント
        $endpoint = 'https://api.spotify.com/v1/me/playlists';

        // Spotify APIリクエストに必要なヘッダー
        $headers = [
            'Authorization' => 'Bearer ' . $user->access_token,
            'Content-Type' => 'application/json',
        ];

        // Spotify APIリクエストのデータ
        $data = [
            'name' => $playlistName,
            'public' => $isPublic,
        ];

        // Spotify APIにプレイリスト作成のリクエストを送信
        $response = Http::withHeaders($headers)->post($endpoint, $data);

        // エラーハンドリング - レスポンスがエラーを含む場合
        if ($response->failed()) {
            Log::error('Failed to create Spotify playlist. Response: ' . $response->body());
            // 適切なエラーレスポンスを返すか、例外をスローするなど
            return null;
        }

        // レスポンスが JSON データを含む場合
        $responseData = $response->json();

        // playlist_id プロパティの存在を確認
        if (isset($responseData['id'])) {
            return $responseData['id'];
        } else {
            Log::error('Missing playlist_id in Spotify API response.');
            return null;
        }
    }

    /*
     * 公開設定メソッド
     */
    private function makePlaylistPublicOnSpotify($playlistId, $user)
    {
        // Spotify API エンドポイント
        $endpoint = "https://api.spotify.com/v1/playlists/{$playlistId}";

        // Spotify API リクエストに必要なヘッダー
        $headers = [
            'Authorization' => 'Bearer ' . $user->access_token,
            'Content-Type' => 'application/json',
        ];

        // Spotify API リクエストのデータ（公開にするためのデータ）
        $data = [
            'public' => true,
        ];

        // Spotify API にプレイリストを公開にするリクエストを送信
        $response = Http::withHeaders($headers)->put($endpoint, $data);

        // エラーハンドリング - レスポンスがエラーを含む場合
        if ($response->failed()) {
            Log::error('Failed to make playlist public on Spotify. Response: ' . $response->body());
            // 適切なエラーレスポンスを返すか、例外をスローするなど
            return false;
        }

        return true; // 公開成功
    }

    use RefreshDatabase, WithFaker;

    public function testCreatePlaylist()
    {
        // 1. Viewを表示
        $response = $this->get(route('your.createPlaylist.view.route'));
        $response->assertStatus(200);

        // 2. メソッドを呼び出してプレイリストを作成
        $response = $this->post(route('createPlaylist'), [
            'start_date' => '2024-01-01',
            'end_date' => '2024-01-10',
            'playlist_name' => 'MyPlaylist',
        ]);

        // 3. レスポンスが成功（200 OK）かどうかを確認
        $response->assertStatus(200);

        // 4. プレイリストがデータベースに正しく追加されたかどうかを確認
        $this->assertDatabaseHas('playlists', [
            'user_id' => auth()->id(),
            'name' => 'MyPlaylist',
            // 他に必要な条件があればここに追加
        ]);

        // 5. プレイリストに楽曲が追加されたかどうかを確認
        $this->assertDatabaseHas('tracks', [
            'name' => 'TrackName', // ここに実際の楽曲名を指定
            'artist' => 'ArtistName', // ここに実際のアーティスト名を指定
            // 他に必要な条件があればここに追加
        ]);

        // 6. レスポンスにSpotifyのリンクが含まれているかどうかを確認
        $response->assertSee('spotify.com'); // これは適切なリンクに変更する必要があります
    }
}
