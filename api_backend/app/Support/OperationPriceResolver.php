<?php

declare(strict_types=1);

namespace App\Support;

use Illuminate\Support\Facades\DB;

/**
 * Resolves the effective price for an entity on a given date by querying
 * price_history. Returns the most recent row whose [effective_from, effective_to]
 * window contains the requested date.
 */
final class OperationPriceResolver
{
    /**
     * Look up the price for an entity on $date.
     *
     * @param  string       $entityType  water|fertilizer|pesticide|labor
     * @param  string|null  $entityId    UUID of the config row (null for water/labor which are global)
     * @param  string|null  $date        Y-m-d; defaults to today
     */
    public function priceFor(string $entityType, ?string $entityId = null, ?string $date = null): string
    {
        $date ??= now()->toDateString();

        // v6: Always return the LATEST price_history entry for the entity.
        // Cost reports must reflect the current authoritative price for every
        // op (no per-row drift). $date is kept in the signature for callers
        // that still pass it, but is intentionally not used in the lookup.
        $row = DB::table('price_history')
            ->where('entity_type', $entityType)
            ->when($entityId !== null, fn ($q) => $q->where('entity_id', $entityId))
            ->orderByDesc('effective_from')
            ->orderByDesc('id')
            ->first(['price_per_unit']);

        return $row ? (string) $row->price_per_unit : '0.0000';
    }

    /**
     * Returns the unit of the currently active water config row.
     */
    public function activeWaterUnit(): string
    {
        return DB::table('water_config')
            ->where('is_active', true)
            ->orderByDesc('created_at')
            ->value('unit') ?? 'm3';
    }
}
