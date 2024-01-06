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
        Schema::create('spotify_users', function (Blueprint $table) {
            $table->string('spotify_id')->primary();
            $table->text('access_token'); // 変更: text型に変更
            $table->text('refresh_token'); // 変更: text型に変更
            $table->timestamp('token_updated_at');
            $table->timestamp('token_expire');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('spotify_users');
    }
};
