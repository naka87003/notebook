<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'comment'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * 作成したTagをまとめて取得
     */
    public function tags(): HasMany
    {
        return $this->hasMany(Tag::class);
    }

    /**
     * 作成したNoteをまとめて取得
     */
    public function notes(): HasMany
    {
        return $this->hasMany(Note::class);
    }

    /**
     *  UserのLikesを取得
     */
    public function likes(): HasMany
    {
        return $this->hasMany(Like::class);
    }

    /**
     * そのUser"が"followしているUsersをまとめて取得
     */
    public function followees(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'follows', 'follower_id', 'followee_id');
    }

    /**
     * そのUser"を"followしているUsersをまとめて取得
     */
    public function followers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'follows', 'followee_id', 'follower_id');
    }

    /**
     *  Userのメール通知設定を取得
     */
    public function emailPreferences(): HasMany
    {
        return $this->hasMany(EmailPreference::class);
    }
}
