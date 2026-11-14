<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_platform_roles', function (Blueprint $table): void {
            if (! Schema::hasColumn('user_platform_roles', 'event_id')) {
                $table->foreignId('event_id')->nullable()->after('user_id')->constrained('events')->cascadeOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('user_platform_roles', function (Blueprint $table): void {
            if (Schema::hasColumn('user_platform_roles', 'event_id')) {
                $table->dropConstrainedForeignId('event_id');
            }
        });
    }
};
