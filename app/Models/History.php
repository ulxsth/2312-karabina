<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class History extends Model
{
    use HasFactory;
    protected $fillable = ['user_spotify_id', 'track_spotify_id', 'played_at'];
    protected $casts = ['played_at' => 'datetime'];
}
