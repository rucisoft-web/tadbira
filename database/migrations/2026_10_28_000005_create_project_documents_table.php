<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Berkas digital kegiatan (TOR, kontrak, BAST, laporan, foto, dsb.).
        // Berkasnya disimpan di storage/app/private, di luar folder web, dan hanya bisa diunduh lewat aplikasi.
        Schema::create('project_documents', function (Blueprint $table) {
            $table->id();
            $table->ulid('ulid')->unique();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('task_id')->nullable()->constrained()->nullOnDelete(); // aktivitas/tonggak SRK terkait
            $table->string('category', 40)->nullable();
            $table->string('title', 200);
            $table->string('original_name', 255);
            $table->string('path', 255);
            $table->string('extension', 10);
            $table->string('mime', 120)->nullable();
            $table->unsignedBigInteger('size');
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['project_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_documents');
    }
};
