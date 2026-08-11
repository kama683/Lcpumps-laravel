<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The source (Postgres/Drizzle) has no explicit ORDER BY on the public
 * product read path — catalog listings come out in whatever order an
 * unordered heap scan happens to return, which in practice tracks
 * insertion order and therefore catalog.json's per-section product array
 * order. MySQL's default (InnoDB clustered-index) scan order is by
 * primary key (slug, alphabetical) instead, which visibly reordered every
 * catalog grid. This column makes the intended order (catalog.json's)
 * explicit and portable instead of leaning on either engine's incidental
 * default.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->unsignedInteger('sort_order')->default(0)->after('category');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('sort_order');
        });
    }
};
