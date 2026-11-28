<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** An earlier state of a post, kept so a change can be undone. */
class PostRevision extends Model
{
    protected $fillable = ['post_id', 'user_id', 'content_hash', 'snapshot'];

    protected $casts = ['snapshot' => 'array'];

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
