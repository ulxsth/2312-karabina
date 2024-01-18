<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Controllers\SpotifyUserController;
use App\Models\SpotifyUser;
use Illuminate\Http\Request;
use \Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Http;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Client;

class AuthController extends Controller
{

    private $authorize_url = 'https://accounts.spotify.com/authorize';
    private $client_id;
    private $client_secret;
    private $redirect_uri;

    public function __construct()
    {
        $this->client_id = getenv('SPOTIFY_CLIENT_ID');
        $this->client_secret = getenv('SPOTIFY_CLIENT_SECRET');
        $this->redirect_uri = getenv('SPOTIFY_REDIRECT_URI');
    }

    /**
     * ユーザをSpotify認証ページへリダイレクトさせる。
     * @return RedirectResponse
     */
    public function redirectToSpotify()
    {
        // 必要なスコープがあれば配列で定義(現：tracks取得可能)
        $scopes = [
            'user-read-private',
            'user-read-email',
            'user-library-read',
            'playlist-modify-public',
            'playlist-modify-private',
            'user-read-recently-played',
            'user-read-recently-played'
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

            // Spotifyトークンの取得に失敗した場合
            if (!$response->successful()) {
                return response()->json(['error' => 'Failed to retrieve Spotify token'], 500);
            }

            $tokenData = $response->json();
            $accessToken = $tokenData['access_token'];
            $refreshToken = $tokenData['refresh_token'];

            // ユーザー情報を取得
            $userResponse = Http::withToken($accessToken)->get('https://api.spotify.com/v1/me');

            // ユーザー情報の取得に失敗した場合
            if (!$userResponse->successful()) {
                $errorMessage = $userResponse->body();
                $statusCode = $userResponse->status();
                return response()->json(['error' => 'Failed to retrieve Spotify user information', 'message' => $errorMessage, 'status' => $statusCode], 500);
            }

            $userData = $userResponse->json();
            $spotifyId = $userData['id'];
            \Log::info('Spotify User Data:', $userData);

            // 存在しているかどうかの確認
            $user = SpotifyUser::where('spotify_id', $spotifyId)->first();
            $userController = app(SpotifyUserController::class);

            $spotifyIdToDelete = '0';
            SpotifyUser::where('spotify_id', $spotifyIdToDelete)->delete();

            // 既存ユーザーが存在しない場合は新規作成
            if (!$user) {
                $spotifyUser = SpotifyUser::create([
                    'spotify_id' => $spotifyId,
                    'access_token' => $accessToken,
                    'refresh_token' => $refreshToken,
                    'token_updated_at' => now(),
                    'token_expire' => now()->addHour(),
                ]);

                // インスタンスを作成
                $request = new Request([
                    'spotify_id' => $spotifyUser->spotify_id,
                    'access_token' => $spotifyUser->access_token,
                    'refresh_token' => $spotifyUser->refresh_token,
                ]);

                // 既存のユーザーが存在しない場合のみUserController::createメソッドを呼び出す
                if (!$userController->read($spotifyUser->spotify_id)) {
                    $userController->create($request);
                }

                return redirect('/home');
            }

            // 既存ユーザーが存在する場合は更新する
            $user->update([
                'access_token' => $accessToken,
                'refresh_token' => $refreshToken,
                'token_updated_at' => now(),
                'token_expire' => now()->addHour(),
            ]);

            // インスタンスを作成
            $request = new Request([
                'spotify_id' => $user->spotify_id,
                'access_token' => $user->access_token,
                'refresh_token' => $user->refresh_token,
            ]);

            $userController->update($request, $user->spotify_id);

            return redirect('/home');

        } catch (RequestException $e) {
            \Log::error('Spotify Token Request Error: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to retrieve Spotify token'], 500);
        }
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

            $response = $client->post('https://accounts.spotify.com/api/token', $options);

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

    /**
     * Spotifyからのリフレッシュトークンを使用してトークンを取得する。
     *
     * @param string $refreshToken
     * @return string|null
     * @throws RequestException
     */
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

    /**
     * ユーザーのトークンを更新する。
     *
     * @param SpotifyUser $user
     * @return void
     */
    public function refreshToken(SpotifyUser $user)
    {
        try {
            // トークンの更新処理を実装
            $newToken = $this->requestSpotifyTokenByRefreshToken($user->refresh_token);
            $user->update(['access_token' => $newToken]);
        } catch (\Exception $e) {
            \Log::error('Failed to refresh token: ' . $e->getMessage());
        }
    }
}
