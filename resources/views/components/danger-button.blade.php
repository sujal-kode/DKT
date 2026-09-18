<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center px-3 py-1.5 bg-rose-50 text-rose-600 border border-rose-200 rounded-lg font-semibold text-xs hover:bg-rose-100 focus:outline-none focus:ring-2 focus:ring-rose-400 focus:ring-offset-2 transition-colors duration-150']) }}>
    {{ $slot }}
</button>
