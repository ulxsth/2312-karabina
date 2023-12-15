<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use \Illuminate\Http\RedirectResponse;
use GuzzleHttp\Client;

class AuthController extends Controller {
    /**
     * ユーザをSpotify認証ページへリダイレクトさせる。
     *
     * @return RedirectResponse
     */

    public function redirectToSpotify()
    {
        // ユーザに認証を要求するためのリダイレクトURLを生成
        $auth_url = $this->authorize_url .'?'.http_build_query([
            'client_id' => $this->client_id,
            'redirect_uri' => $this->redirect_uri,
            'response_type' => 'code',
            'scope' => '', // 必要なスコープを追加
        ]);

        // Guzzleを使用してリダイレクト
        $client = new Client();
        $client->get($auth_url);

        exit();
    }

    /**
     * Spotifyからのコールバックを処理する。
     *
     * @param Request $request
     * @return void
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
        /*　エラー処理あり
        if ($response->successful()) {
        $token = $response->json()['access_token'];
        } else {
        return redirect()->back()->with('error', 'Failed to retrieve Spotify token');
        }
        */
        $token = $response->json()['access_token'];

        // Spotify APIへのユーザ情報取得
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
}
