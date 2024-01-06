<?php

namespace App\Http\Controllers;

use App\Models\History;
use App\Models\SpotifyUser;
use Http;

class HistoryController extends Controller
{
    private $authController = null;

    public function __construct() {
        $this->authController = new AuthController();
    }

    /**
     * ユーザーの履歴をSpotifyから取得し、DBに保存する。
     * @param SpotifyUser $user
     * @return boolean 保存処理の可否
     */
    public function fetch($user) {
        $this->authController->refreshToken($user);

        $timestamp = strtotime('-5 minutes');
        $response = Http::withToken($user->access_token)->get('https://api.spotify.com/v1/me/player/recently-played', [
            'after' => $timestamp,
        ]);

        if ($response->failed()) {
            return false;
        }

        $items = $response->json()['items'];
        foreach ($items as $item) {
            $history = new History();
            $history->user_spotify_id = $user->spotify_id;
            $history->played_at = $item['played_at'];
            $history->track_spotify_id = $item['track']['id'];
            $history->save();
        }

        return true;
    }

    /**
     * ユーザーの履歴を取得する。
     * @param string $user_id ユーザーID。
     * @param int $limit 取得する上限数。デフォルトは50。
     * @return \Illuminate\Support\Collection
     */
    public function get($user_id, $limit = 50)
    {
        return History::where('user_spotify_id', $user_id)->limit($limit)->get();
    }
}
