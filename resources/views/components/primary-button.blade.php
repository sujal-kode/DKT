<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center justify-center px-4 py-2.5 bg-primary border border-transparent rounded-lg font-semibold text-sm text-white hover:bg-primary-hover active:scale-[0.99] focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2 shadow-md shadow-teal-600/20 transition-all duration-150']) }}>
    {{ $slot }}
</button>
