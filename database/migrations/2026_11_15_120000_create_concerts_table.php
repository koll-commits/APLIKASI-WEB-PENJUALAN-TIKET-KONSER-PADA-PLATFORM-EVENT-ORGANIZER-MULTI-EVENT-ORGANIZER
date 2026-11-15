<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Event/konser milik satu EO (tenant). Jenis tiket menyusul pada modul Ticketing berikutnya. */
    public function up(): void
    {
        Schema::create('concerts', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('name', 150);
            $table->string('slug', 160);
            $table->string('venue', 150);
            $table->string('city', 80);
            $table->dateTime('starts_at');
            $table->dateTime('ends_at')->nullable();
            $table->text('description')->nullable();
            $table->string('status', 20)->default('draft'); // draft|published|cancelled
            $table->timestamps(6);

            $table->unique(['tenant_id', 'slug']);
            $table->unique(['tenant_id', 'id']); // target composite FK untuk tabel anak (jenis tiket)
            $table->index(['tenant_id', 'status', 'starts_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('concerts');
    }
};
