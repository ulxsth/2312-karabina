<?php
namespace App\Http\Controllers;

use App\Http\Controllers\AuthController;
use Illuminate\Http\Request;
use \Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Http;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Client;

class AuthController extends Controller {
    private $client_id;
    private $client_secret;
    private $redirect_uri;
    private $authorize_url = 'https://accounts.spotify.com/authorize';

    public function __construct() {
        $this->client_id = getenv('SPOTIFY_CLIENT_ID');
        $this->client_secret = getenv('SPOTIFY_CLIENT_SECRET');
        $this->redirect_uri = getenv('SPOTIFY_REDIRECT_URI');
    }

    /**
     * ユーザをSpotify認証ページへリダイレクトさせる。
     * @return void
     */
    public function redirectToSpotify() {
        // ユーザに認証を要求するためのリダイレクトURLを生成する
        $auth_url = $this->authorize_url . '?' . http_build_query([
            'client_id' => $this->client_id,
            'redirect_uri' => $this->redirect_uri,
            'response_type' => 'code',
            'scope' => 'user-read-top', // 必要なスコープがあればを追加
        ]);

        /* Guzzleを使用してリダイレクトする
        $client = new Client();
        $client->get($auth_url);
        exit();
        */
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
        // Spotifyからのコールバックで受け取った認証コードを使ってトークンを取得する
        $response = Http::asForm()->post('https://accounts.spotify.com/api/token', [
            'grant_type' => 'authorization_code',
            'code' => $request->code,
            'redirect_uri' => env('SPOTIFY_REDIRECT_URI'),
            'client_id' => env('SPOTIFY_CLIENT_ID'),
            'client_secret' => env('SPOTIFY_CLIENT_SECRET'),
        ]);
        /*　エラー処理あり*/
        if ($response->successful()) {
        $token = $response->json()['access_token'];
        } else {
        return redirect()->back()->with('error', 'Failed to retrieve Spotify token');
        }
        
        $token = $response->json()['access_token'];

        $client = new Client();

        $form_params = [
            'grant_type' => 'client_credentials',
            'client_id' => 'client_id',
            'client_secret' => 'client_secret'
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
        \Log::debug($response->getBody());//一旦ログで出力してみる

        // Spotify APIへのユーザ情報取得の例
        $userInfoResponse = $client->get('https://api.spotify.com/v1/me', [
            'headers' => [
                'Authorization' => 'Bearer'. $token,
            ],
        ]);

        // 取得したユーザ情報
        $userInfo = json_decode($userInfoResponse->getBody(), true);
        \Log::debug($response->getBody());//一旦ログで出力してみる

        return redirect()->to('/home');
    }

    public function requestSpotifyTokenByCode($code)
    {
        try{
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

        //URLからアクセストークンの抽出を行う
        $responseData = json_decode($response->getBody(), true);
        $accessToken = $responseData['access_token'];

        return $accessToken;
        }catch (RequestException $e){
            // エラーが発生した場合は null を返す
            return null;
        }
    }

    public function requestSpotifyTokenByRefreshToken($refreshToken){

        try{
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
        } catch (RequestException $e){
            // エラーが発生した場合は null を返す
            return null;
        }
    }
}
