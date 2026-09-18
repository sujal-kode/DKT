<x-app-layout>
    <x-slot name="header">
        <h2 class="font-headline font-bold text-xl text-slate-900 leading-tight">
            {{ __('Doctor Availability & Breaks') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-8">
            @if ($errors->any())
                <div class="p-4 bg-red-50 border border-red-200 rounded-xl text-sm text-red-700">
                    <ul class="list-disc list-inside space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @forelse ($doctors as $doctor)
                <div class="bg-white border border-slate-200/80 overflow-hidden shadow-sm shadow-slate-200/40 sm:rounded-xl p-6 space-y-6">
                    <div class="border-b border-slate-100 pb-4">
                        <h3 class="font-headline text-lg font-semibold text-slate-900">{{ $doctor->name }} <span class="text-sm font-normal text-slate-500">({{ $doctor->specialization }})</span></h3>
                    </div>

                    <!-- Availability Windows -->
                    <div>
                        <h4 class="font-semibold text-sm text-slate-700 mb-3 uppercase tracking-wider text-xs">Set Availability Window</h4>
                        <form method="POST" action="{{ route('admin.availability.store', $doctor) }}" class="flex flex-col sm:flex-row gap-4 mb-4">
                            @csrf
                            <div class="flex-1">
                                <x-input-label for="avail_date_{{ $doctor->id }}" value="Date" />
                                <x-text-input id="avail_date_{{ $doctor->id }}" name="date" type="date" class="mt-1 block w-full text-sm" required />
                            </div>
                            <div class="flex-1">
                                <x-input-label for="avail_start_{{ $doctor->id }}" value="Start Time" />
                                <x-text-input id="avail_start_{{ $doctor->id }}" name="start_time" type="time" class="mt-1 block w-full text-sm" required />
                            </div>
                            <div class="flex-1">
                                <x-input-label for="avail_end_{{ $doctor->id }}" value="End Time" />
                                <x-text-input id="avail_end_{{ $doctor->id }}" name="end_time" type="time" class="mt-1 block w-full text-sm" required />
                            </div>
                            <div class="flex items-end">
                                <x-primary-button class="whitespace-nowrap">{{ __('Add Availability') }}</x-primary-button>
                            </div>
                        </form>

                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-sm">
                                <thead>
                                    <tr class="border-b border-slate-100">
                                        <th class="py-2 text-xs font-semibold text-slate-500 uppercase tracking-wide">Date</th>
                                        <th class="py-2 text-xs font-semibold text-slate-500 uppercase tracking-wide">Window</th>
                                        <th class="py-2 text-xs font-semibold text-slate-500 uppercase tracking-wide text-right">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($doctor->availabilities as $availability)
                                        <tr class="border-b border-slate-100 last:border-0">
                                            <td class="py-2">{{ $availability->date->format('D, M j, Y') }}</td>
                                            <td class="py-2">{{ \Illuminate\Support\Carbon::parse($availability->start_time)->format('g:i A') }} - {{ \Illuminate\Support\Carbon::parse($availability->end_time)->format('g:i A') }}</td>
                                            <td class="py-2 text-right">
                                                <form method="POST" action="{{ route('admin.availability.destroy', $availability) }}" onsubmit="return confirm('Delete this availability period?');" class="inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-red-600 hover:text-red-800 text-xs font-medium">{{ __('Delete') }}</button>
                                                </form>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3" class="py-3 text-slate-400 text-center italic">No availability periods set.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Doctor Breaks -->
                    <div class="border-t border-slate-100 pt-6">
                        <h4 class="font-semibold text-sm text-slate-700 mb-3 uppercase tracking-wider text-xs">Add Break Window</h4>
                        <form method="POST" action="{{ route('admin.breaks.store', $doctor) }}" class="flex flex-col sm:flex-row gap-4 mb-4">
                            @csrf
                            <div class="flex-1">
                                <x-input-label for="break_date_{{ $doctor->id }}" value="Date" />
                                <x-text-input id="break_date_{{ $doctor->id }}" name="date" type="date" class="mt-1 block w-full text-sm" required />
                            </div>
                            <div class="flex-1">
                                <x-input-label for="break_start_{{ $doctor->id }}" value="Start Time" />
                                <x-text-input id="break_start_{{ $doctor->id }}" name="start_time" type="time" class="mt-1 block w-full text-sm" required />
                            </div>
                            <div class="flex-1">
                                <x-input-label for="break_end_{{ $doctor->id }}" value="End Time" />
                                <x-text-input id="break_end_{{ $doctor->id }}" name="end_time" type="time" class="mt-1 block w-full text-sm" required />
                            </div>
                            <div class="flex items-end">
                                <x-secondary-button type="submit" class="whitespace-nowrap">{{ __('Add Break') }}</x-secondary-button>
                            </div>
                        </form>

                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-sm">
                                <thead>
                                    <tr class="border-b border-slate-100">
                                        <th class="py-2 text-xs font-semibold text-slate-500 uppercase tracking-wide">Date</th>
                                        <th class="py-2 text-xs font-semibold text-slate-500 uppercase tracking-wide">Break Window</th>
                                        <th class="py-2 text-xs font-semibold text-slate-500 uppercase tracking-wide text-right">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($doctor->breaks as $break)
                                        <tr class="border-b border-slate-100 last:border-0">
                                            <td class="py-2">{{ $break->date->format('D, M j, Y') }}</td>
                                            <td class="py-2 text-amber-700 font-medium">{{ \Illuminate\Support\Carbon::parse($break->start_time)->format('g:i A') }} - {{ \Illuminate\Support\Carbon::parse($break->end_time)->format('g:i A') }}</td>
                                            <td class="py-2 text-right">
                                                <form method="POST" action="{{ route('admin.breaks.destroy', $break) }}" onsubmit="return confirm('Delete this break and restore free slots?');" class="inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-red-600 hover:text-red-800 text-xs font-medium">{{ __('Delete') }}</button>
                                                </form>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3" class="py-3 text-slate-400 text-center italic">No breaks scheduled.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @empty
                <div class="bg-white border border-slate-200/80 overflow-hidden shadow-sm shadow-slate-200/40 sm:rounded-xl p-6 text-slate-500">
                    No doctors yet. <a href="{{ route('admin.doctors.index') }}" class="text-primary font-medium hover:text-primary-hover hover:underline">Add one first.</a>
                </div>
            @endforelse
        </div>
    </div>
</x-app-layout>
