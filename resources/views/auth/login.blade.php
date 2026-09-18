<x-auth-split heading="Welcome back." subheading="Sign in to view your appointments, book new ones, or manage your schedule.">
    <div class="mb-8">
        <h1 class="font-headline font-bold text-2xl tracking-tight text-slate-900 mb-1">Sign in</h1>
        <p class="text-sm text-slate-500">Enter your details to access your account</p>
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="space-y-5">
        @csrf

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block w-full" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div>
            <div class="flex items-center justify-between mb-1.5">
                <x-input-label for="password" :value="__('Password')" class="mb-0" />
                @if (Route::has('password.request'))
                    <a class="text-xs font-medium text-primary hover:text-primary-hover hover:underline" href="{{ route('password.request') }}">
                        {{ __('Forgot your password?') }}
                    </a>
                @endif
            </div>

            <x-text-input id="password" class="block w-full"
                            type="password"
                            name="password"
                            required autocomplete="current-password" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Remember Me -->
        <div class="flex items-center">
            <input id="remember_me" type="checkbox" class="h-4 w-4 rounded border-slate-300 text-primary focus:ring-primary" name="remember">
            <label for="remember_me" class="ml-2.5 text-xs text-slate-600 select-none">{{ __('Remember me') }}</label>
        </div>

        <x-primary-button class="w-full">
            {{ __('Log in') }}
        </x-primary-button>

        <p class="text-center text-xs text-slate-600">
            {{ __("Don't have an account?") }}
            <a href="{{ route('register') }}" class="font-semibold text-primary hover:text-primary-hover hover:underline">{{ __('Register') }}</a>
        </p>
    </form>
</x-auth-split>
