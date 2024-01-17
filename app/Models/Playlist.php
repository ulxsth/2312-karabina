<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Playlist extends Model
{
    use HasFactory;

    protected $primaryKey = 'playlist_id';
    public function getIsPublicAttribute()
    {
        return $this->attributes['is_public'] == 1;
    }

    public function user()
    {
        return $this->belongsTo(SpotifyUser::class, 'user_id', 'spotify_id');
    }

    public function tracks()
    {
        return $this->belongsToMany(Track::class, 'playlist_track', 'playlist_id', 'track_id')->withTimestamps();
    }

}
