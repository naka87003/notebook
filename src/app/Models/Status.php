<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Status extends Model
{
    use HasFactory;

    /**
     * Statusに所属するNoteをまとめて取得
     */
    public function notes(): HasMany
    {
        return $this->hasMany(Note::class);
    }
}
