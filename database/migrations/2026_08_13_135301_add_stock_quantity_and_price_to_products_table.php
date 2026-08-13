<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * `stock_quantity`/`price` were added directly to
 * 2026_08_11_124456_create_products_table.php's up() after that migration
 * had already run on some databases (this one included) — Laravel only
 * checks the migrations table for "did this filename run", so editing an
 * already-applied migration silently does nothing on any DB that ran it
 * before the edit. Guarded with hasColumn() so this is a no-op on a fresh
 * database (where create_products_table already creates both columns) and
 * only backfills the drifted ones.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (! Schema::hasColumn('products', 'stock_quantity')) {
                $table->unsignedInteger('stock_quantity')->default(0)->after('image');
            }
            if (! Schema::hasColumn('products', 'price')) {
                $table->unsignedInteger('price')->default(0)->after('stock_quantity');
            }
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (Schema::hasColumn('products', 'price')) {
                $table->dropColumn('price');
            }
            if (Schema::hasColumn('products', 'stock_quantity')) {
                $table->dropColumn('stock_quantity');
            }
        });
    }
};
