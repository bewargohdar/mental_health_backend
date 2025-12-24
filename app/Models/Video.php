<?php

namespace App\Models;

use App\Enums\ContentCategory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Video extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'url',
        'thumbnail',
        'duration',
        'category',
        'author_id',
        'is_published',
        'views_count',
        'tags',
    ];

    protected function casts(): array
    {
        return [
            'category' => ContentCategory::class,
            'is_published' => 'boolean',
            'duration' => 'integer',
            'views_count' => 'integer',
            'tags' => 'array',
        ];
    }

    // Relationships
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function bookmarks(): MorphMany
    {
        return $this->morphMany(Bookmark::class, 'bookmarkable');
    }

    // Scopes
    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    public function scopeByCategory($query, ContentCategory $category)
    {
        return $query->where('category', $category);
    }

    // Accessors
    public function getDurationFormattedAttribute(): string
    {
        $minutes = floor($this->duration / 60);
        $seconds = $this->duration % 60;
        return sprintf('%d:%02d', $minutes, $seconds);
    }
}
