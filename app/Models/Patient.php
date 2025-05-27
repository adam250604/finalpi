<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Patient extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'date_of_birth',
        'gender',
        'blood_type',
        'medical_conditions',
        'allergies',
        'emergency_contact_name',
        'emergency_contact_phone',
        'insurance_provider',
        'insurance_number'
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'medical_conditions' => 'array',
        'allergies' => 'array'
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

    public function doctors(): BelongsToMany
    {
        return $this->belongsToMany(Doctor::class, 'appointments', 'patient_id', 'doctor_id')
                    ->distinct();
    }

    public function bills(): HasMany
    {
        return $this->hasMany(Bill::class);
    }

    public function getAgeAttribute(): int
    {
        return $this->date_of_birth->age;
    }

    public function getFullNameAttribute(): string
    {
        return $this->user->name;
    }

    public function getUpcomingAppointments()
    {
        return $this->appointments()
                    ->with('doctor.user')
                    ->upcoming()
                    ->get();
    }

    public function getPendingBills()
    {
        return $this->bills()
                    ->where('status', Bill::STATUS_PENDING)
                    ->sum('amount') ?? 0;
    }

    public function getCompletedAppointmentsCount()
    {
        return $this->appointments()
                    ->completed()
                    ->count();
    }

    public function getDistinctDoctorsCount()
    {
        return $this->doctors()->count();
    }

    public function getActivePrescriptionsAttribute()
    {
        return $this->prescriptions()
            ->with('doctor.user')
            ->whereDate('valid_until', '>=', today())
            ->orderBy('prescribed_date', 'desc')
            ->get();
    }

    public function getHasOutstandingBillsAttribute(): bool
    {
        return $this->bills()
            ->where('status', 'pending')
            ->whereDate('due_date', '<', today())
            ->exists();
    }
} 