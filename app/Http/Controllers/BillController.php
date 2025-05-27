<?php

namespace App\Http\Controllers;

use App\Models\Bill;
use App\Models\Appointment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BillController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $bills = [];

        if ($user->role === 'patient') {
            $bills = Bill::with(['appointment.doctor.user'])
                ->where('patient_id', $user->patient->id)
                ->latest()
                ->paginate(10);
        } else {
            $bills = Bill::with(['patient.user', 'appointment.doctor.user'])
                ->latest()
                ->paginate(10);
        }

        return view('bills.index', compact('bills'));
    }

    public function create()
    {
        $this->authorize('create', Bill::class);

        $appointment = Appointment::findOrFail(request('appointment_id'));
        return view('bills.create', compact('appointment'));
    }

    public function store(Request $request)
    {
        $this->authorize('create', Bill::class);

        $validated = $request->validate([
            'appointment_id' => 'required|exists:appointments,id',
            'amount' => 'required|numeric|min:0',
            'description' => 'required|string|max:500',
            'due_date' => 'required|date|after:today',
        ]);

        $appointment = Appointment::findOrFail($validated['appointment_id']);

        $bill = new Bill([
            'patient_id' => $appointment->patient_id,
            'appointment_id' => $appointment->id,
            'amount' => $validated['amount'],
            'description' => $validated['description'],
            'due_date' => $validated['due_date'],
            'status' => 'pending',
        ]);

        $bill->save();

        return redirect()->route('bills.show', $bill)
            ->with('success', 'Bill created successfully.');
    }

    public function show(Bill $bill)
    {
        $this->authorize('view', $bill);
        
        $bill->load(['patient.user', 'appointment.doctor.user']);
        return view('bills.show', compact('bill'));
    }

    public function pay(Bill $bill)
    {
        $this->authorize('pay', $bill);

        // Here you would typically integrate with a payment gateway
        // For now, we'll just mark it as paid
        $bill->update([
            'status' => 'paid',
            'paid_at' => now(),
        ]);

        return redirect()->route('bills.show', $bill)
            ->with('success', 'Payment processed successfully.');
    }
} 