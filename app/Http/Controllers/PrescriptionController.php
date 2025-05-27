<?php

namespace App\Http\Controllers;

use App\Models\Prescription;
use App\Models\Appointment;
use App\Models\Patient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use PDF;

class PrescriptionController extends Controller
{
    private function checkAccess($prescription = null)
    {
        $user = auth()->user();
        if (!$user) {
            abort(401, 'Unauthenticated.');
        }

        if ($user->role === 'admin') {
            return true;
        }

        if ($prescription) {
            if ($user->role === 'doctor' && $prescription->doctor_id === $user->doctor->id) {
                return true;
            }
            if ($user->role === 'patient' && $prescription->patient_id === $user->patient->id) {
                return true;
            }
            abort(403, 'Unauthorized action.');
        }

        if ($user->role === 'doctor') {
            return true;
        }

        abort(403, 'Unauthorized action.');
    }

    public function index()
    {
        $user = Auth::user();
        $prescriptions = [];

        if ($user->role === 'doctor') {
            $prescriptions = Prescription::with(['patient.user'])
                ->where('doctor_id', $user->doctor->id)
                ->orderBy('prescribed_date', 'desc')
                ->paginate(10);
        } elseif ($user->role === 'patient') {
            $prescriptions = Prescription::with(['doctor.user'])
                ->where('patient_id', $user->patient->id)
                ->orderBy('prescribed_date', 'desc')
                ->paginate(10);
        } else {
            $prescriptions = Prescription::with(['doctor.user', 'patient.user'])
                ->orderBy('prescribed_date', 'desc')
                ->paginate(10);
        }

        return view('prescriptions.index', compact('prescriptions'));
    }

    public function create(Request $request)
    {
        if (auth()->user()->role !== 'doctor') {
            abort(403, 'Only doctors can create prescriptions.');
        }

        $doctor = auth()->user()->doctor;

        if ($request->has('appointment_id')) {
            $appointment = Appointment::findOrFail($request->appointment_id);
            
            // Check if the doctor is authorized to create a prescription for this appointment
            if ($appointment->doctor_id !== $doctor->id) {
                abort(403, 'You can only create prescriptions for your own appointments.');
            }

            // Check if appointment is completed
            if ($appointment->status !== 'completed') {
                return back()->withErrors(['error' => 'You can only create prescriptions for completed appointments.']);
            }

            // Check if prescription already exists
            if ($appointment->prescription()->exists()) {
                return back()->withErrors(['error' => 'A prescription already exists for this appointment.']);
            }

            return view('prescriptions.create', compact('appointment'));
        } 
        elseif ($request->has('patient_id')) {
            $patient = Patient::findOrFail($request->patient_id);
            
            // Check if the doctor has any completed appointments with this patient
            $hasCompletedAppointments = Appointment::where('doctor_id', $doctor->id)
                ->where('patient_id', $patient->id)
                ->where('status', 'completed')
                ->exists();

            if (!$hasCompletedAppointments) {
                return back()->withErrors(['error' => 'You can only create prescriptions for patients with completed appointments.']);
            }

            return view('prescriptions.create', [
                'patient' => $patient,
                'doctor' => $doctor
            ]);
        }
        else {
            abort(400, 'Missing appointment_id or patient_id parameter.');
        }
    }

    public function store(Request $request)
    {
        if (auth()->user()->role !== 'doctor') {
            abort(403, 'Only doctors can create prescriptions.');
        }

        $validated = $request->validate([
            'appointment_id' => 'nullable|exists:appointments,id',
            'patient_id' => 'required_without:appointment_id|exists:patients,id',
            'diagnosis' => 'required|string|max:500',
            'medications' => 'required|array|min:1',
            'medications.*.name' => 'required|string|max:100',
            'medications.*.dosage' => 'required|string|max:100',
            'medications.*.frequency' => 'required|string|max:100',
            'medications.*.duration' => 'required|string|max:100',
            'instructions' => 'required|string|max:1000',
            'valid_until' => 'required|date|after:today',
        ]);

        $doctor = auth()->user()->doctor;

        if (isset($validated['appointment_id'])) {
            $appointment = Appointment::findOrFail($validated['appointment_id']);
            
            // Check if the doctor is authorized to create a prescription for this appointment
            if ($appointment->doctor_id !== $doctor->id) {
                abort(403, 'You can only create prescriptions for your own appointments.');
            }

            // Check if appointment is completed
            if ($appointment->status !== 'completed') {
                return back()->withErrors(['error' => 'You can only create prescriptions for completed appointments.']);
            }

            // Check if prescription already exists
            if ($appointment->prescription()->exists()) {
                return back()->withErrors(['error' => 'A prescription already exists for this appointment.']);
            }

            $patient_id = $appointment->patient_id;
        } else {
            $patient_id = $validated['patient_id'];
            
            // Check if the doctor has any completed appointments with this patient
            $hasCompletedAppointments = Appointment::where('doctor_id', $doctor->id)
                ->where('patient_id', $patient_id)
                ->where('status', 'completed')
                ->exists();

            if (!$hasCompletedAppointments) {
                return back()->withErrors(['error' => 'You can only create prescriptions for patients with completed appointments.']);
            }
        }

        try {
            $prescription = new Prescription([
                'doctor_id' => $doctor->id,
                'patient_id' => $patient_id,
                'appointment_id' => $validated['appointment_id'] ?? null,
                'diagnosis' => $validated['diagnosis'],
                'medications' => $validated['medications'],
                'instructions' => $validated['instructions'],
                'prescribed_date' => now(),
                'valid_until' => $validated['valid_until'],
            ]);

            $prescription->save();

            return redirect()->route('prescriptions.show', $prescription)
                ->with('success', 'Prescription created successfully.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to create prescription. Please try again.'])->withInput();
        }
    }

    public function show(Prescription $prescription)
    {
        $this->checkAccess($prescription);
        $prescription->load(['doctor.user', 'patient.user', 'appointment']);
        
        $canDownload = in_array(auth()->user()->role, ['doctor', 'patient', 'admin']) && 
                      ($prescription->doctor_id === optional(auth()->user()->doctor)->id || 
                       $prescription->patient_id === optional(auth()->user()->patient)->id || 
                       auth()->user()->role === 'admin');
                       
        return view('prescriptions.show', compact('prescription', 'canDownload'));
    }

    public function download(Prescription $prescription)
    {
        $this->checkAccess($prescription);
        
        try {
            $prescription->load(['doctor.user', 'patient.user', 'appointment']);
            $pdf = PDF::loadView('prescriptions.pdf', compact('prescription'));
            return $pdf->download('prescription-'.$prescription->id.'.pdf');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to generate prescription PDF. Please try again.']);
        }
    }
} 