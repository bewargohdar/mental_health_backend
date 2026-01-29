<?php

namespace App\Models;

use App\Enums\ContentCategory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Facades\Storage;

class Exercise extends Model
{
    use HasFactory;

    protected $appends = ['image_full_url'];

    protected $fillable = [
        'title',
        'description',
        'instructions',
        'category',
        'author_id',
        'duration',
        'difficulty',
        'audio_url',
        'image_url',
        'is_published',
        'completions_count',
        'tags',
    ];

    protected function casts(): array
    {
        return [
            'category' => ContentCategory::class,
            'is_published' => 'boolean',
            'duration' => 'integer',
            'completions_count' => 'integer',
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

    public function completions()
    {
        return $this->hasMany(ExerciseCompletion::class);
    }

    // Scopes
    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    public function scopeByDifficulty($query, string $difficulty)
    {
        return $query->where('difficulty', $difficulty);
    }

    // Accessors
    public function getImageFullUrlAttribute(): ?string
    {
        if (!$this->image_url) {
            return null;
        }
        
        return Storage::disk('public')->url($this->image_url);
    }
}
