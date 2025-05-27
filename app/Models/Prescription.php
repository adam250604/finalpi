<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Prescription extends Model
{
    use HasFactory;

    protected $fillable = [
        'appointment_id',
        'patient_id',
        'doctor_id',
        'medication',
        'dosage',
        'frequency',
        'duration',
        'instructions',
        'prescribed_date',
        'valid_until',
        'notes',
    ];

    protected $casts = [
        'prescribed_date' => 'date',
        'valid_until' => 'date',
        'medication' => 'array',
    ];

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }

    public function scopeActive($query)
    {
        return $query->whereDate('valid_until', '>=', today());
    }

    public function getIsActiveAttribute(): bool
    {
        return $this->valid_until >= today();
    }

    public function getFormattedPrescribedDateAttribute(): string
    {
        return $this->prescribed_date->format('F j, Y');
    }

    public function getFormattedValidUntilAttribute(): string
    {
        return $this->valid_until->format('F j, Y');
    }
} 