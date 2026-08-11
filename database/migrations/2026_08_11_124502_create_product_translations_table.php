<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_translations', function (Blueprint $table) {
            $table->string('product_slug');
            $table->enum('locale', ['ru', 'en', 'kk', 'zh']);
            $table->string('name');
            $table->json('description');
            $table->json('specs');
            $table->json('applications');

            $table->primary(['product_slug', 'locale']);
            $table->foreign('product_slug')->references('slug')->on('products')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_translations');
    }
};
