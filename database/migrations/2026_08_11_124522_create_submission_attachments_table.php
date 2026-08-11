<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('submission_attachments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('submission_id');
            $table->string('file_name');
            $table->string('mime_type');
            $table->unsignedInteger('size_bytes');
            $table->string('storage_path');
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('submission_id')->references('id')->on('contact_submissions')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('submission_attachments');
    }
};
