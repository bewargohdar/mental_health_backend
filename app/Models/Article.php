<?php

namespace App\Models;

use App\Enums\ContentCategory;
use App\Models\Traits\SerializesLocalTimezone;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Article extends Model
{
    use HasFactory, SoftDeletes, SerializesLocalTimezone;

    protected $appends = ['featured_image_url'];

    protected $fillable = [
        'title',
        'slug',
        'content',
        'excerpt',
        'category',
        'author_id',
        'featured_image',
        'reading_time',
        'is_published',
        'published_at',
        'views_count',
        'tags',
    ];

    protected function casts(): array
    {
        return [
            'category' => ContentCategory::class,
            'is_published' => 'boolean',
            'published_at' => 'datetime',
            'reading_time' => 'integer',
            'views_count' => 'integer',
            'tags' => 'array',
        ];
    }

    // Relationships
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function comments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

    public function bookmarks(): MorphMany
    {
        return $this->morphMany(Bookmark::class, 'bookmarkable');
    }

    // Scopes
    public function scopePublished($query)
    {
        return $query->where('is_published', true)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    public function scopeByCategory($query, ContentCategory $category)
    {
        return $query->where('category', $category);
    }

    // Boot
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($article) {
            if (empty($article->slug)) {
                $article->slug = str($article->title)->slug();
            }
        });
    }

    // Accessors
    public function getFeaturedImageUrlAttribute(): ?string
    {
        if (!$this->featured_image) {
            return null;
        }
        
        return Storage::disk('public')->url($this->featured_image);
    }
}
