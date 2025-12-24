<?php

namespace App\Models;

use App\Enums\ContentCategory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Post extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'title',
        'content',
        'category',
        'is_anonymous',
        'is_approved',
        'approved_at',
        'approved_by',
        'likes_count',
        'comments_count',
    ];

    protected function casts(): array
    {
        return [
            'category' => ContentCategory::class,
            'is_anonymous' => 'boolean',
            'is_approved' => 'boolean',
            'approved_at' => 'datetime',
            'likes_count' => 'integer',
            'comments_count' => 'integer',
        ];
    }

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function comments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

    public function likes(): MorphMany
    {
        return $this->morphMany(Like::class, 'likeable');
    }

    // Scopes
    public function scopeApproved($query)
    {
        return $query->where('is_approved', true);
    }

    public function scopePending($query)
    {
        return $query->where('is_approved', false);
    }

    public function scopeByCategory($query, ContentCategory $category)
    {
        return $query->where('category', $category);
    }

    // Accessors
    public function getAuthorNameAttribute(): string
    {
        return $this->is_anonymous ? 'Anonymous' : $this->user->name;
    }
}
