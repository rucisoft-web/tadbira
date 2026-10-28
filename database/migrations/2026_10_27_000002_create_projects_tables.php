<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->ulid('ulid')->unique();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('fiscal_year_id')->nullable()->constrained()->nullOnDelete(); // null = templat
            $table->foreignId('leader_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('code', 60)->nullable();   // kode kegiatan / RO / nomor proyek
            $table->string('name', 200);
            $table->text('description')->nullable();
            $table->string('category', 100)->nullable();
            $table->string('color', 7)->nullable();
            $table->string('status', 20)->default('planning'); // planning, executing, on_hold, closing, closed, cancelled
            // penjadwalan
            $table->string('scheduling_direction', 10)->default('forward'); // forward | backward
            $table->date('start_date')->nullable();          // jangkar bila maju
            $table->date('target_finish_date')->nullable();  // jangkar bila mundur
            $table->date('computed_start_date')->nullable(); // hasil mesin penjadwalan
            $table->date('computed_finish_date')->nullable();
            $table->decimal('percent_complete', 5, 2)->default(0);
            $table->unsignedInteger('schedule_version')->default(0);
            // templat
            $table->boolean('is_template')->default(false);
            $table->foreignId('template_id')->nullable()->constrained('projects')->nullOnDelete();
            // anggaran ringkas (rincian di budget_lines)
            $table->decimal('budget_amount', 18, 2)->default(0);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['organization_id', 'fiscal_year_id', 'status']);
            $table->index(['organization_id', 'is_template']);
        });

        Schema::create('project_members', function (Blueprint $table) {
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('role', 20)->default('executor'); // leader, planner, executor, viewer
            $table->primary(['project_id', 'user_id']);
        });

        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->ulid('ulid')->unique();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('tasks')->cascadeOnDelete();
            $table->string('kind', 20)->default('activity'); // phase, work_package, activity, milestone
            $table->string('wbs_code', 50)->nullable();
            $table->unsignedInteger('position')->default(0);
            $table->string('title');
            $table->text('expected_output')->nullable();
            $table->text('notes')->nullable();
            $table->string('status', 20)->default('not_started'); // not_started, in_progress, completed
            $table->string('priority', 20)->default('medium');
            // masukan penjadwalan
            $table->string('scheduling_mode', 10)->default('auto'); // auto | manual
            $table->unsignedSmallInteger('duration_days')->default(1); // lama pengerjaan (hari kerja)
            $table->string('constraint_type', 10)->default('asap');    // asap, snet, mso
            $table->date('constraint_date')->nullable();
            $table->date('manual_start')->nullable();
            // hasil mesin penjadwalan
            $table->date('start_date')->nullable();
            $table->date('finish_date')->nullable();
            $table->date('late_start')->nullable();
            $table->date('late_finish')->nullable();
            $table->smallInteger('total_slack_days')->nullable();
            $table->boolean('is_critical')->default(false);
            // kemajuan
            $table->unsignedTinyInteger('percent_complete')->default(0);
            $table->date('actual_start')->nullable();
            $table->date('actual_finish')->nullable();
            $table->unsignedInteger('version')->default(1);
            $table->timestamps();
            $table->softDeletes();
            $table->index(['project_id', 'parent_id', 'position']);
            $table->index(['project_id', 'start_date', 'finish_date']);
        });

        Schema::create('task_dependencies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('predecessor_id')->constrained('tasks')->cascadeOnDelete();
            $table->foreignId('successor_id')->constrained('tasks')->cascadeOnDelete();
            $table->string('type', 2)->default('FS'); // FS = Selesai-ke-Mulai
            $table->smallInteger('lag_days')->default(0); // jeda (+) / percepatan (−)
            $table->unique(['predecessor_id', 'successor_id']);
        });

        Schema::create('task_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('role', 20)->default('member'); // responsible = penanggung jawab, member = pelaksana
            $table->unsignedSmallInteger('units_percent')->default(100);
            $table->unique(['task_id', 'user_id']);
        });

        Schema::create('checklist_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->boolean('is_checked')->default(false);
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();
        });

        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('project_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('subject_type', 50);
            $table->unsignedBigInteger('subject_id');
            $table->foreignId('causer_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('event', 50);
            $table->string('description')->nullable();
            $table->json('changes')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->index(['project_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
        Schema::dropIfExists('checklist_items');
        Schema::dropIfExists('task_assignments');
        Schema::dropIfExists('task_dependencies');
        Schema::dropIfExists('tasks');
        Schema::dropIfExists('project_members');
        Schema::dropIfExists('projects');
    }
};
