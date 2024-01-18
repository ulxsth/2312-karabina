<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // playlists テーブルが存在していない場合のみ作成する
        if (!Schema::hasTable('playlists')) {
            Schema::create('playlists', function (Blueprint $table) {
                $table->id();
                $table->string('user_id');
                $table->foreign('user_id')->references('spotify_id')->on('spotify_users');
                $table->string('name');
                $table->boolean('public')->default(false);
                $table->timestamps();
            });
        }

        // 主キーの名前を変更
        Schema::table('playlists', function (Blueprint $table) {
            $table->renameColumn('id', 'playlist_id');
        });

        // playlist_track テーブルが存在していない場合のみ作成する
        if (!Schema::hasTable('playlist_track')) {
            Schema::create('playlist_track', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('playlist_id');
                $table->text('track_id');
                // 他に必要なカラムがあれば追加

                $table->timestamps();

                $table->foreign('playlist_id')->references('playlist_id')->on('playlists')->onDelete('cascade');
                $table->foreign('track_id')->references('id')->on('tracks')->onDelete('cascade');
            });
        }

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('playlists');
    }
};
