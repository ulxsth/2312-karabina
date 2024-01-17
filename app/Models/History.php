<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class History extends Model
{
    use HasFactory;
    protected $primaryKey = 'histories_id';
    protected $fillable = ['track_id', 'played_at', 'play_count'];

    // 期間の絞り込みを行うスコープを定義
    public function scopeInPeriod($query, $startDate, $endDate)
    {
        return $query->whereBetween('played_at', [$startDate, $endDate]);
    }

}
