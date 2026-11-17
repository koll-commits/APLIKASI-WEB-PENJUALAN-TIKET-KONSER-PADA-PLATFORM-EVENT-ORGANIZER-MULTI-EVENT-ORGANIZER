<?php

namespace App\Modules\Catalog\Services;

use App\Models\Catalog;
use App\Modules\Admin\Services\AuditLogger;
use Illuminate\Support\Facades\DB;

final class CatalogStockService
{
    public function __construct(private AuditLogger $audit) {}

    public function adjust(Catalog $catalog, int $quantityDelta, string $type, string $idempotencyKey, ?string $reason = null): Catalog
    {
        return DB::transaction(function () use ($catalog, $quantityDelta, $type, $idempotencyKey, $reason): Catalog {
            $locked = Catalog::query()->withoutGlobalScope('tenant')->lockForUpdate()->findOrFail($catalog->id);

            DB::table('catalog_stock_movements')->insert([
                'tenant_id' => $locked->tenant_id,
                'catalog_id' => $locked->id,
                'idempotency_key' => $idempotencyKey,
                'type' => $type,
                'quantity_delta' => $quantityDelta,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $locked->forceFill(['stock_qty' => $locked->stock_qty + $quantityDelta])->save();

            $this->audit->record('catalog', $locked->id, 'stock_'.$type,
                null, ['delta' => $quantityDelta, 'reason' => $reason, 'stock_qty' => $locked->stock_qty], $locked->tenant_id);

            return $locked;
        });
    }

    public function toggleAvailability(Catalog $catalog): Catalog
    {
        $catalog->forceFill(['is_available' => ! $catalog->is_available])->save();

        $this->audit->record('catalog', $catalog->id, 'toggle_availability',
            null, ['is_available' => $catalog->is_available], $catalog->tenant_id);

        return $catalog;
    }
}