<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class PatientController extends Controller
{
    public function index()
    {
        $patients = Patient::with('user')
            ->withCount('appointments')
            ->paginate(10);
        return view('patients.index', compact('patients'));
    }

    public function create()
    {
        return view('patients.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'phone' => 'required|string|max:20',
            'address' => 'required|string',
            'date_of_birth' => 'required|date|before:today',
            'blood_type' => 'required|string|in:A+,A-,B+,B-,AB+,AB-,O+,O-',
            'medical_conditions' => 'nullable|string',
            'allergies' => 'nullable|string',
            'gender' => 'required|string|in:male,female,other',
            'emergency_contact_name' => 'required|string|max:255',
            'emergency_contact_phone' => 'required|string|max:20',
            'insurance_provider' => 'nullable|string|max:255',
            'insurance_number' => 'nullable|string|max:255',
        ]);

        DB::transaction(function () use ($validated) {
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'role' => 'patient',
                'phone' => $validated['phone'],
                'address' => $validated['address'],
            ]);

            Patient::create([
                'user_id' => $user->id,
                'date_of_birth' => $validated['date_of_birth'],
                'gender' => $validated['gender'],
                'blood_type' => $validated['blood_type'],
                'medical_conditions' => $validated['medical_conditions'],
                'allergies' => $validated['allergies'],
                'emergency_contact_name' => $validated['emergency_contact_name'],
                'emergency_contact_phone' => $validated['emergency_contact_phone'],
                'insurance_provider' => $validated['insurance_provider'],
                'insurance_number' => $validated['insurance_number'],
            ]);
        });

        return redirect()->route('patients.index')
            ->with('success', 'Patient created successfully.');
    }

    public function show(Patient $patient)
    {
        $patient->load([
            'user',
            'appointments.doctor.user',
            'prescriptions.doctor.user',
            'bills' => function ($query) {
                $query->latest();
            }
        ]);
        
        return view('patients.show', compact('patient'));
    }

    public function edit(Patient $patient)
    {
        $patient->load('user');
        return view('patients.edit', compact('patient'));
    }

    public function update(Request $request, Patient $patient)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,'.$patient->user_id,
            'phone' => 'required|string|max:20',
            'address' => 'required|string',
            'date_of_birth' => 'required|date|before:today',
            'blood_type' => 'required|string|in:A+,A-,B+,B-,AB+,AB-,O+,O-',
            'medical_conditions' => 'nullable|string',
            'allergies' => 'nullable|string',
            'gender' => 'required|string|in:male,female,other',
            'emergency_contact_name' => 'required|string|max:255',
            'emergency_contact_phone' => 'required|string|max:20',
            'insurance_provider' => 'nullable|string|max:255',
            'insurance_number' => 'nullable|string|max:255',
        ]);

        DB::transaction(function () use ($validated, $patient) {
            $patient->user->update([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'],
                'address' => $validated['address'],
            ]);

            $patient->update([
                'date_of_birth' => $validated['date_of_birth'],
                'gender' => $validated['gender'],
                'blood_type' => $validated['blood_type'],
                'medical_conditions' => $validated['medical_conditions'],
                'allergies' => $validated['allergies'],
                'emergency_contact_name' => $validated['emergency_contact_name'],
                'emergency_contact_phone' => $validated['emergency_contact_phone'],
                'insurance_provider' => $validated['insurance_provider'],
                'insurance_number' => $validated['insurance_number'],
            ]);
        });

        return redirect()->route('patients.show', $patient)
            ->with('success', 'Patient updated successfully.');
    }

    public function destroy(Patient $patient)
    {
        DB::transaction(function () use ($patient) {
            $patient->user->delete();
            $patient->delete();
        });

        return redirect()->route('patients.index')
            ->with('success', 'Patient deleted successfully.');
    }
} 