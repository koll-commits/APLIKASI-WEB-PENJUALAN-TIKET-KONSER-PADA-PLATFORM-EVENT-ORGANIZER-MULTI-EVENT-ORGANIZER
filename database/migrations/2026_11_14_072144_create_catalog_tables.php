<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Domain C — Katalog & Konfigurasi Event Organizer (StageTix).
 * Mendukung penjualan multi-produk: Tiket Konser, Merchandise Resmi, dan Fasilitas Tambahan.
 * Isolasi lintas-tenant ditegakkan DB: composite FK (tenant_id, x_id) -> parent(tenant_id, id).
 */
return new class extends Migration
{
    public function up(): void
    {
        // 1. Jam Operasional Stand / Loket EO
        Schema::create('tenant_operating_hours', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('day_of_week'); // 0=Minggu .. 6=Sabtu
            $table->time('opens_at');
            $table->time('closes_at');
            $table->timestamps(6);

            $table->unique(['tenant_id', 'day_of_week']);
        });

        // 2. Skema Komisi Bagi Hasil Platform & EO (Effective-dated)
        Schema::create('commission_schemes', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->decimal('commission_rate', 6, 4); // misal 0.1500 = 15%
            $table->timestampTz('valid_from', 6);
            $table->timestampTz('valid_to', 6)->nullable(); // null = masih berlaku
            $table->timestamps(6);

            $table->unique(['tenant_id', 'id']); // target FK snapshot komisi
            $table->index(['tenant_id', 'valid_from']);
        });

        // 3. Kategori Katalog (Tiket Konser, Official Merchandise, Parking Pass, dll)
        Schema::create('catalog_categories', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('name', 120);
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps(6);

            $table->unique(['tenant_id', 'name']);
            $table->unique(['tenant_id', 'id']); // target composite FK
        });

        // 4. Grup Modifier / Varian (Ukuran Baju, Tambahan Akses Backstage, dll)
        Schema::create('modifier_groups', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('name', 120);
            $table->unsignedSmallInteger('min_select')->default(0);
            $table->unsignedSmallInteger('max_select')->default(1);
            $table->boolean('is_active')->default(true);
            $table->timestamps(6);

            $table->unique(['tenant_id', 'id']); // target composite FK
        });

        // 5. Item Katalog (Tiket, Merchandise, Akses Acara)
        Schema::create('catalogs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('category_id');
            $table->string('name', 120);
            $table->unsignedBigInteger('base_price'); // Harga dalam Rupiah (integer)
            $table->integer('stock_qty')->default(0); // Kuota tiket / stok barang
            $table->boolean('is_available')->default(true);
            $table->unsignedSmallInteger('prep_minutes')->default(0);
            $table->timestamps(6);

            $table->unique(['tenant_id', 'id']); // target composite FK untuk order_items
            $table->index(['tenant_id', 'category_id', 'is_available']);
            // FK Komposit: Kategori harus milik EO yang sama
            $table->foreign(['tenant_id', 'category_id'])
                ->references(['tenant_id', 'id'])->on('catalog_categories')->restrictOnDelete();
        });

        // 6. Opsi Varian / Modifier (Pilihan Size Kaos, Paket Foto, dll)
        Schema::create('modifier_options', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('group_id');
            $table->string('name', 120);
            $table->bigInteger('price_delta')->default(0); // Tambahan harga (+/-)
            $table->integer('stock_qty')->default(0);
            $table->boolean('is_available')->default(true);
            $table->timestamps(6);

            $table->unique(['tenant_id', 'id']); // target composite FK
            // FK Komposit: Grup varian harus milik EO yang sama
            $table->foreign(['tenant_id', 'group_id'])
                ->references(['tenant_id', 'id'])->on('modifier_groups')->restrictOnDelete();
        });

        // 7. Pivot Item Katalog ke Grup Varian (Relasi Many-to-Many)
        Schema::create('catalog_modifier_groups', function (Blueprint $table): void {
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('catalog_id');
            $table->unsignedBigInteger('modifier_group_id');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps(6);

            $table->primary(['catalog_id', 'modifier_group_id']);
            $table->foreign(['tenant_id', 'catalog_id'])
                ->references(['tenant_id', 'id'])->on('catalogs')->cascadeOnDelete();
            $table->foreign(['tenant_id', 'modifier_group_id'])
                ->references(['tenant_id', 'id'])->on('modifier_groups')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('catalog_modifier_groups');
        Schema::dropIfExists('modifier_options');
        Schema::dropIfExists('catalogs');
        Schema::dropIfExists('modifier_groups');
        Schema::dropIfExists('catalog_categories');
        Schema::dropIfExists('commission_schemes');
        Schema::dropIfExists('tenant_operating_hours');
    }
};