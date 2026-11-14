<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Domain E — Pembayaran & Keuangan (StageTix).
 * - payment_events & ledger_entries bersifat append-only (koreksi via reversal, bukan edit).
 * - idempotency_key unik pada setiap operasi kritis finansial.
 * - Menegakkan batas maksimal satu withdrawal aktif per EO (active_tenant_lock).
 */
return new class extends Migration
{
    public function up(): void
    {
        // 1. Pembayaran Induk Order
        Schema::create('payments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('order_id')->constrained()->restrictOnDelete();
            $table->string('payment_reference', 100);
            $table->string('idempotency_key', 100);
            $table->unsignedBigInteger('amount'); // Nominal Rupiah integer
            $table->string('status', 30)->default('pending'); // pending|paid|failed|expired|refunded
            $table->timestampTz('settled_at', 6)->nullable();
            $table->timestamps(6);

            $table->unique('order_id');
            $table->unique('payment_reference');
            $table->unique('idempotency_key');
        });

        // 2. Upaya Pembayaran (Gateway / QRIS / VA)
        Schema::create('payment_attempts', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('payment_id')->constrained()->cascadeOnDelete();
            $table->string('provider_reference', 120);
            $table->text('qris_payload')->nullable();
            $table->string('status', 30)->default('created'); // created|pending|success|failed|expired
            $table->timestampTz('expires_at', 6)->nullable();
            $table->timestamps(6);

            $table->unique('provider_reference');
            $table->index(['payment_id', 'status']);
        });

        // 3. Webhook Callback Provider (Append-only & Dedup Guard)
        Schema::create('payment_events', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('payment_id')->constrained()->restrictOnDelete();
            $table->foreignId('payment_attempt_id')->nullable()->constrained()->nullOnDelete();
            $table->string('provider_event_id', 150);
            $table->string('signature', 255)->nullable();
            $table->json('payload');
            $table->string('result', 30); // verified|rejected|duplicate
            $table->timestampTz('received_at', 6);
            $table->timestamps(6);

            $table->unique('provider_event_id'); // Mencegah replay attack callback
            $table->index(['payment_id', 'result']);
        });

        // 4. Penarikan Dana EO (Payout / Withdrawal)
        Schema::create('withdrawals', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->restrictOnDelete();
            $table->foreignId('bank_account_id')->constrained('tenant_bank_accounts')->restrictOnDelete();
            $table->foreignId('requested_by')->constrained('users')->restrictOnDelete();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('idempotency_key', 100);
            $table->unsignedBigInteger('amount');
            $table->string('status', 30)->default('requested'); // requested|approved|processing|paid|rejected
            $table->json('transfer_snapshot')->nullable();
            // Menegakkan "maksimal satu withdrawal aktif per EO": diisi tenant_id saat aktif, null saat selesai/final
            $table->unsignedBigInteger('active_tenant_lock')->nullable();
            $table->timestamps(6);

            $table->unique('idempotency_key');
            $table->unique('active_tenant_lock');
            $table->index(['tenant_id', 'status']);
        });

        // 5. Buku Besar Saldo EO (Append-only Ledger)
        Schema::create('ledger_entries', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->restrictOnDelete();
            $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('payment_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('withdrawal_id')->nullable()->constrained()->nullOnDelete();
            $table->string('idempotency_key', 100);
            $table->string('type', 40); // sale_credit|commission_debit|hold|release|withdrawal_debit|reversal
            $table->bigInteger('available_delta')->default(0);
            $table->bigInteger('held_delta')->default(0);
            $table->timestamps(6);

            $table->unique('idempotency_key');
            $table->index(['tenant_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ledger_entries');
        Schema::dropIfExists('withdrawals');
        Schema::dropIfExists('payment_events');
        Schema::dropIfExists('payment_attempts');
        Schema::dropIfExists('payments');
    }
};
