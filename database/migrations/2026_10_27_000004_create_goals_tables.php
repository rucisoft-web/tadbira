<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Sasaran program (Perjanjian Kinerja) per tahun anggaran beserta indikator kinerjanya (IKU).
        Schema::create('goals', function (Blueprint $table) {
            $table->id();
            $table->ulid()->unique();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('fiscal_year_id')->constrained()->cascadeOnDelete();
            $table->string('code', 20)->nullable();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('color', 9)->nullable();
            $table->unsignedSmallInteger('position')->default(0);
            $table->timestamps();
            $table->index(['fiscal_year_id', 'position']);
        });

        Schema::create('goal_indicators', function (Blueprint $table) {
            $table->id();
            $table->foreignId('goal_id')->constrained()->cascadeOnDelete();
            $table->string('name', 500);
            $table->string('unit', 30)->nullable();           // %, nilai, predikat
            $table->string('target', 50);                      // teks agar bisa "WTP" atau "70-80"
            $table->string('realization', 50)->nullable();
            $table->decimal('score', 6, 2)->nullable();        // capaian kinerja (%)
            $table->string('baseline_realization', 50)->nullable(); // realisasi tahun sebelumnya
            $table->decimal('baseline_score', 6, 2)->nullable();
            $table->unsignedSmallInteger('position')->default(0);
            $table->timestamps();
        });

        Schema::table('projects', function (Blueprint $table) {
            $table->foreignId('goal_id')->nullable()->after('fiscal_year_id')->constrained()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropConstrainedForeignId('goal_id');
        });
        Schema::dropIfExists('goal_indicators');
        Schema::dropIfExists('goals');
    }
};
