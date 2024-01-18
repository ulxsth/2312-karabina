<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class History extends Model
{
    use HasFactory;

    protected $primaryKey = 'histories_id';

    // 変更: モデルのfillableプロパティに変更したカラムを追加
    protected $fillable = ['track_id','spotify_uris','played_at', 'start_date', 'end_date', 'popularity'];

    // 期間の絞り込みを行うスコープを定義
    public function scopeInPeriod($query, $startDate, $endDate)
    {
        // 変更: カラム名を変更
        return $query->whereBetween('played_at', [$startDate, $endDate])
            ->orWhereBetween('start_date', [$startDate, $endDate])
            ->orWhereBetween('end_date', [$startDate, $endDate]);
    }
}
