<x-app-layout>
    <x-slot name="header">
        <h2 class="font-headline font-bold text-xl text-slate-900 leading-tight">
            {{ __('Manage Doctors') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white border border-slate-200/80 overflow-hidden shadow-sm shadow-slate-200/40 sm:rounded-xl p-6">
                <h3 class="font-headline text-lg font-semibold text-slate-900 mb-4">Add a Doctor</h3>
                <form method="POST" action="{{ route('admin.doctors.store') }}" class="flex flex-col sm:flex-row gap-4">
                    @csrf
                    <div class="flex-1">
                        <x-input-label for="name" value="Name" />
                        <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" value="{{ old('name') }}" required />
                        <x-input-error :messages="$errors->get('name')" class="mt-2" />
                    </div>
                    <div class="flex-1">
                        <x-input-label for="specialization" value="Specialization" />
                        <x-text-input id="specialization" name="specialization" type="text" class="mt-1 block w-full" value="{{ old('specialization') }}" required />
                        <x-input-error :messages="$errors->get('specialization')" class="mt-2" />
                    </div>
                    <div class="flex items-end">
                        <x-primary-button>{{ __('Add Doctor') }}</x-primary-button>
                    </div>
                </form>
            </div>

            <div class="bg-white border border-slate-200/80 overflow-hidden shadow-sm shadow-slate-200/40 sm:rounded-xl p-6">
                <h3 class="font-headline text-lg font-semibold text-slate-900 mb-4">Doctors</h3>
                <table class="w-full text-left">
                    <thead>
                        <tr class="border-b border-slate-100">
                            <th class="py-2 text-xs font-semibold text-slate-500 uppercase tracking-wide">Name</th>
                            <th class="py-2 text-xs font-semibold text-slate-500 uppercase tracking-wide">Specialization</th>
                            <th class="py-2 text-xs font-semibold text-slate-500 uppercase tracking-wide">Availability Days Set</th>
                            <th class="py-2 text-xs font-semibold text-slate-500 uppercase tracking-wide"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($doctors as $doctor)
                            <tr class="border-b border-slate-100">
                                <td class="py-2">{{ $doctor->name }}</td>
                                <td class="py-2">{{ $doctor->specialization }}</td>
                                <td class="py-2">{{ $doctor->availabilities_count }}</td>
                                <td class="py-2 text-right">
                                    <form method="POST" action="{{ route('admin.doctors.destroy', $doctor) }}" onsubmit="return confirm('Remove this doctor?');">
                                        @csrf
                                        @method('DELETE')
                                        <x-danger-button>{{ __('Remove') }}</x-danger-button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="py-4 text-slate-500">No doctors yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
