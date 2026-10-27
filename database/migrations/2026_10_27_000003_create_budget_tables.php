<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Rincian anggaran berbentuk pohon. Untuk pemerintah pusat mengikuti POK RKA-K/L
        // (Program → Kegiatan → KRO → RO → Komponen → Subkomponen → Akun → Detail);
        // untuk sektor lain mengikuti RAB/RKAP (Kelompok → Pos → Detail).
        Schema::create('budget_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('fiscal_year_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('budget_lines')->cascadeOnDelete();
            $table->foreignId('task_id')->nullable()->constrained()->nullOnDelete(); // aktivitas terkait di SRK
            $table->string('level', 20);          // program, kegiatan, kro, ro, komponen, subkomponen, akun, detail, kelompok, pos
            $table->string('code', 60)->nullable();
            $table->string('description', 500);
            $table->decimal('volume', 14, 2)->nullable();
            $table->string('unit', 50)->nullable();
            $table->decimal('unit_price', 18, 2)->nullable();
            $table->decimal('amount', 18, 2)->default(0);
            $table->string('funding_source', 20)->nullable(); // RM, PNBP, BLU, PHLN, SBSN, Kas, ...
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();
            $table->index(['project_id', 'parent_id', 'position']);
        });

        Schema::create('expenditures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('budget_line_id')->constrained()->cascadeOnDelete();
            $table->string('status', 20)->default('paid'); // committed, invoiced, paid
            $table->decimal('amount', 18, 2);
            $table->date('transaction_date');
            $table->string('reference', 100)->nullable(); // no. SPM/SP2D/kuitansi
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['budget_line_id', 'transaction_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expenditures');
        Schema::dropIfExists('budget_lines');
    }
};
