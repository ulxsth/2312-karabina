<?php

namespace App\Http\Controllers;

use App\Models\SpotifyUser;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * ユーザーを登録する。
     * @param Request $request
     * @return JsonResponse
     */
    public function create(Request $request)
    {
        //新規ユーザー情報をデータベースに格納
        $user = SpotifyUser::create([
            'spotify_id' => $request->input('spotify_id'),
            'access_token' => $request->input('access_token'),
            'refresh_token' => $request->input('refresh_token'),
            'token_updated_at' => now()->timestamp,
            'token_expire' => now()->addHour()->timestamp,
        ]);

        //新規ユーザー情報を返す
        return response()->json($user, 201);
    }

    /**
     * SpotifyIDを指定してユーザー情報を取得する。
     * @param $spotifyId
     * @return JsonResponse
     */
    public function read($spotifyId)
    {
        // 指定されたSpotifyIDのユーザー情報をデータベースから取得
        $user = SpotifyUser::where('spotify_id', $spotifyId)->first();

        // もしも、ユーザーが見つからない場合はエラーを返す
        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }
        // 見つかった場合は、ユーザー情報を返す
        return response()->json($user, 200);
    }

    /**
     * ユーザー情報を更新する。
     * @param Request $request
     * @param string $spotifyId
     * @return JsonResponse
     */
    public function update(Request $request, string $spotifyId)
    {
        // 指定されたSpotifyIDのユーザー情報をデータベースから取得
        $user = SpotifyUser::where('spotify_id', $spotifyId)->first();

        // もしも、ユーザーが見つからない場合はエラーを返す
        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        //ユーザー情報を更新
        $user->update([
            'access_token' => $request->input('access_token'),
            'token_updated_at' => now()->timestamp,
            'token_expire' => now()->addHour()->timestamp,
        ]);

        // 更新後のユーザー情報を返す
        return response()->json($user, 200);
    }
}
