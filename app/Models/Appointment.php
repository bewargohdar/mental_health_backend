<?php

namespace App\Models;

use App\Enums\AppointmentStatus;
use App\Models\Traits\SerializesLocalTimezone;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Facades\Crypt;

class Appointment extends Model
{
    use HasFactory, SerializesLocalTimezone;

    protected $fillable = [
        'patient_id',
        'doctor_id',
        'scheduled_at',
        'duration',
        'status',
        'notes',
        'patient_notes',
        'video_room_id',
        'cancelled_at',
        'cancelled_by',
        'cancellation_reason',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => AppointmentStatus::class,
            'scheduled_at' => 'datetime',
            'cancelled_at' => 'datetime',
            'completed_at' => 'datetime',
            'duration' => 'integer',
        ];
    }

    // Encrypted notes accessor/mutator
    protected function notes(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value ? Crypt::decryptString($value) : null,
            set: fn ($value) => $value ? Crypt::encryptString($value) : null,
        );
    }

    // Relationships
    public function patient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'patient_id');
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }

    public function cancelledByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }

    // Scopes
    public function scopeUpcoming($query)
    {
        return $query->where('scheduled_at', '>', now())
            ->where('status', AppointmentStatus::CONFIRMED);
    }

    public function scopePending($query)
    {
        return $query->where('status', AppointmentStatus::PENDING);
    }

    public function scopeForDoctor($query, int $doctorId)
    {
        return $query->where('doctor_id', $doctorId);
    }

    public function scopeForPatient($query, int $patientId)
    {
        return $query->where('patient_id', $patientId);
    }

    // Helpers
    public function getEndTimeAttribute()
    {
        return $this->scheduled_at->addMinutes($this->duration);
    }

    public function canBeCancelled(): bool
    {
        return in_array($this->status, [
            AppointmentStatus::PENDING,
            AppointmentStatus::CONFIRMED,
        ]) && $this->scheduled_at->isFuture();
    }
}
