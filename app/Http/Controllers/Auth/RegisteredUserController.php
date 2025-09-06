<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Doctor;
use App\Models\Patient;
use App\Providers\RouteServiceProvider;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'phone' => ['required', 'string', 'max:20'],
            'address' => ['required', 'string'],
            'role' => ['required', 'string', 'in:doctor,patient'],

            // Patient-specific fields
            'date_of_birth' => ['required_if:role,patient', 'nullable', 'date', 'before:today'],
            'gender' => ['required_if:role,patient', 'nullable', 'string', 'in:male,female,other'],
            'emergency_contact_name' => ['required_if:role,patient', 'nullable', 'string', 'max:255'],
            'emergency_contact_phone' => ['required_if:role,patient', 'nullable', 'string', 'max:20'],
            
            // Doctor specific fields
            'specialization' => ['required_if:role,doctor', 'nullable', 'string', 'max:100'],
            'qualifications' => ['required_if:role,doctor', 'nullable', 'string', 'max:500'],
            'license_number' => ['required_if:role,doctor', 'nullable', 'string', 'max:50', 'unique:doctors'],
            'experience' => ['required_if:role,doctor', 'nullable', 'integer', 'min:0'],
        ]);

        DB::transaction(function () use ($request) {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => $request->role,
                'phone' => $request->phone,
                'address' => $request->address,
            ]);

            if ($request->role === 'doctor') {
                Doctor::create([
                    'user_id' => $user->id,
                    'specialization' => $request->specialization,
                    'qualifications' => $request->qualifications,
                    'license_number' => $request->license_number,
                    'experience' => $request->experience,
                    'is_available' => true,
                ]);
            } else {
                Patient::create([
                    'user_id' => $user->id,
                    'date_of_birth' => $request->date_of_birth,
                    'gender' => $request->gender,
                    'emergency_contact_name' => $request->emergency_contact_name,
                    'emergency_contact_phone' => $request->emergency_contact_phone,
                ]);
            }

            event(new Registered($user));

            Auth::login($user);
        });

        return redirect(RouteServiceProvider::HOME);
    }
}
