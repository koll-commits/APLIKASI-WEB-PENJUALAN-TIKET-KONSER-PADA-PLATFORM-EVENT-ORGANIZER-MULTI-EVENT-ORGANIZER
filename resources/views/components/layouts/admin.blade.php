@props(['title' => 'Admin'])
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="antialiased">
<head>
    @include('partials.head')
</head>
<body class="min-h-screen bg-zinc-50 text-zinc-900 dark:bg-zinc-950 dark:text-zinc-100">
    <div x-data="{ open: false }" class="flex min-h-screen">
        <aside :class="open ? 'translate-x-0' : '-translate-x-full'"
            class="fixed inset-y-0 left-0 z-20 w-64 transform border-r border-zinc-200 bg-white p-4 transition-transform duration-200 lg:static lg:translate-x-0 dark:border-zinc-800 dark:bg-zinc-900">
            <div class="mb-6 text-lg font-semibold">Pengelola Event</div>
            <nav class="flex flex-col gap-1 text-sm">
                <a href="{{ route('admin.dashboard') }}"
                    class="rounded-lg px-3 py-2 font-medium {{ request()->routeIs('admin.dashboard') ? 'bg-zinc-100 dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100' : 'text-zinc-600 hover:bg-zinc-100 dark:text-zinc-400 dark:hover:bg-zinc-800' }}">
                    Ringkasan
                </a>
                <a href="{{ route('admin.tenants.index') }}"
                    class="rounded-lg px-3 py-2 font-medium {{ request()->routeIs('admin.tenants.*') ? 'bg-zinc-100 dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100' : 'text-zinc-600 hover:bg-zinc-100 dark:text-zinc-400 dark:hover:bg-zinc-800' }}">
                    Tenant &amp; Komisi
                </a>
                <span class="px-3 py-2 text-zinc-400">Meja &amp; QR Code (Modul 6)</span>
                <span class="px-3 py-2 text-zinc-400">Pencairan Dana (Modul 10)</span>
                <span class="px-3 py-2 text-zinc-400">Jejak Audit</span>
            </nav>
        </aside>

        <div class="flex min-w-0 flex-1 flex-col">
            <header class="flex min-h-14 items-center justify-between border-b border-zinc-200 bg-white px-4 lg:px-6 dark:border-zinc-800 dark:bg-zinc-900">
                <div class="flex items-center gap-3">
                    <button type="button" @click="open = !open" class="inline-flex min-h-11 min-w-11 items-center justify-center rounded-lg lg:hidden" aria-label="Toggle sidebar">☰</button>
                    <span class="font-semibold">{{ $title }}</span>
                </div>
                <div class="flex items-center gap-4">
                    <span class="text-sm text-zinc-500">{{ auth()->user()?->name }}</span>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="rounded-lg bg-zinc-100 px-3 py-1.5 text-xs font-medium text-red-600 hover:bg-red-50 dark:bg-zinc-800 dark:text-red-400 dark:hover:bg-red-950/40">
                            Keluar
                        </button>
                    </form>
                </div>
            </header>
            <main class="flex-1 p-6">{{ $slot }}</main>
        </div>
    </div>
</body>
</html>
