<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="antialiased">
<head>
    @include('partials.head')
    <title>StageTix — Platform Tiket Konser & Merchandise</title>
</head>
<body class="min-h-screen bg-zinc-50 text-zinc-900 dark:bg-zinc-950 dark:text-zinc-100 flex flex-col justify-between">

    <!-- Header / Navbar -->
    <header class="sticky top-0 z-20 border-b border-zinc-200 bg-white/90 backdrop-blur dark:border-zinc-800 dark:bg-zinc-900/90">
        <div class="mx-auto flex h-16 max-w-6xl items-center justify-between px-4 sm:px-6">
            <div class="flex items-center gap-3">
                <span class="flex size-9 items-center justify-center rounded-lg bg-red-600 text-white font-bold text-lg">ST</span>
                <span class="text-xl font-bold tracking-tight">Stage<span class="text-red-600">Tix</span></span>
            </div>

            <div class="flex items-center gap-3">
                @auth
                    <a href="{{ route('dashboard') }}" class="rounded-lg bg-zinc-100 px-4 py-2 text-sm font-medium hover:bg-zinc-200 dark:bg-zinc-800 dark:hover:bg-zinc-700">
                        Dashboard Saya ({{ auth()->user()->name }}) →
                    </a>
                @else
                    <a href="{{ route('login') }}" class="rounded-lg border border-zinc-300 px-4 py-2 text-sm font-medium hover:bg-zinc-100 dark:border-zinc-700 dark:hover:bg-zinc-800">
                        Masuk
                    </a>
                    @if (Route::has('register'))
                        <a href="{{ route('register') }}" class="rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700">
                            Daftar
                        </a>
                    @endif
                @endauth
            </div>
        </div>
    </header>

    <!-- Hero Section -->
    <section class="border-b border-zinc-200 bg-white py-12 dark:border-zinc-800 dark:bg-zinc-900">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 text-center">
            <span class="rounded-full bg-red-100 px-3 py-1 text-xs font-semibold text-red-700 dark:bg-red-950/60 dark:text-red-300">
                Multi-Tenant Concert & Event Ticketing
            </span>
            <h1 class="mt-4 text-3xl font-extrabold tracking-tight sm:text-5xl">
                Temukan Konser &amp; Event Favoritmu
            </h1>
            <p class="mx-auto mt-3 max-w-2xl text-base text-zinc-500 sm:text-lg dark:text-zinc-400">
                Pesan tiket resmi, merchandise eksklusif, dan akses konser langsung dari Event Organizer terpercaya.
            </p>
        </div>
    </section>

    <!-- Katalog Event Grid -->
    <main class="mx-auto max-w-6xl flex-1 px-4 py-10 sm:px-6 w-full">
        <div class="mb-6 flex items-center justify-between">
            <h2 class="text-xl font-bold">Event &amp; Konser Tersedia</h2>
            <span class="text-sm text-zinc-500">{{ $events->count() }} Event Aktif</span>
        </div>

        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @forelse ($events as $event)
                <div class="group flex flex-col justify-between overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-sm transition hover:shadow-md dark:border-zinc-800 dark:bg-zinc-900">
                    <div class="p-6">
                        <div class="flex items-center justify-between">
                            <span class="rounded-md bg-zinc-100 px-2 py-1 text-xs font-semibold text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300">
                                {{ $event->city ?? 'Indonesia' }}
                            </span>
                            <span class="text-xs text-red-600 font-medium">
                                {{ $event->tenants_count ?? 0 }} Booth / EO
                            </span>
                        </div>

                        <h3 class="mt-4 text-lg font-bold group-hover:text-red-600 transition">
                            {{ $event->name }}
                        </h3>

                        <p class="mt-2 text-xs text-zinc-500 line-clamp-2 dark:text-zinc-400">
                            {{ $event->venue ? $event->venue . ' · ' : '' }}{{ $event->starts_at ? $event->starts_at->translatedFormat('d M Y, H:i') : 'Jadwal diumumkan segera' }}
                        </p>
                    </div>

                    <div class="border-t border-zinc-100 bg-zinc-50/50 p-4 dark:border-zinc-800 dark:bg-zinc-900/50">
                        <a href="{{ route('customer.home', ['event' => $event->slug]) }}"
                           class="flex w-full items-center justify-center rounded-xl bg-red-600 py-2.5 text-sm font-semibold text-white shadow transition hover:bg-red-700">
                            Pilih Event &amp; Lihat Menu Tiket →
                        </a>
                    </div>
                </div>
            @empty
                <div class="col-span-full rounded-2xl border border-dashed border-zinc-300 p-12 text-center dark:border-zinc-700">
                    <p class="font-medium text-zinc-700 dark:text-zinc-300">Belum ada event yang aktif saat ini.</p>
                    <p class="mt-1 text-xs text-zinc-500">Silakan login sebagai Admin atau EO untuk membuat event baru.</p>
                    <a href="{{ route('login') }}" class="mt-4 inline-block rounded-lg bg-zinc-900 px-4 py-2 text-sm text-white dark:bg-zinc-100 dark:text-zinc-900">
                        Masuk ke Dashboard
                    </a>
                </div>
            @endforelse
        </div>
    </main>

    <!-- Footer -->
    <footer class="border-t border-zinc-200 bg-white py-6 text-center text-xs text-zinc-500 dark:border-zinc-800 dark:bg-zinc-900 dark:text-zinc-400">
        &copy; {{ date('Y') }} StageTix — Multi-Tenant Ticketing &amp; Event Platform.
    </footer>

    @fluxScripts
</body>
</html>