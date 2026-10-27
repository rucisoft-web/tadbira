<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('organizations', function (Blueprint $table) {
            $table->id();
            $table->ulid('ulid')->unique();
            $table->foreignId('parent_id')->nullable()->constrained('organizations')->nullOnDelete();
            $table->string('name', 150);
            $table->string('short_name', 50)->nullable();
            $table->string('sector', 30)->default('kementerian'); // lihat config/tadbira.php
            $table->string('code', 30)->nullable();               // kode satker / kode entitas
            $table->string('timezone', 64)->default('Asia/Jakarta');
            $table->json('settings')->nullable();
            $table->timestamps();
        });

        Schema::create('organization_user', function (Blueprint $table) {
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('role', 30)->default('anggota'); // admin, pengelola_portofolio, anggota
            $table->primary(['organization_id', 'user_id']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('current_organization_id')->nullable()->after('id')
                ->constrained('organizations')->nullOnDelete();
            $table->unsignedSmallInteger('capacity_hours_per_day')->default(8)->after('password');
        });

        Schema::create('fiscal_years', function (Blueprint $table) {
            $table->id();
            $table->ulid('ulid')->unique();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('year');
            $table->string('code', 20);                 // "TA 2027"
            $table->date('start_date');
            $table->date('end_date');
            $table->string('status', 20)->default('planning'); // planning, active, closed
            $table->decimal('ceiling_amount', 18, 2)->nullable(); // pagu
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->unique(['organization_id', 'year']);
        });

        Schema::create('holidays', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->nullable()->constrained()->cascadeOnDelete(); // null = nasional
            $table->date('date');
            $table->string('name', 150);
            $table->boolean('is_working_day')->default(false); // hari kerja pengganti
            $table->unique(['organization_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('holidays');
        Schema::dropIfExists('fiscal_years');
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('current_organization_id');
            $table->dropColumn('capacity_hours_per_day');
        });
        Schema::dropIfExists('organization_user');
        Schema::dropIfExists('organizations');
    }
};
