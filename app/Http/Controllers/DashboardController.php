<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Appointment;
use App\Models\Prescription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        if ($user->isAdmin()) {
            return $this->adminDashboard();
        } elseif ($user->isDoctor()) {
            return $this->doctorDashboard();
        } else {
            return $this->patientDashboard();
        }
    }

    private function adminDashboard()
    {
        $stats = [
            'total_doctors' => Doctor::count(),
            'total_patients' => Patient::count(),
            'total_appointments' => Appointment::whereDate('appointment_date', '>=', now())->count(),
            'total_prescriptions' => Prescription::count(),
        ];
        
        $recent_appointments = Appointment::with(['doctor.user', 'patient.user'])
            ->orderBy('appointment_date', 'desc')
            ->orderBy('appointment_time', 'desc')
            ->take(10)
            ->get();

        return view('dashboard.admin', compact('stats', 'recent_appointments'));
    }

    private function doctorDashboard()
    {
        $doctor = Auth::user()->doctor;
        
        if (!$doctor) {
            return redirect()->route('login')->with('error', 'Doctor profile not found.');
        }
        
        $stats = [
            'todays_appointments_count' => $doctor->appointments()
                ->whereDate('appointment_date', today())
                ->count(),
            'pending_appointments' => $doctor->appointments()
                ->where('status', 'pending')
                ->count(),
            'total_patients' => $doctor->patients()->distinct()->count(),
            'total_prescriptions' => $doctor->prescriptions()->count()
        ];

        $todays_appointments = $doctor->appointments()
            ->with('patient.user')
            ->whereDate('appointment_date', today())
            ->orderBy('appointment_time')
            ->get();

        $pending_appointments = $doctor->appointments()
            ->with('patient.user')
            ->where('status', 'pending')
            ->orderBy('appointment_date')
            ->orderBy('appointment_time')
            ->get();

        $upcoming_appointments = $doctor->appointments()
            ->with('patient.user')
            ->whereDate('appointment_date', '>=', now())
            ->orderBy('appointment_date')
            ->orderBy('appointment_time')
            ->take(5)
            ->get();

        $recent_prescriptions = $doctor->prescriptions()
            ->with('patient.user')
            ->latest()
            ->take(5)
            ->get();

        return view('dashboard.doctor', compact(
            'stats',
            'todays_appointments',
            'pending_appointments',
            'upcoming_appointments',
            'recent_prescriptions'
        ));
    }

    private function patientDashboard()
    {
        $patient = Auth::user()->patient;
        
        if (!$patient) {
            return redirect()->route('login')->with('error', 'Patient profile not found.');
        }
        
        $stats = [
            'total_appointments' => $patient->appointments()->count(),
            'completed_appointments' => $patient->appointments()
                ->where('status', 'completed')
                ->count(),
            'total_prescriptions' => $patient->prescriptions()->count(),
            'pending_bills' => $patient->bills()
                ->where('status', 'pending')
                ->count()
        ];

        $upcoming_appointments = $patient->appointments()
            ->with('doctor.user')
            ->whereDate('appointment_date', '>=', now())
            ->orderBy('appointment_date')
            ->orderBy('appointment_time')
            ->take(5)
            ->get();

        $recent_prescriptions = $patient->prescriptions()
            ->with('doctor.user')
            ->latest()
            ->take(5)
            ->get();

        $pending_bills = $patient->bills()
            ->where('status', 'pending')
            ->latest()
            ->take(5)
            ->get();

        return view('dashboard.patient', compact(
            'stats',
            'upcoming_appointments',
            'recent_prescriptions',
            'pending_bills'
        ));
    }
} 