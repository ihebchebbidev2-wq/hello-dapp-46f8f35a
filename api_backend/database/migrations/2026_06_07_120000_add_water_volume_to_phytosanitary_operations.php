<?php

/**
 * Phytosanitary entry redesign (client v5): the technician now records the
 * spray-mix volume per plot ("volume de bouillie") and, per pesticide, a dose
 * expressed as Qté/100L. The absolute product quantity is derived as
 *   quantity_applied = water_volume_l × dose / 100
 * and stored as before, while `water_volume_l` is persisted so the report can
 * show a "volume / ha" column.
 */

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('phytosanitary_operations')
            && ! Schema::hasColumn('phytosanitary_operations', 'water_volume_l')) {
            DB::statement('ALTER TABLE phytosanitary_operations ADD COLUMN water_volume_l numeric(12,3)');
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('phytosanitary_operations')
            && Schema::hasColumn('phytosanitary_operations', 'water_volume_l')) {
            DB::statement('ALTER TABLE phytosanitary_operations DROP COLUMN water_volume_l');
        }
    }
};
