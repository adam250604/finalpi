<x-app-layout>
    <x-slot name="header">
        <h2 class="text-2xl font-semibold text-gray-800">Appointment Details</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="px-4 py-5 sm:px-6">
                    <p class="mt-1 max-w-2xl text-sm text-gray-500">
                        {{ $appointment->appointment_date->format('l, F j, Y') }} at {{ $appointment->appointment_time->format('h:i A') }}
                    </p>
                </div>
                <div class="border-t border-gray-200">
                    <dl>
                        <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                            <dt class="text-sm font-medium text-gray-500">Patient Name</dt>
                            <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ $appointment->patient->user->name }}</dd>
                        </div>
                        <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                            <dt class="text-sm font-medium text-gray-500">Doctor Name</dt>
                            <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                                {{ $appointment->doctor->user->name }}
                                <span class="text-gray-500">({{ $appointment->doctor->specialization }})</span>
                            </dd>
                        </div>
                        <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                            <dt class="text-sm font-medium text-gray-500">Status</dt>
                            <dd class="mt-1 text-sm sm:mt-0 sm:col-span-2">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                    {{ $appointment->status === 'completed' ? 'bg-green-100 text-green-800' : 
                                       ($appointment->status === 'cancelled' ? 'bg-red-100 text-red-800' : 
                                       ($appointment->status === 'in_progress' ? 'bg-blue-100 text-blue-800' : 'bg-yellow-100 text-yellow-800')) }}">
                                    {{ ucfirst($appointment->status) }}
                                </span>
                            </dd>
                        </div>
                        <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                            <dt class="text-sm font-medium text-gray-500">Reason for Visit</dt>
                            <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ $appointment->reason }}</dd>
                        </div>
                        @if($appointment->notes)
                        <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                            <dt class="text-sm font-medium text-gray-500">Doctor's Notes</dt>
                            <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ $appointment->notes }}</dd>
                        </div>
                        @endif
                        @if($appointment->diagnosis)
                        <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                            <dt class="text-sm font-medium text-gray-500">Diagnosis</dt>
                            <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ $appointment->diagnosis }}</dd>
                        </div>
                        @endif
                        @if($appointment->treatment)
                        <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                            <dt class="text-sm font-medium text-gray-500">Treatment Plan</dt>
                            <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ $appointment->treatment }}</dd>
                        </div>
                        @endif
                    </dl>
                </div>
            </div>

            <div class="mt-6 flex justify-between">
                <div>
                    <a href="{{ route('appointments.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 font-bold py-2 px-4 rounded">
                        Back to List
                    </a>
                </div>
                <div>
                    @if($appointment->status === 'scheduled')
                        @if(auth()->user()->role === 'doctor')
                        <a href="{{ route('appointments.start', $appointment) }}" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                            Start Appointment
                        </a>
                        @endif
                        @if(auth()->user()->role === 'patient')
                        <form action="{{ route('appointments.cancel', $appointment) }}" method="POST" class="inline">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded" onclick="return confirm('Are you sure you want to cancel this appointment?')">
                                Cancel Appointment
                            </button>
                        </form>
                        @endif
                    @endif
                    @if($appointment->status === 'in_progress' && auth()->user()->role === 'doctor')
                    <form action="{{ route('appointments.update', $appointment) }}" method="POST" class="inline">
                        @csrf
                        @method('PUT')
                        <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                            Complete Appointment
                        </button>
                    </form>
                    @endif
                    @if($appointment->status === 'completed' && auth()->user()->role === 'doctor')
                    <a href="{{ route('prescriptions.create', ['appointment_id' => $appointment->id]) }}" class="bg-indigo-500 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded">
                        Write Prescription
                    </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout> 