<?php

namespace App\Models;
use Illuminate\Support\Facades\Log;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Carbon\Carbon;

class SpotifyUser extends Model implements Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;
    protected $primaryKey = 'spotify_id';

    /**
     * モデルのIDを自動増分するか
     * @var bool
     */
    public $incrementing = false;

    /**
     * 自動増分IDのデータ型
     * @var string
     */
    protected $keyType = 'string';

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

    protected $casts = [
        'spotify_id' => 'string',
        'access_token' => 'string',
        'refresh_token' => 'string',
        'token_updated_at' => 'datetime',
        'token_expire' => 'datetime',
    ];

    /**
     * トークンの有効期限が切れているかどうかを判定する。
     * @return bool
     */
    public function isTokenExpired()
    {
        Log::info('Checking token expiration for user: ' . $this->spotify_id);
        return Carbon::now()->gt($this->token_expire);
    }

    public function playlists()
    {
        return $this->hasMany(Playlist::class, 'user_id', 'spotify_id');
    }

    /*
     * Laravelの認証にログインする為に必要なデータ
     */

    // Authenticatable インターフェースのメソッドを実装
    public function getAuthIdentifierName()
    {
        return 'spotify_id';
    }
    public function getAuthPassword()
    {
        // パスワードが存在しない場合、空の文字列を返す
        return '';
    }

    public function getAuthIdentifier()
    {
        return $this->attributes['spotify_id'];
    }
    public function getRememberToken()
    {
        return $this->attributes['remember_token'];
    }

    public function setRememberToken($value)
    {
        $this->attributes('remember_token', $value);
    }

    public function getRememberTokenName()
    {
        return 'remember_token';
    }
}
