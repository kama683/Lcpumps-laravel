<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contact_submissions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->timestamp('created_at')->useCurrent();
            $table->enum('mode', ['basic', 'technical']);
            $table->string('name');
            $table->string('company');
            $table->string('email');
            $table->string('phone');
            $table->text('message');
            $table->json('tech_primary')->nullable();
            $table->json('tech_extra')->nullable();
            $table->enum('status', ['new', 'read'])->default('new');

            $table->index('status', 'contact_submissions_status_idx');
            $table->index('created_at', 'contact_submissions_created_at_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contact_submissions');
    }
};
