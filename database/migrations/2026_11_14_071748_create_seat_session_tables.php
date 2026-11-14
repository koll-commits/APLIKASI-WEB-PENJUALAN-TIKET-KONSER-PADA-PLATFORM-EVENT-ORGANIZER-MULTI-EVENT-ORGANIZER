<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Domain B — Kursi/Spot Event, QR Token Tempat, dan Sesi Penonton (StageTix).
 * Token disimpan sebagai hash BINARY(32); tidak menyimpan token mentah.
 */
return new class extends Migration
{
    public function up(): void
    {
        // 1. Kursi / Spot / Meja VIP di Event
        Schema::create('event_seats', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('event_id')->constrained('events')->restrictOnDelete();
            $table->string('code', 30);       // misal: 'SEAT-A01', 'VIP-TABLE-05', 'FEST-01'
            $table->string('label', 60);      // misal: 'Kursi VIP Baris A No 1'
            $table->string('zone', 60)->nullable(); // misal: 'VIP Lounge', 'Tribune Utara', 'Festival'
            $table->string('status', 20)->default('active'); // active|inactive|reserved
            $table->timestamps(6);

            $table->unique(['event_id', 'code']);
            $table->unique(['id', 'event_id']); // target composite FK
        });

        // 2. Token QR pada Kursi / Meja VIP
        Schema::create('seat_qr_tokens', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('event_seat_id')->constrained('event_seats')->cascadeOnDelete();
            $table->binary('token_hash', 32); // SHA-256 (32 bytes)
            $table->string('status', 20)->default('active'); // active|revoked|expired
            $table->timestampTz('issued_at', 6)->nullable();
            $table->timestampTz('expires_at', 6)->nullable();
            $table->timestampTz('revoked_at', 6)->nullable();
            $table->timestamps(6);

            $table->unique('token_hash');
            $table->index(['event_seat_id', 'status']);
        });

        // 3. Sesi Penonton (Menampung Keranjang & Alur Checkout Tiket)
        Schema::create('customer_sessions', function (Blueprint $table): void {
            $table->ulid('id')->primary(); // ULID transaksional
            $table->foreignId('event_id')->constrained('events')->restrictOnDelete();
            $table->foreignId('event_seat_id')->nullable()->constrained('event_seats')->nullOnDelete();
            $table->foreignId('qr_token_id')->nullable()->constrained('seat_qr_tokens')->nullOnDelete();
            $table->binary('session_token_hash', 32); // SHA-256 session token
            $table->string('status', 20)->default('active'); // active|expired|closed
            $table->timestampTz('expires_at', 6)->nullable();
            $table->timestamps(6);

            $table->unique('session_token_hash');
            $table->index(['event_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_sessions');
        Schema::dropIfExists('seat_qr_tokens');
        Schema::dropIfExists('event_seats');
    }
};
