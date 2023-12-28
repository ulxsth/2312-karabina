<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Playlist extends Model
{
    use HasFactory;
    public function getIsPublicAttribute()
    {
        return $this->attributes['is_public'] == 1;
    }

}
