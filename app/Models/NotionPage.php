<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class NotionPage extends Model
{
    use HasFactory;

    protected $fillable = [
        'embed_collection_id',
        'link'
    ];

    public function embedCollection(): BelongsTo
    {
        return $this->belongsTo(EmbedCollection::class);
    }
}
