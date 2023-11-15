<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class NotionToken extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'token',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
