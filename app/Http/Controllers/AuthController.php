<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use \Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Http;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Client;

class AuthController extends Controller
{
    private $client_id;
    private $client_secret;
    private $redirect_uri;
    private $authorize_url = 'https://accounts.spotify.com/authorize';

    public function __construct()
    {
        $this->client_id = getenv('SPOTIFY_CLIENT_ID');
        $this->client_secret = getenv('SPOTIFY_CLIENT_SECRET');
        $this->redirect_uri = getenv('SPOTIFY_REDIRECT_URI');
    }

    /**
     * ユーザをSpotify認証ページへリダイレクトさせる。
     * @return void
     */
    public function redirectToSpotify()
    {
        // 必要なスコープがあれば配列で定義(現：tracks取得可能)
        $scopes = [
            'user-read-private',
            'user-read-email',
            'user-library-read',
            'playlist-modify-private',
        ];

        // ユーザに認証を要求するためのリダイレクトURLを生成する
        $auth_url = $this->authorize_url . '?' . http_build_query([
            'client_id' => $this->client_id,
            'redirect_uri' => $this->redirect_uri,
            'response_type' => 'code',
            'scope' => implode(' ', $scopes), // 配列を半角スペースで連結
        ]);

        return redirect()->away($auth_url);
    }

    /**
     * Spotifyからのコールバックを処理する。
     *
     * @param Request $request
     * @return RedirectResponse
     */
    public function handleSpotifyCallback(Request $request)
    {
        try {
            // Spotifyからのコールバックで受け取った認証コードを使ってトークンを取得する
            $response = Http::asForm()->post('https://accounts.spotify.com/api/token', [
                'grant_type' => 'authorization_code',
                'code' => $request->code,
                'redirect_uri' => env('SPOTIFY_REDIRECT_URI'),
                'client_id' => env('SPOTIFY_CLIENT_ID'),
                'client_secret' => env('SPOTIFY_CLIENT_SECRET'),
            ]);

            // エラー処理
            if ($response->successful()) {
                $tokenData = $response->json();
                $accessToken = $tokenData['access_token'];
                $refreshToken = $tokenData['refresh_token'];


                // ユーザー情報を取得
                $userResponse = Http::withHeaders(['Authorization' => 'Bearer ' . $accessToken])->get('https://api.spotify.com/v1/me');

                // ユーザー情報が正常に取得できた場合
                if ($userResponse->successful()) {
                    $userData = $userResponse->json();
                    \Log::info('Spotify User Data:', $userData); // デバッグログを追加
                    $spotifyId = $userData['id'];


                    // データベースに新規登録または更新
                    $spotifyUser = \App\Models\SpotifyUser::updateOrCreate(
                        ['spotify_id' => $spotifyId],
                        [
                            'access_token' => $accessToken,
                            'refresh_token' => $refreshToken,
                            'token_updated_at' => now(),
                            'token_expire' => now()->addHour(),
                        ]
                    );

                    // 保存する前に ID がセットされていない場合のみ保存
                    if (!$spotifyUser->exists) {
                        $spotifyUser->save();
                    }

                    // UserControllerのインスタンスを取得
                    $userController = app(UserController::class);

                    // インスタンスを作成
                    $request = new \Illuminate\Http\Request([
                        'spotify_id' => $spotifyUser->spotify_id,
                        'access_token' => $spotifyUser->access_token,
                        'refresh_token' => $spotifyUser->refresh_token,
                    ]);

                    // UserController::create メソッドを呼び出す
                    return $userController->create($request);
                } else {
                    // ユーザー情報の取得に失敗した場合
                    return response()->json(['error' => 'Failed to retrieve Spotify user information'], 500);
                }

            } else {
                // Spotifyトークンの取得に失敗した場合
                return response()->json(['error' => 'Failed to retrieve Spotify token'], 500);
            }

        } catch (RequestException $e) {
            \Log::error('Spotify Token Request Error: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to retrieve Spotify token'], 500);
        }

        $client = new Client();

        $form_params = [
            'grant_type' => 'client_credentials',
            'client_id' => env('SPOTIFY_CLIENT_ID'),
            'client_secret' => env('SPOTIFY_CLIENT_SECRET')
        ];

        $headers = [
            'Content-Type' => 'application/x-www-form-urlencoded'
        ];

        $options = [
            'form_params' => $form_params,
            'headers' => $headers
        ];

        $response = $client->post('https://accounts.spotify.com/api/token', $options);

        // 取得したトークンを使用し、Spotify APIへのリクエストを行う
        $token = json_decode($response->getBody(), true)['access_token'];

        return redirect()->to('/home');
    }

    /**
     * Spotifyからのコードを使用してトークンを取得する。
     *
     * @param string $code
     * @return string|null
     */
    private function requestSpotifyTokenByCode($code)
    {
        try {
            $client = new Client();

            $form_params = [
                'grant_type' => 'authorization_code',
                'code' => $code,
                'redirect_uri' => env('SPOTIFY_REDIRECT_URI'),
                'client_id' => env('SPOTIFY_CLIENT_ID'),
                'client_secret' => env('SPOTIFY_CLIENT_SECRET'),
            ];

            $headers = [
                'Content-Type' => 'application/x-www-form-urlencoded',
            ];

            $options = [
                'form_params' => $form_params,
                'headers' => $headers,
            ];

            $$response = $this->httpClient->post('https://accounts.spotify.com/api/token', $options);

            if ($response->getStatusCode() == 200) {
                $responseData = json_decode($response->getBody(), true);
                $accessToken = $responseData['access_token'];
                return $accessToken;
            } else {
                \Log::error('Spotify Token Request Error: Unexpected status code ' . $response->getStatusCode());
                return null;
            }
        } catch (RequestException $e) {
            \Log::error('Spotify Token Request Error: ' . $e->getMessage());
            return null;
        }
    }

    public function requestSpotifyTokenByRefreshToken($refreshToken)
    {

        try {
            $client = new Client();

            $form_params = [
                'grant_type' => 'refresh_token',
                'refresh_token' => $refreshToken,
                'client_id' => env('SPOTIFY_CLIENT_ID'),
                'client_secret' => env('SPOTIFY_CLIENT_SECRET'),
            ];

            $headers = [
                'Content-Type' => 'application/x-www-form-urlencoded',
            ];

            $options = [
                'form_params' => $form_params,
                'headers' => $headers,
            ];

            $response = $client->post('https://accounts.spotify.com/api/token', $options);

            //URLからアクセストークンの抽出を行う
            $responseData = json_decode($response->getBody(), true);
            $accessToken = $responseData['access_token'];

            return $accessToken;
        } catch (RequestException $e) {
            // エラーが発生した場合は null を返す
            return null;
        }
    }
}
