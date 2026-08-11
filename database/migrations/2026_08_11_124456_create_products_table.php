<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->string('slug')->primary();
            $table->string('code');
            $table->enum('category', ['pumps', 'valves', 'control', 'water']);
            $table->string('section_id')->nullable();
            $table->string('image')->nullable();
            $table->unsignedInteger('stock_quantity')->default(0);
            // Admin-only field: never expose on public product reads/views.
            $table->unsignedInteger('price')->default(0);
            $table->timestamps();

            $table->foreign('section_id')->references('id')->on('sections')->nullOnDelete();
            $table->index('section_id', 'products_section_id_idx');
            $table->index('category', 'products_category_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
