<x-app-layout>
    <x-slot name="header">
        <h2 class="text-2xl font-semibold text-gray-800">Create Bill</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <form method="POST" action="{{ route('bills.store') }}" class="space-y-6">
                        @csrf
                        <input type="hidden" name="appointment_id" value="{{ $appointment->id }}">

                        <!-- Patient Information -->
                        <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6 rounded-lg">
                            <dt class="text-sm font-medium text-gray-500">Patient Name</dt>
                            <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                                {{ $appointment->patient->user->name }}
                            </dd>
                        </div>

                        <!-- Consultation Fee -->
                        <div>
                            <label for="consultation_fee" class="block text-sm font-medium text-gray-700">Consultation Fee</label>
                            <div class="mt-1 relative rounded-md shadow-sm">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="text-gray-500 sm:text-sm">$</span>
                                </div>
                                <input type="number" name="consultation_fee" id="consultation_fee" step="0.01" min="0"
                                    value="{{ old('consultation_fee', $appointment->doctor->consultation_fee) }}"
                                    class="focus:ring-indigo-500 focus:border-indigo-500 block w-full pl-7 pr-12 sm:text-sm border-gray-300 rounded-md"
                                    required>
                            </div>
                            @error('consultation_fee')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Additional Services -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Additional Services</label>
                            <div id="services-container">
                                <div class="service-entry mb-4">
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                        <div class="col-span-2">
                                            <input type="text" name="services[0][name]" placeholder="Service name"
                                                class="focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md">
                                        </div>
                                        <div>
                                            <div class="relative rounded-md shadow-sm">
                                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                    <span class="text-gray-500 sm:text-sm">$</span>
                                                </div>
                                                <input type="number" name="services[0][cost]" placeholder="Cost" step="0.01" min="0"
                                                    class="focus:ring-indigo-500 focus:border-indigo-500 block w-full pl-7 pr-12 sm:text-sm border-gray-300 rounded-md">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <button type="button" onclick="addService()" class="mt-2 text-sm text-indigo-600 hover:text-indigo-900">
                                + Add Another Service
                            </button>
                        </div>

                        <!-- Notes -->
                        <div>
                            <label for="notes" class="block text-sm font-medium text-gray-700">Notes</label>
                            <textarea id="notes" name="notes" rows="3"
                                class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"
                                placeholder="Any additional notes">{{ old('notes') }}</textarea>
                            @error('notes')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Due Date -->
                        <div>
                            <label for="due_date" class="block text-sm font-medium text-gray-700">Due Date</label>
                            <input type="date" id="due_date" name="due_date"
                                min="{{ date('Y-m-d') }}"
                                value="{{ old('due_date', date('Y-m-d', strtotime('+30 days'))) }}"
                                class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"
                                required>
                            @error('due_date')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="flex justify-end">
                            <button type="submit"
                                class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                Generate Bill
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        let serviceCount = 1;

        function addService() {
            const container = document.getElementById('services-container');
            const newEntry = document.createElement('div');
            newEntry.className = 'service-entry mb-4';
            newEntry.innerHTML = `
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="col-span-2">
                        <input type="text" name="services[${serviceCount}][name]" placeholder="Service name"
                            class="focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md">
                    </div>
                    <div>
                        <div class="relative rounded-md shadow-sm">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <span class="text-gray-500 sm:text-sm">$</span>
                            </div>
                            <input type="number" name="services[${serviceCount}][cost]" placeholder="Cost" step="0.01" min="0"
                                class="focus:ring-indigo-500 focus:border-indigo-500 block w-full pl-7 pr-12 sm:text-sm border-gray-300 rounded-md">
                        </div>
                    </div>
                </div>
            `;
            container.appendChild(newEntry);
            serviceCount++;
        }
    </script>
    @endpush
</x-app-layout> 