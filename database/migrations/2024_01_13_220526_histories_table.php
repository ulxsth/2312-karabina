<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('histories', function (Blueprint $table) {
            $table->id();
            $table->string('track_id'); // Spotifyからの文字列であると仮定
            $table->integer('popularity');
            $table->string('spotify_uris');
            $table->dateTime('played_at')->nullable();
            $table->dateTime('start_date')->nullable()->default(null);
            $table->dateTime('end_date')->nullable()->default(null);
            $table->timestamps();
        });

        // 主キーの名前を変更
        Schema::table('histories', function (Blueprint $table) {
            $table->renameColumn('id', 'histories_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // upmethodの変更をロールバックするため
        Schema::table('histories', function (Blueprint $table) {
            $table->renameColumn('histories_id', 'id');
        });

        Schema::dropIfExists('histories');
    }
};
