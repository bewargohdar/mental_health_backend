<?php

namespace App\Models;

use App\Enums\MoodType;
use App\Models\Traits\SerializesLocalTimezone;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MoodEntry extends Model
{
    use HasFactory, SerializesLocalTimezone;

    protected $fillable = [
        'user_id',
        'mood_type',
        'intensity',
        'notes',
        'factors',
        'activities',
        'sleep_hours',
        'is_private',
        'recorded_at',
    ];

    protected function casts(): array
    {
        return [
            'mood_type' => MoodType::class,
            'intensity' => 'integer',
            'factors' => 'array',
            'activities' => 'array',
            'sleep_hours' => 'decimal:1',
            'is_private' => 'boolean',
            'recorded_at' => 'datetime',
        ];
    }

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Scopes
    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopePublic($query)
    {
        return $query->where('is_private', false);
    }

    public function scopeInDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('recorded_at', [$startDate, $endDate]);
    }

    public function scopeByMood($query, MoodType $mood)
    {
        return $query->where('mood_type', $mood);
    }
}
