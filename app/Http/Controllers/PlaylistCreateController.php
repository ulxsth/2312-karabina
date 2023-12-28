<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Playlist;
use App\Models\History;
use App\Models\SpotifyUser;

class PlaylistCreateController extends Controller
{
    // PlaylistController.php

public function createPlaylist(Request $request)
{
    // 期間を指定して履歴データを取得する
    $startDate = $request->input('start_date');
    $endDate = $request->input('end_date');
    $historyData = History::withinPeriod($startDate, $endDate)->orderBy('play_count')->orderBy('playback_date')->get();

    // ユーザーのトークンを確認して、期限切れなら更新
    $user = SpotifyUser::find(auth()->id());
    if ($user->tokenExpired()) {
        // 取り敢えずトークンを更新する処理を実装する
    }

    // 空のプレイリストを作成する
    $playlist = new Playlist();
    $playlist->user_id = auth()->id();
    $playlist->save();

    // 曲をプレイリストに追加する
    foreach ($historyData as $history) {
        $playlist->songs()->attach($history->song_id);
    }

    // （公開する場合は）プレイリストをpublicにする処理をここに実装する

    return response()->json(['message' => 'Playlist created successfully'], 200);
}

}
