<?php

namespace App\Http\Controllers;

use App\Models\SpotifyUser;
use Illuminate\Http\Request;

class SpotifyUserController extends Controller
{
    /**
     * ユーザー作成機能（C）
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
     * すべてのユーザーを取得する
     * @return \Illuminate\Http\JsonResponse
     */
    public function all()
    {
        return response()->json(SpotifyUser::all());
    }

    /**
     * SpotifyIDによるユーザー情報の読込機能（R）
     */
    public function read($spotifyId)
    {
        // 指定されたSpotifyIDのユーザー情報をデータベースから取得
        $user = SpotifyUser::where('spotify_id', $spotifyId)->first();

        // ユーザーが存在する場合はtrueを、存在しない場合はfalseを返す
        return $user !== null;
    }

    /**
     * ユーザー情報更新機能（U）
     */
    public function update(Request $request, $spotifyId)
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
