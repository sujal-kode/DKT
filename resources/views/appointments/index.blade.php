<x-app-layout>
    <x-slot name="header">
        <h2 class="font-headline font-bold text-xl text-slate-900 leading-tight">
            {{ __('My Appointments') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white border border-slate-200/80 overflow-hidden shadow-sm shadow-slate-200/40 sm:rounded-xl p-6">
                <table class="w-full text-left">
                    <thead>
                        <tr class="border-b border-slate-100">
                            <th class="py-2 text-xs font-semibold text-slate-500 uppercase tracking-wide">Doctor</th>
                            <th class="py-2 text-xs font-semibold text-slate-500 uppercase tracking-wide">Date</th>
                            <th class="py-2 text-xs font-semibold text-slate-500 uppercase tracking-wide">Time</th>
                            <th class="py-2 text-xs font-semibold text-slate-500 uppercase tracking-wide">Status</th>
                            <th class="py-2 text-xs font-semibold text-slate-500 uppercase tracking-wide"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($appointments as $appointment)
                            <tr class="border-b border-slate-100">
                                <td class="py-2">{{ $appointment->doctor->name }}</td>
                                <td class="py-2">{{ $appointment->slot->date->format('D, M j, Y') }}</td>
                                <td class="py-2">{{ \Illuminate\Support\Carbon::parse($appointment->slot->start_time)->format('g:i A') }}</td>
                                <td class="py-2">
                                    @if ($appointment->status === 'cancelled_by_break')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-amber-100 text-amber-800" title="{{ $appointment->cancellation_reason }}">
                                            {{ __('Cancelled — schedule change') }}
                                        </span>
                                    @elseif ($appointment->status === 'cancelled')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-slate-100 text-slate-700">
                                            {{ __('Cancelled') }}
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-emerald-100 text-emerald-800">
                                            {{ __('Booked') }}
                                        </span>
                                    @endif
                                </td>
                                <td class="py-2 text-right">
                                    @if ($appointment->status === 'booked')
                                        <form method="POST" action="{{ route('appointments.destroy', $appointment) }}" onsubmit="return confirm('Cancel this appointment?');">
                                            @csrf
                                            @method('DELETE')
                                            <x-danger-button>{{ __('Cancel') }}</x-danger-button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-4 text-slate-500">You have no appointments yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
