<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('section_translations', function (Blueprint $table) {
            $table->string('section_id');
            $table->enum('locale', ['ru', 'en', 'kk', 'zh']);
            $table->string('title');
            $table->json('paragraphs');
            $table->json('bullets')->nullable();

            $table->primary(['section_id', 'locale']);
            $table->foreign('section_id')->references('id')->on('sections')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('section_translations');
    }
};
