<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class SpotifyUser extends Model
{
    use HasApiTokens, HasFactory, Notifiable;
    protected $primaryKey = 'spotify_id';

    /**
     * Eloquentモデルに保存できる属性を指定
     * ※ただし、指定された属性以外はcreateやupdateメソッドで保存されないので
     * 　必要な属性を全てここに配置する
     */
    protected $fillable = [
        'spotify_id',
        'access_token',
        'refresh_token',
        'token_updated_at',
        'token_expire',
    ];

    /**
     * 空の状態の場合、属性をJSONシリアライズから除外するために使用されるっぽい
     * なので、モデル内の特定の属性をJSONから除外したい場合はここにその属性を指定してあげる
     * 一応、空でも問題はなさそう
     */
    protected $hidden = [
    ];

    /**
     * 属性がdatetimeタイプなのが前提
     */
    protected $casts = [
        'token_updated_at' => 'datetime',
        'token_expire' => 'datetime',
    ];

    public function tokenExpired()
    {
        return now()->gt($this->token_expire);
    }
}
