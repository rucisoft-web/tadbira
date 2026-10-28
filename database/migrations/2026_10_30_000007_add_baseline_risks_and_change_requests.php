<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Fitur manajemen proyek profesional: baseline jadwal dan biaya, register risiko dan isu,
 * serta log permintaan perubahan.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->date('baseline_start')->nullable();
            $table->date('baseline_finish')->nullable();
            $table->decimal('baseline_budget', 18, 2)->nullable();
            $table->unsignedSmallInteger('baseline_number')->default(0);
            $table->timestamp('baseline_at')->nullable();
            $table->foreignId('baseline_by')->nullable()->constrained('users')->nullOnDelete();
        });

        Schema::table('tasks', function (Blueprint $table) {
            $table->date('baseline_start')->nullable();
            $table->date('baseline_finish')->nullable();
            $table->unsignedSmallInteger('baseline_duration')->nullable();
        });

        Schema::create('project_risks', function (Blueprint $table) {
            $table->id();
            $table->ulid('ulid')->unique();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('task_id')->nullable()->constrained()->nullOnDelete();
            $table->string('type', 10)->default('risk');       // risk = risiko, issue = isu (sudah terjadi)
            $table->string('title', 200);
            $table->text('description')->nullable();
            $table->string('category', 40)->nullable();
            $table->unsignedTinyInteger('probability')->nullable(); // 1-5, hanya risiko
            $table->unsignedTinyInteger('impact');                  // 1-5
            $table->string('strategy', 20)->nullable();             // hindari, mitigasi, alihkan, terima, eskalasi
            $table->text('response')->nullable();
            $table->foreignId('owner_id')->nullable()->constrained('users')->nullOnDelete();
            $table->date('due_date')->nullable();
            $table->string('status', 20)->default('open');          // open, monitoring, closed
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();
            $table->index(['project_id', 'type', 'status']);
        });

        Schema::create('change_requests', function (Blueprint $table) {
            $table->id();
            $table->ulid('ulid')->unique();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('number');
            $table->string('title', 200);
            $table->text('description');
            $table->text('reason')->nullable();
            $table->smallInteger('schedule_impact_days')->default(0);
            $table->decimal('cost_impact', 18, 2)->default(0);
            $table->text('scope_impact')->nullable();
            $table->string('status', 20)->default('submitted'); // submitted, approved, rejected, withdrawn
            $table->foreignId('requested_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('decided_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('decided_at')->nullable();
            $table->text('decision_note')->nullable();
            $table->boolean('rebaselined')->default(false);
            $table->timestamps();
            $table->unique(['project_id', 'number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('change_requests');
        Schema::dropIfExists('project_risks');
        Schema::table('tasks', fn (Blueprint $table) => $table->dropColumn(['baseline_start', 'baseline_finish', 'baseline_duration']));
        Schema::table('projects', function (Blueprint $table) {
            $table->dropConstrainedForeignId('baseline_by');
            $table->dropColumn(['baseline_start', 'baseline_finish', 'baseline_budget', 'baseline_number', 'baseline_at']);
        });
    }
};
