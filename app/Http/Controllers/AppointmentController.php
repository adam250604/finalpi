<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Doctor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AppointmentController extends Controller
{
    private function checkAccess($appointment = null)
    {
        $user = auth()->user();
        if (!$user) {
            abort(401, 'Unauthenticated.');
        }

        if ($user->role === 'admin') {
            return true;
        }

        if ($appointment) {
            if ($user->role === 'doctor' && $appointment->doctor_id === $user->doctor->id) {
                return true;
            }
            if ($user->role === 'patient' && $appointment->patient_id === $user->patient->id) {
                return true;
            }
            abort(403, 'Unauthorized action.');
        }

        return true;
    }

    public function index()
    {
        $user = Auth::user();
        $appointments = [];

        if ($user->role === 'doctor') {
            $appointments = Appointment::with(['patient.user'])
                ->where('doctor_id', $user->doctor->id)
                ->orderBy('appointment_date', 'asc')
                ->orderBy('appointment_time', 'asc')
                ->paginate(10);
        } elseif ($user->role === 'patient') {
            $appointments = Appointment::with(['doctor.user'])
                ->where('patient_id', $user->patient->id)
                ->orderBy('appointment_date', 'asc')
                ->orderBy('appointment_time', 'asc')
                ->paginate(10);
        } else {
            $appointments = Appointment::with(['doctor.user', 'patient.user'])
                ->orderBy('appointment_date', 'asc')
                ->orderBy('appointment_time', 'asc')
                ->paginate(10);
        }

        return view('appointments.index', compact('appointments'));
    }

    public function create()
    {
        if (auth()->user()->role !== 'patient') {
            abort(403, 'Only patients can create appointments.');
        }

    $doctors = Doctor::with('user') // 'schedule' removed from here
            ->where('is_available', true)
            ->get();

        return view('appointments.create', compact('doctors'));
    }

    public function store(Request $request)
    {
        if (auth()->user()->role !== 'patient') {
            abort(403, 'Only patients can create appointments.');
        }

        $validated = $request->validate([
            'doctor_id' => 'required|exists:doctors,id',
            'appointment_date' => 'required|date|after:today',
            'appointment_time' => 'required|date_format:H:i',
            'reason' => 'required|string|max:500',
        ]);

        // Check if doctor is available at this time
        $doctor = Doctor::findOrFail($validated['doctor_id']);
        $dayOfWeek = strtolower(date('l', strtotime($validated['appointment_date'])));
        
        $schedule = collect($doctor->schedule)->firstWhere('day', $dayOfWeek);
        if (!$schedule || !$schedule['is_available']) {
            return back()->withErrors(['appointment_time' => 'Doctor is not available on this day.'])->withInput();
        }

        // Check if time is within doctor's schedule
        $appointmentTime = strtotime($validated['appointment_time']);
        $startTime = strtotime($schedule['start_time']);
        $endTime = strtotime($schedule['end_time']);
        
        if ($appointmentTime < $startTime || $appointmentTime > $endTime) {
            return back()->withErrors(['appointment_time' => 'Selected time is outside doctor\'s working hours.'])->withInput();
        }

        // Check for existing appointments
        $existingAppointment = Appointment::where('doctor_id', $validated['doctor_id'])
            ->where('appointment_date', $validated['appointment_date'])
            ->where('appointment_time', $validated['appointment_time'])
            ->where('status', '!=', 'cancelled')
            ->exists();

        if ($existingAppointment) {
            return back()->withErrors(['appointment_time' => 'This time slot is already booked.'])->withInput();
        }

        $appointment = new Appointment([
            'doctor_id' => $validated['doctor_id'],
            'patient_id' => auth()->user()->patient->id,
            'appointment_date' => $validated['appointment_date'],
            'appointment_time' => $validated['appointment_time'],
            'reason' => $validated['reason'],
            'status' => 'pending',
        ]);

        $appointment->save();

        return redirect()->route('appointments.index')
            ->with('success', 'Appointment booked successfully! Waiting for doctor confirmation.');
    }

    public function show(Appointment $appointment)
    {
        $this->checkAccess($appointment);
        $appointment->load(['doctor.user', 'patient.user']);
        
        $canConfirm = auth()->user()->role === 'doctor' && 
                     $appointment->doctor_id === auth()->user()->doctor->id && 
                     $appointment->status === 'pending';
                     
        $canCancel = auth()->user()->role === 'patient' && 
                    $appointment->patient_id === auth()->user()->patient->id && 
                    in_array($appointment->status, ['pending', 'confirmed']);
                    
        $canStart = auth()->user()->role === 'doctor' && 
                   $appointment->doctor_id === auth()->user()->doctor->id && 
                   $appointment->status === 'confirmed';
                   
        $canComplete = auth()->user()->role === 'doctor' && 
                      $appointment->doctor_id === auth()->user()->doctor->id && 
                      $appointment->status === 'in_progress';

        return view('appointments.show', compact('appointment', 'canConfirm', 'canCancel', 'canStart', 'canComplete'));
    }

    public function confirm(Appointment $appointment)
    {
        if (auth()->user()->role !== 'doctor' || $appointment->doctor_id !== auth()->user()->doctor->id) {
            abort(403, 'Unauthorized action.');
        }

        if ($appointment->status !== 'pending') {
            return back()->withErrors(['status' => 'This appointment cannot be confirmed.']);
        }

        $appointment->update(['status' => 'confirmed']);

        return back()->with('success', 'Appointment confirmed successfully.');
    }

    public function reject(Appointment $appointment)
    {
        if (auth()->user()->role !== 'doctor' || $appointment->doctor_id !== auth()->user()->doctor->id) {
            abort(403, 'Unauthorized action.');
        }

        if ($appointment->status !== 'pending') {
            return back()->withErrors(['status' => 'This appointment cannot be rejected.']);
        }

        $appointment->update(['status' => 'rejected']);

        return back()->with('success', 'Appointment rejected successfully.');
    }

    public function cancel(Appointment $appointment)
    {
        if (auth()->user()->role !== 'patient' || $appointment->patient_id !== auth()->user()->patient->id) {
            abort(403, 'Unauthorized action.');
        }

        if (!in_array($appointment->status, ['pending', 'confirmed'])) {
            return back()->withErrors(['status' => 'This appointment cannot be cancelled.']);
        }

        $appointment->update(['status' => 'cancelled']);

        return back()->with('success', 'Appointment cancelled successfully.');
    }

    public function start(Appointment $appointment)
    {
        if (auth()->user()->role !== 'doctor' || $appointment->doctor_id !== auth()->user()->doctor->id) {
            abort(403, 'Unauthorized action.');
        }

        if ($appointment->status !== 'confirmed') {
            return back()->withErrors(['status' => 'This appointment cannot be started.']);
        }

        $appointment->update(['status' => 'in_progress']);

        return back()->with('success', 'Appointment started successfully.');
    }

    public function complete(Appointment $appointment)
    {
        if (auth()->user()->role !== 'doctor' || $appointment->doctor_id !== auth()->user()->doctor->id) {
            abort(403, 'Unauthorized action.');
        }

        if ($appointment->status !== 'in_progress') {
            return back()->withErrors(['status' => 'This appointment cannot be completed.']);
        }

        $appointment->update(['status' => 'completed']);

        return back()->with('success', 'Appointment completed successfully.');
    }
} 