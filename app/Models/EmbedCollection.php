<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmbedCollection extends Model
{
    use HasFactory;

    protected $fillable = [
        'notion_integration_id',
        "name"
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function notionIntegration(): BelongsTo
    {
        return $this->belongsTo(NotionIntegration::class);
    }
}
