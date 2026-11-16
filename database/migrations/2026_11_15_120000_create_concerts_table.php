<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Event/konser milik satu EO (tenant) disimpan langsung di tabel events. */
    public function up(): void
    {
        Schema::dropIfExists('concerts');

        Schema::table('events', function (Blueprint $table): void {
            if (! Schema::hasColumn('events', 'tenant_id')) {
                $table->foreignId('tenant_id')->nullable()->after('id')->constrained('tenants')->cascadeOnDelete();
            }
            if (! Schema::hasColumn('events', 'venue')) {
                $table->string('venue', 150)->nullable()->after('name');
            }
            if (! Schema::hasColumn('events', 'city')) {
                $table->string('city', 80)->nullable()->after('venue');
            }
            if (! Schema::hasColumn('events', 'starts_at')) {
                $table->dateTime('starts_at')->nullable()->after('city');
            }
            if (! Schema::hasColumn('events', 'ends_at')) {
                $table->dateTime('ends_at')->nullable()->after('starts_at');
            }
            if (! Schema::hasColumn('events', 'description')) {
                $table->text('description')->nullable()->after('ends_at');
            }
        });
    }

    public function down(): void
    {
        if (Schema::hasTable('events')) {
            Schema::table('events', function (Blueprint $table): void {
                if (Schema::hasColumn('events', 'tenant_id')) {
                    $table->dropConstrainedForeignId('tenant_id');
                }
                $cols = array_filter(['venue', 'city', 'starts_at', 'ends_at', 'description'], fn ($col) => Schema::hasColumn('events', $col));
                if (! empty($cols)) {
                    $table->dropColumn($cols);
                }
            });
        }

        Schema::dropIfExists('concerts');
    }
};