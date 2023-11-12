<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Chat extends Model
{
    use HasFactory;

    protected $fillable = [
        "chat_user_id",
        "embed_collection_id",
        "title"
    ];

    public function chatUser(): BelongsTo
    {
        return $this->belongsTo(User::class, "chat_user_id", "id");
    }

    public function embedCollection(): BelongsTo
    {
        return $this->belongsTo(EmbedCollection::class);
    }
}
