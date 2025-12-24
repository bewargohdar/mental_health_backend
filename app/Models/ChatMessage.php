<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ChatMessage extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'chat_room_id',
        'user_id',
        'message',
        'is_anonymous',
        'attachment',
    ];

    protected function casts(): array
    {
        return [
            'is_anonymous' => 'boolean',
            'attachment' => 'array',
        ];
    }

    // Relationships
    public function chatRoom(): BelongsTo
    {
        return $this->belongsTo(ChatRoom::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Accessors
    public function getSenderNameAttribute(): string
    {
        return $this->is_anonymous ? 'Anonymous' : $this->user->name;
    }
}
