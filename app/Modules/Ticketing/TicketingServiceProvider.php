<?php

namespace App\Modules\Ticketing;

use App\Modules\ModuleServiceProvider;

/**
 * Modul: ticketing (alias `ticketing`).
 * Tanggung jawab: Laporan scoped, rekonsiliasi, ekspor async, withdrawal (Modul 13).
 *
 * Titik perakitan modul: binding container di register(); route (routes/*.php), view
 * (`ticketing::`) dan komponen Livewire (`<livewire:ticketing::...>`) dimuat oleh
 * ModuleServiceProvider::boot(). Batas antarmodul ditegakkan lewat kontrak & event,
 * bukan akses langsung tabel/controller modul lain.
 */
final class TicketingServiceProvider extends ModuleServiceProvider
{
    public function register(): void
    {
        // Binding kontrak -> implementasi ditambahkan saat modul diimplementasikan.
    }

    protected function moduleAlias(): string
    {
        return 'ticketing';
    }
}
