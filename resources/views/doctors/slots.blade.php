<x-app-layout>
    <x-slot name="header">
        <h2 class="font-headline font-bold text-xl text-slate-900 leading-tight">
            {{ __('Slots for :name', ['name' => $doctor->name]) }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <a href="{{ route('doctors.index') }}" class="text-primary font-medium hover:text-primary-hover hover:underline">&larr; Back to Doctors</a>

            @forelse ($slots as $date => $daySlots)
                <div class="bg-white border border-slate-200/80 overflow-hidden shadow-sm shadow-slate-200/40 sm:rounded-xl p-6">
                    <h3 class="font-headline text-lg font-semibold text-slate-900 mb-4">{{ \Illuminate\Support\Carbon::parse($date)->format('D, M j, Y') }}</h3>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                        @foreach ($daySlots as $slot)
                            <form method="POST" action="{{ route('appointments.store') }}">
                                @csrf
                                <input type="hidden" name="appointment_slot_id" value="{{ $slot->id }}">
                                <button type="submit" class="w-full px-3 py-2.5 border border-slate-300 rounded-lg text-sm font-medium text-slate-700 hover:bg-primary-surface hover:border-primary hover:text-primary transition-colors">
                                    {{ \Illuminate\Support\Carbon::parse($slot->start_time)->format('g:i A') }}
                                </button>
                            </form>
                        @endforeach
                    </div>
                </div>
            @empty
                <div class="bg-white border border-slate-200/80 overflow-hidden shadow-sm shadow-slate-200/40 sm:rounded-xl p-6 text-slate-500">
                    No available slots for this doctor right now.
                </div>
            @endforelse
        </div>
    </div>
</x-app-layout>
