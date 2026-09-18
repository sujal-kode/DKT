<x-app-layout>
    <x-slot name="header">
        <h2 class="font-headline font-bold text-xl text-slate-900 leading-tight">
            {{ __('Available Doctors') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white border border-slate-200/80 overflow-hidden shadow-sm shadow-slate-200/40 sm:rounded-xl p-6">
                <table class="w-full text-left">
                    <thead>
                        <tr class="border-b border-slate-100">
                            <th class="py-2 text-xs font-semibold text-slate-500 uppercase tracking-wide">Name</th>
                            <th class="py-2 text-xs font-semibold text-slate-500 uppercase tracking-wide">Specialization</th>
                            <th class="py-2 text-xs font-semibold text-slate-500 uppercase tracking-wide"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($doctors as $doctor)
                            <tr class="border-b border-slate-100">
                                <td class="py-2">{{ $doctor->name }}</td>
                                <td class="py-2">{{ $doctor->specialization }}</td>
                                <td class="py-2 text-right">
                                    <a href="{{ route('doctors.slots', $doctor) }}" class="text-primary font-medium hover:text-primary-hover hover:underline">View Slots</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="py-4 text-slate-500">No doctors available yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
