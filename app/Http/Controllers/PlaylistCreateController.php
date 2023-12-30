<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Playlist;
use App\Models\History;
use App\Models\SpotifyUser;

class PlaylistCreateController extends Controller
{

    public function createPlaylist(Request $request)
    {
        // 期間を指定して履歴データを取得する
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        // 次に、期間を取得して履歴データを取得する
        $historyData = History::withinPeriod($startDate, $endDate)
            ->orderBy('play_count', 'desc')  // 再生回数が多い順にソート
            ->orderBy('played_at')          // 再生時期が古い順にソート
            ->get();

        // ユーザーのトークンを確認して、期限切れなら更新
        $user = SpotifyUser::find(auth()->id());
        if ($user->tokenExpired()) {
            // トークンを更新する処理
            app(AuthController::class)->refreshToken($user);
        }

        // 空のプレイリストを作成する
        $playlist = new Playlist();
        $playlist->user_id = auth()->id();
        $playlist->save();

        // 曲をプレイリストに追加する
        foreach ($historyData as $history) {
            $playlist->tracks()->attach($history->track_id);
        }

        // ユーザが決めたプレイリスト名を受け取る
        $playlistName = $request->input('playlist_name');

        // (公開する場合は)プレイリストをpublicにする処理をここに実装する
        return response()->json(['message' => 'Playlist created successfully'], 200);
    }

}
