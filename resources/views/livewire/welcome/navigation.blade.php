<nav class="-mx-3 flex flex-1 justify-end">
    @auth
        <a
            href="{{ url('/app') }}"
            class="rounded-md px-3 py-2 bg-white text-black font-medium ring-1 ring-transparent transition hover:text-mc-purple/70 hover:ring-black/20 focus:outline-none focus-visible:ring-[#FF2D20] dark:text-white dark:hover:text-white/80 dark:focus-visible:ring-white"
        >
            Dashboard
        </a>
    @else
        <a
            href="{{ route('filament.app.auth.login') }}"
            class="rounded-md px-3 py-2 mr-2 bg-white text-black font-medium ring-1 ring-transparent transition hover:text-mc-purple/70 hover:ring-black/20 focus:outline-none focus-visible:ring-[#FF2D20] dark:text-white dark:hover:text-white/80 dark:focus-visible:ring-white"
        >
            Log in
        </a>

        @if (Route::has('register'))
            <a
                href="{{ route('filament.app.auth.register') }}"
                class="rounded-md px-3 py-2 bg-white text-black font-medium ring-1 ring-transparent transition hover:text-mc-purple/70 hover:ring-black/20 focus:outline-none focus-visible:ring-[#FF2D20] dark:text-white dark:hover:text-white/80 dark:focus-visible:ring-white"
            >
                Register
            </a>
        @endif
    @endauth
</nav>
