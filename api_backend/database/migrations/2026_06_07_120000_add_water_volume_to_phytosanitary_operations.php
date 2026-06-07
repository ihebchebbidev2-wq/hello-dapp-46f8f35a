<?php

/**
 * Phytosanitary entry redesign (client v5): the technician now records the
 * spray-mix volume per plot ("volume de bouillie") and, per pesticide, a dose
 * expressed as Qté/100L. The absolute product quantity is derived as
 *   quantity_applied = water_volume_l × dose / 100
 * and stored as before, while `water_volume_l` is persisted so the report can
 * show a "volume / ha" column.
 *
 * NOTE: this migration deliberately avoids Schema::hasColumn()/hasTable()
 * introspection and runs OUTSIDE a transaction. On Neon's pooled Postgres a
 * session can arrive with an already-aborted transaction, which makes the
 * metadata SELECTs behind hasColumn() blow up with SQLSTATE 25P02. A single
 * idempotent `ADD COLUMN IF NOT EXISTS` statement sidesteps that entirely.
 */

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /** Run without the implicit migration transaction (Neon pooler friendly). */
    public $withinTransaction = false;

    public function up(): void
    {
        // Clear any aborted-transaction state left on a pooled connection so
        // the DDL below isn't rejected with 25P02.
        try { DB::statement('ROLLBACK'); } catch (\Throwable) {}

        DB::statement('ALTER TABLE phytosanitary_operations ADD COLUMN IF NOT EXISTS water_volume_l numeric(12,3)');
    }

    public function down(): void
    {
        try { DB::statement('ROLLBACK'); } catch (\Throwable) {}

        DB::statement('ALTER TABLE phytosanitary_operations DROP COLUMN IF EXISTS water_volume_l');
    }
};
