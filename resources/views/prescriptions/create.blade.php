<x-app-layout>
    <x-slot name="header">
        <h2 class="text-2xl font-semibold text-gray-800">Write Prescription</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="px-4 py-5 sm:px-6">
                    <p class="mt-1 max-w-2xl text-sm text-gray-500">
                        For {{ $appointment->patient->user->name }} - Appointment on {{ $appointment->appointment_date->format('F j, Y') }}
                    </p>
                </div>
                <div class="border-t border-gray-200">
                    <form action="{{ route('prescriptions.store') }}" method="POST" class="p-6">
                        @csrf
                        <input type="hidden" name="appointment_id" value="{{ $appointment->id }}">

                        <!-- Diagnosis -->
                        <div class="mb-6">
                            <label for="diagnosis" class="block text-sm font-medium text-gray-700">Diagnosis</label>
                            <textarea id="diagnosis" name="diagnosis" rows="3" required
                                class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"
                                placeholder="Enter the diagnosis">{{ old('diagnosis') }}</textarea>
                            @error('diagnosis')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Medications -->
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Medications</label>
                            <div id="medications-container">
                                <div class="medication-entry mb-4">
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                        <div>
                                            <input type="text" name="medications[0][name]" placeholder="Medication name"
                                                class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                                        </div>
                                        <div>
                                            <input type="text" name="medications[0][dosage]" placeholder="Dosage"
                                                class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                                        </div>
                                        <div>
                                            <input type="text" name="medications[0][frequency]" placeholder="Frequency"
                                                class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <button type="button" onclick="addMedication()" class="mt-2 text-sm text-indigo-600 hover:text-indigo-900">
                                + Add Another Medication
                            </button>
                            @error('medications')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Instructions -->
                        <div class="mb-6">
                            <label for="instructions" class="block text-sm font-medium text-gray-700">Instructions</label>
                            <textarea id="instructions" name="instructions" rows="3" required
                                class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"
                                placeholder="Enter any special instructions">{{ old('instructions') }}</textarea>
                            @error('instructions')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Validity -->
                        <div class="mb-6">
                            <label for="valid_until" class="block text-sm font-medium text-gray-700">Valid Until</label>
                            <input type="date" id="valid_until" name="valid_until" required
                                min="{{ date('Y-m-d') }}"
                                class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                            @error('valid_until')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="flex justify-end">
                            <button type="submit"
                                class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                Save Prescription
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        let medicationCount = 1;

        function addMedication() {
            const container = document.getElementById('medications-container');
            const newEntry = document.createElement('div');
            newEntry.className = 'medication-entry mb-4';
            newEntry.innerHTML = `
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <input type="text" name="medications[${medicationCount}][name]" placeholder="Medication name"
                            class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                    </div>
                    <div>
                        <input type="text" name="medications[${medicationCount}][dosage]" placeholder="Dosage"
                            class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                    </div>
                    <div>
                        <input type="text" name="medications[${medicationCount}][frequency]" placeholder="Frequency"
                            class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                    </div>
                </div>
            `;
            container.appendChild(newEntry);
            medicationCount++;
        }
    </script>
    @endpush
</x-app-layout> 