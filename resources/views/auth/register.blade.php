<x-guest-layout>
@if ($errors->any())
    <div class="mb-4">
        <div class="font-medium text-red-600">
            {{ __('Whoops! Something went wrong.') }}
        </div>

        <ul class="mt-3 list-disc list-inside text-sm text-red-600">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form method="POST" action="{{ route('register') }}" class="space-y-4" id="registrationForm">
    @csrf

    <!-- Name -->
    <div>
        <x-input-label for="name" :value="__('Name')" />
        <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" />
        <x-input-error :messages="$errors->get('name')" class="mt-2" />
    </div>

    <!-- Email Address -->
    <div>
        <x-input-label for="email" :value="__('Email')" />
        <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autocomplete="username" />
        <x-input-error :messages="$errors->get('email')" class="mt-2" />
    </div>

    <!-- Phone -->
    <div>
        <x-input-label for="phone" :value="__('Phone')" />
        <x-text-input id="phone" class="block mt-1 w-full" type="tel" name="phone" :value="old('phone')" required />
        <x-input-error :messages="$errors->get('phone')" class="mt-2" />
    </div>

    <!-- Address -->
    <div>
        <x-input-label for="address" :value="__('Address')" />
        <x-text-input id="address" class="block mt-1 w-full" type="text" name="address" :value="old('address')" required />
        <x-input-error :messages="$errors->get('address')" class="mt-2" />
    </div>

    <!-- Password -->
    <div>
        <x-input-label for="password" :value="__('Password')" />
        <x-text-input id="password" class="block mt-1 w-full" type="password" name="password" required autocomplete="new-password" />
        <x-input-error :messages="$errors->get('password')" class="mt-2" />
    </div>

    <!-- Confirm Password -->
    <div>
        <x-input-label for="password_confirmation" :value="__('Confirm Password')" />
        <x-text-input id="password_confirmation" class="block mt-1 w-full" type="password" name="password_confirmation" required autocomplete="new-password" />
        <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
    </div>

    <!-- Role -->
    <div class="mt-4">
        <x-input-label for="role" :value="__('Register as')" />
        <select id="role" name="role" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
            <option value="">Select Role</option>
            <option value="patient" {{ old('role') == 'patient' ? 'selected' : '' }}>Patient</option>
            <option value="doctor" {{ old('role') == 'doctor' ? 'selected' : '' }}>Doctor</option>
        </select>
        <x-input-error :messages="$errors->get('role')" class="mt-2" />
    </div>

    <!-- Patient Fields -->
    <div id="patientFields" class="space-y-4" style="display: none;">
        <!-- Date of Birth (MOVED HERE) -->
        <div>
            <x-input-label for="date_of_birth" :value="__('Date of Birth')" />
            <x-text-input id="date_of_birth" class="block mt-1 w-full" type="date" name="date_of_birth" :value="old('date_of_birth')" />
            <x-input-error :messages="$errors->get('date_of_birth')" class="mt-2" />
        </div>

        <!-- Gender (MOVED HERE) -->
        <div>
            <x-input-label for="gender" :value="__('Gender')" />
            <select id="gender" name="gender" class="block mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                <option value="">Select gender</option>
                <option value="male" {{ old('gender') == 'male' ? 'selected' : '' }}>Male</option>
                <option value="female" {{ old('gender') == 'female' ? 'selected' : '' }}>Female</option>
                <option value="other" {{ old('gender') == 'other' ? 'selected' : '' }}>Other</option>
            </select>
            <x-input-error :messages="$errors->get('gender')" class="mt-2" />
        </div>

        <!-- Emergency Contact Name (MOVED HERE) -->
        <div>
            <x-input-label for="emergency_contact_name" :value="__('Emergency Contact Name')" />
            <x-text-input id="emergency_contact_name" class="block mt-1 w-full" type="text" name="emergency_contact_name" :value="old('emergency_contact_name')" />
            <x-input-error :messages="$errors->get('emergency_contact_name')" class="mt-2" />
        </div>

        <!-- Emergency Contact Phone (MOVED HERE) -->
        <div>
            <x-input-label for="emergency_contact_phone" :value="__('Emergency Contact Phone')" />
            <x-text-input id="emergency_contact_phone" class="block mt-1 w-full" type="tel" name="emergency_contact_phone" :value="old('emergency_contact_phone')" />
            <x-input-error :messages="$errors->get('emergency_contact_phone')" class="mt-2" />
        </div>

        <!-- Blood Type -->
        <div>
            <x-input-label for="blood_type" :value="__('Blood Type')" />
            <select id="blood_type" name="blood_type" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                <option value="">Select Blood Type</option>
                @foreach(['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'] as $type)
                    <option value="{{ $type }}" {{ old('blood_type') == $type ? 'selected' : '' }}>{{ $type }}</option>
                @endforeach
            </select>
            <x-input-error :messages="$errors->get('blood_type')" class="mt-2" />
        </div>

        <!-- Allergies -->
        <div>
            <x-input-label for="allergies" :value="__('Allergies')" />
            <x-text-input id="allergies" class="block mt-1 w-full" type="text" name="allergies" :value="old('allergies')" placeholder="List any allergies (optional)" />
            <x-input-error :messages="$errors->get('allergies')" class="mt-2" />
        </div>
    </div>

    <!-- Doctor Fields -->
    <div id="doctorFields" class="space-y-4" style="display: none;">
        <!-- Specialization -->
        <div>
            <x-input-label for="specialization" :value="__('Specialization')" />
            <x-text-input id="specialization" class="block mt-1 w-full" type="text" name="specialization" :value="old('specialization')" />
            <x-input-error :messages="$errors->get('specialization')" class="mt-2" />
        </div>

        <!-- Qualifications -->
        <div>
            <x-input-label for="qualifications" :value="__('Qualifications')" />
            <x-text-input id="qualifications" class="block mt-1 w-full" type="text" name="qualifications" :value="old('qualifications')" />
            <x-input-error :messages="$errors->get('qualifications')" class="mt-2" />
        </div>

        <!-- License Number -->
        <div>
            <x-input-label for="license_number" :value="__('License Number')" />
            <x-text-input id="license_number" class="block mt-1 w-full" type="text" name="license_number" :value="old('license_number')" />
            <x-input-error :messages="$errors->get('license_number')" class="mt-2" />
        </div>

        <!-- Experience -->
        <div>
            <x-input-label for="experience" :value="__('Years of Experience')" />
            <x-text-input id="experience" class="block mt-1 w-full" type="number" name="experience" :value="old('experience')" min="0" />
            <x-input-error :messages="$errors->get('experience')" class="mt-2" />
        </div>
    </div>

    <div class="flex items-center justify-end mt-4">
        <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" href="{{ route('login') }}">
            {{ __('Already registered?') }}
        </a>

        <x-primary-button class="ml-4">
            {{ __('Register') }}
        </x-primary-button>
    </div>
