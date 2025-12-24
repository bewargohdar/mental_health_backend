<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExerciseCompletion extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'exercise_id',
        'completed_at',
        'notes',
        'rating',
    ];

    protected function casts(): array
    {
        return [
            'completed_at' => 'datetime',
            'rating' => 'integer',
        ];
    }

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function exercise(): BelongsTo
    {
        return $this->belongsTo(Exercise::class);
    }
}
