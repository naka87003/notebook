<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Reply extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $guarded = [];


    /**
     *  replyしたuserを取得
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     *  reply宛のuserを取得
     */
    public function addressee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reply_to');
    }

    /**
     *  replyされたcommentを取得
     */
    public function comment(): BelongsTo
    {
        return $this->belongsTo(Comment::class);
    }
}
