<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * Unit kerja, peran rinci (admin, pimpinan, perencana, keuangan, operator), dan status anggota.
 *
 * Data lama ikut dirapikan: setiap kategori kegiatan menjadi unit kerja, dan peran "anggota" diganti
 * menjadi operator unit (bila semua kegiatan yang dipimpinnya ada di satu unit) atau perencana.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('units', function (Blueprint $table) {
            $table->id();
            $table->ulid('ulid')->unique();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->string('name', 100);                 // sama dengan kategori kegiatan, mis. "FADIB"
            $table->string('full_name', 200)->nullable(); // mis. "Fakultas Adab dan Ilmu Budaya"
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();
            $table->unique(['organization_id', 'name']);
        });

        Schema::table('organization_user', function (Blueprint $table) {
            $table->foreignId('unit_id')->nullable()->constrained('units')->nullOnDelete();
            $table->boolean('is_active')->default(true);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('last_login_at')->nullable();
        });

        $this->migrateData();
    }

    private function migrateData(): void
    {
        $now = now();
        $categories = DB::table('projects')
            ->where('is_template', false)->whereNotNull('category')->where('category', '!=', '')
            ->select('organization_id', 'category')->distinct()->orderBy('organization_id')->orderBy('category')->get();
        foreach ($categories->groupBy('organization_id') as $orgId => $rows) {
            foreach ($rows->values() as $i => $row) {
                DB::table('units')->insert([
                    'ulid' => (string) Str::ulid(), 'organization_id' => $orgId, 'name' => Str::limit($row->category, 100, ''),
                    'position' => $i, 'created_at' => $now, 'updated_at' => $now,
                ]);
            }
        }

        $members = DB::table('organization_user')->whereIn('role', ['anggota', 'pengelola_portofolio'])->get();
        foreach ($members as $m) {
            $led = DB::table('projects')->where('organization_id', $m->organization_id)->where('leader_id', $m->user_id)
                ->where('is_template', false)->whereNull('deleted_at')->distinct()->pluck('category')->filter();
            $unitId = $led->count() === 1
                ? DB::table('units')->where('organization_id', $m->organization_id)->where('name', $led->first())->value('id')
                : null;

            DB::table('organization_user')->where('organization_id', $m->organization_id)->where('user_id', $m->user_id)
                ->update($unitId ? ['role' => 'operator', 'unit_id' => $unitId] : ['role' => 'perencana']);
        }
    }

    public function down(): void
    {
        DB::table('organization_user')->whereIn('role', ['pimpinan', 'perencana', 'keuangan', 'operator'])->update(['role' => 'anggota']);
        Schema::table('users', fn (Blueprint $table) => $table->dropColumn('last_login_at'));
        Schema::table('organization_user', function (Blueprint $table) {
            $table->dropConstrainedForeignId('unit_id');
            $table->dropColumn('is_active');
        });
        Schema::dropIfExists('units');
    }
};
