<x-layouts.tenant title="Dashboard">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <div class="flex items-center gap-3">
                <h1 class="text-xl font-semibold">{{ $tenant->display_name }}</h1>
                <x-status-badge :status="$tenant->status">{{ $tenant->status }}</x-status-badge>
            </div>
            <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">Slug: <span class="font-mono">{{ $tenant->slug }}</span></p>
        </div>
        @can('create', \App\Models\Event::class)
            <a href="{{ route('tenant.concerts.create', ['tenant' => $tenant->slug]) }}"><x-button>+ Tambah event</x-button></a>
        @endcan
    </div>

    <div class="mt-6 grid gap-4 sm:grid-cols-3">
        @foreach ([['Total event', $stats['total']], ['Terbit', $stats['published']], ['Draf', $stats['draft']]] as [$label, $value])
            <a href="{{ route('tenant.concerts.index', ['tenant' => $tenant->slug]) }}"
               class="rounded-xl border border-zinc-200 bg-white p-4 transition hover:border-zinc-400 dark:border-zinc-800 dark:bg-zinc-900">
                <div class="text-sm text-zinc-500">{{ $label }}</div>
                <div class="mt-1 text-2xl font-semibold">{{ $value }}</div>
            </a>
        @endforeach
    </div>

    <section class="mt-8">
        <div class="mb-2 flex items-center justify-between">
            <h2 class="font-semibold">Event mendatang</h2>
            <a class="text-sm underline" href="{{ route('tenant.concerts.index', ['tenant' => $tenant->slug]) }}">Kelola semua</a>
        </div>
        @if ($upcoming->isEmpty())
            <x-empty-state title="Belum ada event mendatang" description="Tambahkan event pertama Anda untuk mulai menjual tiket." />
        @else
            <ul class="divide-y divide-zinc-200 rounded-xl border border-zinc-200 bg-white text-sm dark:divide-zinc-800 dark:border-zinc-800 dark:bg-zinc-900">
                @foreach ($upcoming as $concert)
                    <li class="flex flex-wrap items-center justify-between gap-2 px-4 py-3">
                        <div>
                            <div class="font-medium">{{ $concert->name }}</div>
                            <div class="text-xs text-zinc-500">{{ $concert->venue }}, {{ $concert->city }} · {{ $concert->starts_at->translatedFormat('j M Y, H:i') }}</div>
                        </div>
                        <x-status-badge :status="$concert->status === 'published' ? 'active' : 'pending'">{{ \App\Models\Event::STATUSES[$concert->status] ?? $concert->status }}</x-status-badge>
                    </li>
                @endforeach
            </ul>
        @endif
    </section>
</x-layouts.tenant>
