<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Track extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'artist']; // データベースに保存する際に入力を許可するフィールド

    public function playlists()
    {
        return $this->belongsToMany(Playlist::class);
    }
}
