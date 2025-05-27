<?php

namespace App\Http\Controllers;

use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Appointment;
use App\Models\Prescription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AdminController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('admin');
    }

    public function dashboard(): View
    {
        $stats = [
            'total_doctors' => Doctor::count(),
            'total_patients' => Patient::count(),
            'total_appointments' => Appointment::count(),
            'total_prescriptions' => Prescription::count(),
        ];

        $recent_appointments = Appointment::with(['doctor.user', 'patient.user'])
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        return view('dashboard.admin', compact('stats', 'recent_appointments'));
    }
} 