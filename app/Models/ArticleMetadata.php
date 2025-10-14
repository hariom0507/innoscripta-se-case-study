<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ArticleMetadata extends Model
{
    protected $fillable = ['article_id','metadata'];
    protected $casts = ['metadata' => 'array'];

    public function article(): BelongsTo
    {
        return $this->belongsTo(Article::class);
    }
}
