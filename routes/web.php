<?php

use App\Http\Controllers\DashboardRedirectController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    $events = \App\Models\Event::query()
        ->whereIn('status', ['active', 'published'])
        ->withCount(['tenants' => fn ($q) => $q->where('status', 'active')])
        ->latest('id')
        ->get();

    return view('welcome', compact('events'));
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', DashboardRedirectController::class)->name('dashboard');
});
require __DIR__.'/settings.php';
