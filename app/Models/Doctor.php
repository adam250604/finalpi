<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Doctor extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'specialization',
        'qualifications',
        'license_number',
        'experience',
        'schedule',
        'consultation_fee',
        'is_available'
    ];

    protected $casts = [
        'schedule' => 'array',
        'consultation_fee' => 'decimal:2',
        'is_available' => 'boolean'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    public function prescriptions(): HasMany
    {
        return $this->hasMany(Prescription::class);
    }

    public function patients(): BelongsToMany
    {
        return $this->belongsToMany(Patient::class, 'appointments', 'doctor_id', 'patient_id')
                    ->distinct();
    }

    public function getFullNameAttribute(): string
    {
        return 'Dr. ' . $this->user->name;
    }

    public function getSpecializationDisplayAttribute(): string
    {
        return ucwords($this->specialization);
    }

    public function scopeAvailable($query)
    {
        return $query->where('is_available', true);
    }

    public function scopeBySpecialization($query, $specialization)
    {
        return $query->where('specialization', $specialization);
    }

    public function getTodaysAppointments()
    {
        return $this->appointments()
                    ->with('patient.user')
                    ->today()
                    ->get();
    }

    public function getPendingAppointments()
    {
        return $this->appointments()
                    ->with('patient.user')
                    ->pending()
                    ->get();
    }

    public function getUpcomingAppointments()
    {
        return $this->appointments()
                    ->with('patient.user')
                    ->upcoming()
                    ->get();
    }

    public function getDistinctPatientCount()
    {
        return $this->patients()->count();
    }
} 