</form>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const roleSelect = document.getElementById('role');
        const patientFields = document.getElementById('patientFields');
        const doctorFields = document.getElementById('doctorFields');
        const form = document.getElementById('registrationForm');

        function toggleFields() {
            const selectedRole = roleSelect.value;
            
            // Hide all role-specific fields first
            patientFields.style.display = 'none';
            doctorFields.style.display = 'none';
            
            // Show fields based on selected role
            if (selectedRole === 'patient') {
                patientFields.style.display = 'block';
            } else if (selectedRole === 'doctor') {
                doctorFields.style.display = 'block';
            }
        }

        // Initial toggle
        toggleFields();

        // Toggle on role change
        roleSelect.addEventListener('change', toggleFields);

        // Form submission handling
        form.addEventListener('submit', function(e) {
            const selectedRole = roleSelect.value;
            let isValid = true;
            
            // Common required fields for both roles
            const commonFields = ['name', 'email', 'phone', 'address', 'password', 'password_confirmation'];
            
            // Validate common fields
            commonFields.forEach(field => {
                const input = document.getElementById(field);
                if (!input.value) {
                    isValid = false;
                    input.classList.add('border-red-500');
                } else {
                    input.classList.remove('border-red-500');
                }
            });
            
            if (selectedRole === 'patient') {
                const patientFields = ['date_of_birth', 'gender', 'emergency_contact_name', 'emergency_contact_phone'];
                
                patientFields.forEach(field => {
                    const input = document.getElementById(field);
                    if (!input.value) {
                        isValid = false;
                        input.classList.add('border-red-500');
                    } else {
                        input.classList.remove('border-red-500');
                    }
                });
                
                if (!isValid) {
                    e.preventDefault();
                    alert('Please fill in all required fields for patient registration.');
                }
            } else if (selectedRole === 'doctor') {
                const doctorFields = ['specialization', 'qualifications', 'license_number', 'experience'];
                
                doctorFields.forEach(field => {
                    const input = document.getElementById(field);
                    if (!input.value) {
                        isValid = false;
                        input.classList.add('border-red-500');
                    } else {
                        input.classList.remove('border-red-500');
                    }
                });
                
                if (!isValid) {
                    e.preventDefault();
                    alert('Please fill in all required fields for doctor registration.');
                }
            }
            
            if (!selectedRole) {
                isValid = false;
                roleSelect.classList.add('border-red-500');
                e.preventDefault();
                alert('Please select a role.');
            }
        });
    });
</script>
</x-guest-layout>
