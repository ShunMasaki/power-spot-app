<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * 登録可能な属性
     */
    protected $fillable = [
        'cognito_sub',
        'name',
        'email',
        'nickname',
    ];

    /**
     * 非表示にする属性（Cognito連携なので password, remember_token は不要）
     */
    protected $hidden = [];

    /**
     * 属性の型変換
     */
    protected function casts(): array
    {
        return [];
    }

    /**
     * ───────────────
     * リレーション
     * ───────────────
     */

    public function favorites()
    {
        return $this->hasMany(Favorite::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function goshuinImages()
    {
        return $this->hasMany(GoshuinImage::class);
    }

    public function omikujiImages()
    {
        return $this->hasMany(OmikujiImage::class);
    }

    public function contacts()
    {
        return $this->hasMany(Contact::class);
    }
}
