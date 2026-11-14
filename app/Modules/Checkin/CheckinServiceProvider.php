<?php

namespace App\Modules\Checkin;

use App\Modules\ModuleServiceProvider;

/**
 * Modul: Checkin (alias `Checkin`).
 * Tanggung jawab: Checkin event.
 *
 * Titik perakitan modul: binding container di register(); route (routes/*.php), view
 * (`Checkin::`) dan komponen Livewire (`<livewire:Checkin::...>`) dimuat oleh
 * ModuleServiceProvider::boot(). Batas antarmodul ditegakkan lewat kontrak & event,
 * bukan akses langsung tabel/controller modul lain.
 */
final class CheckinServiceProvider extends ModuleServiceProvider
{
    public function register(): void
    {
        // Binding kontrak -> implementasi ditambahkan saat modul diimplementasikan.
    }

    protected function moduleAlias(): string
    {
        return 'checkin';
    }
}
