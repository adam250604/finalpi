<?php

namespace App\Http\Controllers;

use App\Models\Doctor;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class DoctorController extends Controller
{
    private function checkAccess($type)
    {
        $user = auth()->user();
        if (!$user) {
            abort(401, 'Unauthenticated.');
        }

        switch ($type) {
            case 'admin':
                if ($user->role !== 'admin') {
                    abort(403, 'Only administrators can perform this action.');
                }
                break;
            case 'doctor':
                if ($user->role !== 'doctor') {
                    abort(403, 'Only doctors can perform this action.');
                }
                break;
            case 'doctor_or_admin':
                if (!in_array($user->role, ['admin', 'doctor'])) {
                    abort(403, 'Unauthorized action.');
                }
                break;
        }
    }

    public function index()
    {
        $this->checkAccess('admin');
        $doctors = Doctor::with('user')
            ->orderBy('created_at', 'desc')
            ->paginate(10);
        return view('doctors.index', compact('doctors'));
    }

    public function create()
    {
        $this->checkAccess('admin');
        return view('doctors.create');
    }

    public function store(Request $request)
    {
        $this->checkAccess('admin');
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'phone' => 'required|string|max:20',
            'address' => 'required|string',
            'specialization' => 'required|string|max:100',
            'qualifications' => 'required|string|max:500',
            'license_number' => 'required|string|max:50|unique:doctors',
            'experience' => 'required|integer|min:0',
        ]);

        try {
            DB::transaction(function () use ($validated) {
                $user = User::create([
                    'name' => $validated['name'],
                    'email' => $validated['email'],
                    'password' => Hash::make($validated['password']),
                    'role' => 'doctor',
                    'phone' => $validated['phone'],
                    'address' => $validated['address'],
                ]);

                Doctor::create([
                    'user_id' => $user->id,
                    'specialization' => $validated['specialization'],
                    'qualifications' => $validated['qualifications'],
                    'license_number' => $validated['license_number'],
                    'experience' => $validated['experience'],
                    'is_available' => true,
                ]);
            });

            return redirect()->route('doctors.index')
                ->with('success', 'Doctor created successfully.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to create doctor. Please try again.'])->withInput();
        }
    }

    public function show(Doctor $doctor)
    {
        $this->checkAccess('doctor_or_admin');
        $doctor->load(['user', 'appointments' => function($query) {
            $query->with('patient.user')
                ->orderBy('appointment_date', 'desc')
                ->orderBy('appointment_time', 'desc')
                ->take(10);
        }]);
        
        return view('doctors.show', compact('doctor'));
    }

    public function edit(Doctor $doctor)
    {
        $this->checkAccess('admin');
        $doctor->load('user');
        return view('doctors.edit', compact('doctor'));
    }

    public function update(Request $request, Doctor $doctor)
    {
        $this->checkAccess('admin');
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,'.$doctor->user_id,
            'phone' => 'required|string|max:20',
            'address' => 'required|string',
            'specialization' => 'required|string|max:100',
            'qualifications' => 'required|string|max:500',
            'license_number' => 'required|string|max:50|unique:doctors,license_number,'.$doctor->id,
            'experience' => 'required|integer|min:0',
            'is_available' => 'boolean',
        ]);

        try {
            DB::transaction(function () use ($validated, $doctor) {
                $doctor->user->update([
                    'name' => $validated['name'],
                    'email' => $validated['email'],
                    'phone' => $validated['phone'],
                    'address' => $validated['address'],
                ]);

                $doctor->update([
                    'specialization' => $validated['specialization'],
                    'qualifications' => $validated['qualifications'],
                    'license_number' => $validated['license_number'],
                    'experience' => $validated['experience'],
                    'is_available' => $validated['is_available'] ?? false,
                ]);
            });

            return redirect()->route('doctors.show', $doctor)
                ->with('success', 'Doctor updated successfully.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to update doctor. Please try again.'])->withInput();
        }
    }

    public function destroy(Doctor $doctor)
    {
        $this->checkAccess('admin');
        
        try {
            DB::transaction(function () use ($doctor) {
                // Cancel all future appointments
                $doctor->appointments()
                    ->whereIn('status', ['pending', 'confirmed'])
                    ->where('appointment_date', '>', now())
                    ->update(['status' => 'cancelled']);
                    
                $doctor->user->delete();
                $doctor->delete();
            });

            return redirect()->route('doctors.index')
                ->with('success', 'Doctor deleted successfully.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to delete doctor. Please try again.']);
        }
    }

    public function patients()
    {
        $this->checkAccess('doctor');
        $doctor = auth()->user()->doctor;
        $patients = $doctor->patients()
            ->with('user')
            ->withCount(['appointments' => function($query) {
                $query->where('status', 'completed');
            }])
            ->orderBy('appointments_count', 'desc')
            ->paginate(10);

        return view('doctors.patients', compact('patients'));
    }
} 