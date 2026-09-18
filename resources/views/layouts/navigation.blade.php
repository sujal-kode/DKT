@php $isAdmin = Auth::user()->isAdmin(); @endphp
<nav x-data="{ open: false }" class="{{ $isAdmin ? 'bg-slate-900 border-b border-slate-800' : 'bg-white border-b border-slate-200/80' }}">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center gap-3">
                    <a href="{{ route('dashboard') }}" class="flex items-center">
                        @if ($isAdmin)
                            <span class="flex items-center gap-2 font-headline font-bold text-lg tracking-tight text-white">
                                <x-application-logo class="block h-6 w-6 text-teal-400" />
                                <span>{{ config('app.name') }}</span>
                            </span>
                        @else
                            <img src="{{ asset('images/logo.png') }}" alt="{{ config('app.name') }}" class="h-8 w-auto">
                        @endif
                    </a>
                    @if ($isAdmin)
                        <span class="hidden sm:inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold uppercase tracking-wide bg-teal-500/10 text-teal-400 border border-teal-500/30">
                            Admin
                        </span>
                    @endif
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                    @if ($isAdmin)
                        <x-nav-link :href="route('admin.doctors.index')" :active="request()->routeIs('admin.doctors.*')" class="!text-slate-300 hover:!text-white !border-transparent {{ request()->routeIs('admin.doctors.*') ? '!border-teal-400 !text-white' : '' }}">
                            {{ __('Doctors') }}
                        </x-nav-link>
                        <x-nav-link :href="route('admin.availability.index')" :active="request()->routeIs('admin.availability.*')" class="!text-slate-300 hover:!text-white !border-transparent {{ request()->routeIs('admin.availability.*') ? '!border-teal-400 !text-white' : '' }}">
                            {{ __('Availability') }}
                        </x-nav-link>
                    @else
                        <x-nav-link :href="route('doctors.index')" :active="request()->routeIs('doctors.*')">
                            {{ __('Doctors') }}
                        </x-nav-link>
                        <x-nav-link :href="route('appointments.index')" :active="request()->routeIs('appointments.*')">
                            {{ __('My Appointments') }}
                        </x-nav-link>
                    @endif
                </div>
            </div>

            <!-- Settings Dropdown -->
            <div class="hidden sm:flex sm:items-center sm:ms-6">
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md transition ease-in-out duration-150 focus:outline-none {{ $isAdmin ? 'text-slate-300 hover:text-white bg-transparent' : 'text-gray-500 hover:text-gray-700 bg-white' }}">
                            <div>{{ Auth::user()->name }}</div>

                            <div class="ms-1">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile.edit')">
                            {{ __('Profile') }}
                        </x-dropdown-link>

                        <!-- Authentication -->
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf

                            <x-dropdown-link :href="route('logout')"
                                    onclick="event.preventDefault();
                                                this.closest('form').submit();">
                                {{ __('Log Out') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Hamburger -->
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md transition duration-150 ease-in-out focus:outline-none {{ $isAdmin ? 'text-slate-400 hover:text-white hover:bg-slate-800 focus:bg-slate-800 focus:text-white' : 'text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:bg-gray-100 focus:text-gray-500' }}">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden {{ $isAdmin ? 'bg-slate-900' : '' }}">
        <div class="pt-2 pb-3 space-y-1">
            @if ($isAdmin)
                <x-responsive-nav-link :href="route('admin.doctors.index')" :active="request()->routeIs('admin.doctors.*')" class="!text-slate-300 hover:!text-white">
                    {{ __('Doctors') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('admin.availability.index')" :active="request()->routeIs('admin.availability.*')" class="!text-slate-300 hover:!text-white">
                    {{ __('Availability') }}
                </x-responsive-nav-link>
            @else
                <x-responsive-nav-link :href="route('doctors.index')" :active="request()->routeIs('doctors.*')">
                    {{ __('Doctors') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('appointments.index')" :active="request()->routeIs('appointments.*')">
                    {{ __('My Appointments') }}
                </x-responsive-nav-link>
            @endif
        </div>

        <!-- Responsive Settings Options -->
        <div class="pt-4 pb-1 border-t {{ $isAdmin ? 'border-slate-800' : 'border-gray-200' }}">
            <div class="px-4">
                <div class="font-medium text-base {{ $isAdmin ? 'text-white' : 'text-gray-800' }}">{{ Auth::user()->name }}</div>
                <div class="font-medium text-sm {{ $isAdmin ? 'text-slate-400' : 'text-gray-500' }}">{{ Auth::user()->email }}</div>
            </div>

            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile.edit')">
                    {{ __('Profile') }}
                </x-responsive-nav-link>

                <!-- Authentication -->
                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    <x-responsive-nav-link :href="route('logout')"
                            onclick="event.preventDefault();
                                        this.closest('form').submit();">
                        {{ __('Log Out') }}
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>
