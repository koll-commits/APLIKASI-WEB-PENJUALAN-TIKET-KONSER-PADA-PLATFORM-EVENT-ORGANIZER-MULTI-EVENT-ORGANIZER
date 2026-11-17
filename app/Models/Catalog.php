<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Database\Factories\CatalogFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Catalog extends Model
{
    use BelongsToTenant;

    /** @use HasFactory<CatalogFactory> */
    use HasFactory;

    // tenant_id & category_id di-set eksplisit oleh sistem domain
    protected $fillable = ['name', 'base_price', 'stock_qty', 'is_available', 'prep_minutes'];

    protected function casts(): array
    {
        return [
            'base_price' => 'integer',
            'stock_qty' => 'integer',
            'is_available' => 'boolean',
            'prep_minutes' => 'integer',
        ];
    }

    /** @return BelongsTo<Tenant, $this> */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /** @return BelongsTo<CatalogCategory, $this> */
    public function category(): BelongsTo
    {
        return $this->belongsTo(CatalogCategory::class, 'category_id');
    }
    public function modifierGroups(): BelongsToMany
    {
    return $this->belongsToMany(
        ModifierGroup::class,
        'catalog_modifier_groups',
        'catalog_id',
        'modifier_group_id'
    )->withPivot(['tenant_id', 'sort_order'])
     ->orderBy('catalog_modifier_groups.sort_order');
    }
    public function scopeSellable(Builder $query): void
    {
    $query->where('is_available', true)->where('stock_qty', '>', 0);
    }
}
