<?php

namespace App\Models;

use Database\Factories\PreviewMessageFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One message of the review chat on a project's preview: the client's, or noehassiel's AI reply.
 */
class PreviewMessage extends Model
{
    /** @use HasFactory<PreviewMessageFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'role',
        'content',
        'page_url',
        'pending_quote',
    ];

    protected function casts(): array
    {
        return [
            'pending_quote' => 'array',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isFromClient(): bool
    {
        return $this->role === 'user';
    }

    /** What the AI sees: the client's words with the page they were on, or the reply as written. */
    public function forAgent(): string
    {
        return $this->isFromClient() ? "Page: {$this->page_url}\n\nClient: {$this->content}" : $this->content;
    }
}
