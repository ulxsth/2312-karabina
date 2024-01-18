<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use App\Models\Playlist;
use App\Models\History;
use App\Models\SpotifyUser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;

class PlaylistCreateController extends Controller
{

    /**
     * プレイリストを作成するメソッド
     *
     * @param Request $request
     * @return \Illuminate\Contracts\View\View
     */
    public function createPlaylist(Request $request)
    {
        DB::enableQueryLog();
        // 1. ユーザーのトークンを確認して、期限切れなら更新
        $user = SpotifyUser::first();

        Auth::login($user);

        if ($user && $user->isTokenExpired()) {
            // トークンを更新する処理
            app(AuthController::class)->refreshToken($user);
        }

        // フォームからデータを取得
        $startDate = Carbon::parse($request->input('start_date'))->startOfDay();
        $endDate = Carbon::parse($request->input('end_date'))->endOfDay();

        // ユーザが最近再生した曲を取得
        $recentlyPlayedTracks = $this->getMyRecentTracksWithTimestamps($user, $startDate, $endDate);
        //ok \Log::info('Recently Played Tracks: ' . json_encode($recentlyPlayedTracks));

        // 繰り返し回数
        $repeatCount = 50;

        for ($i = 0; $i < $repeatCount; $i++) {
            // 最近再生したトラックからランダムに1つ選ぶ
            $randomTrack = $recentlyPlayedTracks[rand(0, count($recentlyPlayedTracks) - 1)];
            // \Log::info('Random Track Data: ' . json_encode($randomTrack));

            // 各トラックの情報にアクセス
            $randomTrackData = $randomTrack['track'];

            // データベースに保存
            History::create([
                'track_id' => $randomTrackData['id'],
                'spotify_uris' => $randomTrackData['uri'],
                'played_at' => Carbon::parse($randomTrack['played_at']),
                'popularity' => $randomTrackData['popularity'],
            ]);
        }

        // 5. History モデルから指定期間内のデータを取得
        $historyData = History::inPeriod($startDate, $endDate)
            ->orderBy('popularity', 'desc')
            ->orderBy('played_at')
            ->get();
        // ok \Log::info('History Data: ' . json_encode($historyData));
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

        $addcount = 10;
        $trackUris = [];
        for ($i = 0; $i < $addcount; $i++) {
            $trackUri = History::inRandomOrder()->value('spotify_uris');
            array_push($trackUris, $trackUri);
        }

        $this->addPlaylistItems($spotifyPlaylistId, $user, $trackUris);

        // for ($i = 0; $i < $addcount; $i++) {
        // $this->addPlaylistItems($spotifyPlaylistId, $user);
        // }

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

    private function getMyRecentTracksWithTimestamps($user, $startDate, $endDate)
    {
        try {
            // Spotify APIエンドポイント
            $endpoint = 'https://api.spotify.com/v1/me/player/recently-played';

            // Spotify APIリクエストに必要なヘッダー
            $headers = [
                'Authorization' => 'Bearer ' . $user->access_token,
            ];

            // Spotify APIに最近再生したトラックを取得するリクエストを送信
            $response = Http::withHeaders($headers)->get($endpoint)->throw();
            // レスポンスが JSON データを含む場合
            $responseData = $response->json();

            // レスポンスからトラックのデータを取得
            $tracks = isset($responseData['items']) ? $responseData['items'] : [];

            // フィルタリング
            $filteredTracks = array_filter($tracks, function ($track) use ($startDate, $endDate) {
                $playedAt = Carbon::parse($track['played_at']);
                return $playedAt->between($startDate, $endDate);
            });
            return $filteredTracks;
        } catch (\Exception $e) {
            // エラーハンドリング - レスポンスがエラーを含む場合
            Log::error('Failed to get recently played tracks. Error: ' . $e->getMessage());
            return [];
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
            'start_date' => '2024-01-18',
            'end_date' => '2024-01-18',
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

    public function addPlaylistItems($playlistId, $user, $trackUris)
    {
        // 最新の履歴レコードを取得
        // $uri = History::inRandomOrder()->value('spotify_uris');
        // 配列に変換
        // $uris = [$uri];
        //$uris = History::pluck('spotify_uris')->toArray();
        // Spotify API エンドポイント
        $endpoint = "https://api.spotify.com/v1/playlists/{$playlistId}/tracks";

        // Spotify API リクエストに必要なヘッダー
        $headers = [
            'Authorization' => 'Bearer ' . $user->access_token,
            'Content-Type' => 'application/json',
        ];

        $data = [
            'uris' => $trackUris
        ];

        // Spotify API にプレイリストを公開にするリクエストを送信
        $response = Http::withHeaders($headers)->post($endpoint, $data);

        // エラーハンドリング - レスポンスがエラーを含む場合
        if ($response->failed()) {
            Log::error('Failed to add playlist items on Spotify. Response: ' . $response->body());
            // 適切なエラーレスポンスを返すか、例外をスローするなど
            return false;
        }

        return;
    }

}
