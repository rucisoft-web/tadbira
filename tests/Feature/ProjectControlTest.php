<?php

namespace Tests\Feature;

use App\Models\Organization;
use App\Models\Project;
use App\Models\User;
use App\Services\Scheduling\ProjectScheduler;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Tests\TestCase;

class ProjectControlTest extends TestCase
{
    use RefreshDatabase;

    private Organization $org;

    private Project $project;

    protected function setUp(): void
    {
        parent::setUp();
        $this->org = Organization::create(['name' => 'UIN Contoh', 'sector' => 'perguruan_tinggi']);
        $fy = $this->org->fiscalYears()->create(['year' => 2027, 'code' => 'TA 2027', 'start_date' => '2027-01-01', 'end_date' => '2027-12-31', 'status' => 'active']);
        $this->org->units()->create(['name' => 'FST']);
        $this->project = Project::create([
            'organization_id' => $this->org->id, 'fiscal_year_id' => $fy->id, 'name' => 'Lab FST', 'category' => 'FST',
            'start_date' => '2027-03-01', 'budget_amount' => 100_000_000, 'status' => 'executing',
        ]);
        $a = $this->project->tasks()->create(['title' => 'Desain', 'kind' => 'activity', 'duration_days' => 5, 'position' => 1]);
        $this->project->tasks()->create(['title' => 'Pembangunan', 'kind' => 'activity', 'duration_days' => 10, 'position' => 2]);
        $scheduler = app(ProjectScheduler::class);
        $scheduler->renumberAndSave($this->project);
        $this->project->dependencies()->create(['predecessor_id' => $a->id, 'successor_id' => $a->id + 1, 'type' => 'SS', 'lag_days' => 2]);
        $scheduler->reschedule($this->project->refresh());
    }

    private function user(string $role, ?string $unit = null): User
    {
        $user = User::factory()->create(['current_organization_id' => $this->org->id]);
        $this->org->users()->attach($user->id, ['role' => $role, 'unit_id' => $unit ? $this->org->units()->where('name', $unit)->value('id') : null]);

        return $user;
    }

    public function test_start_to_start_dependency_and_text_format(): void
    {
        $build = $this->project->tasks()->where('title', 'Pembangunan')->first();
        $this->assertSame('2027-03-03', $build->start_date->toDateString());

        $this->actingAs($this->user('perencana'))->patch(route('tasks.update', $build->ulid), ['predecessors' => '1FF+6'])->assertRedirect();
        $build->refresh();
        // Desain selesai Jumat 5 Mar; FF+6 → Pembangunan (10 hari) selesai Senin 15 Mar, mulai Selasa 2 Mar.
        $this->assertSame('2027-03-15', $build->finish_date->toDateString());
        $this->assertSame('2027-03-02', $build->start_date->toDateString());
        $this->assertSame('FF', $this->project->dependencies()->value('type'));

        $this->actingAs($this->user('perencana'))->get(route('projects.show', $this->project->ulid))
            ->assertInertia(fn ($page) => $page->where('tasks.1.predecessors', '1FF+6'));
    }

    public function test_baseline_then_rebaseline_needs_approver(): void
    {
        $operator = $this->user('operator', 'FST');
        $this->actingAs($operator)->post(route('baseline.store', $this->project->ulid))->assertRedirect();

        $this->project->refresh();
        $this->assertSame(1, $this->project->baseline_number);
        $this->assertSame('2027-03-03', $this->project->tasks()->where('title', 'Pembangunan')->first()->baseline_start->toDateString());
        $this->assertEquals(100_000_000, $this->project->baseline_budget);

        // Menetapkan ulang hanya oleh pimpinan/perencana/admin, dan wajib beralasan.
        $this->actingAs($operator)->post(route('baseline.store', $this->project->ulid), ['reason' => 'revisi'])->assertForbidden();
        $planner = $this->user('perencana');
        $this->actingAs($planner)->post(route('baseline.store', $this->project->ulid))->assertSessionHasErrors('reason');
        $this->actingAs($planner)->post(route('baseline.store', $this->project->ulid), ['reason' => 'Revisi DIPA'])->assertRedirect();
        $this->assertSame(2, $this->project->refresh()->baseline_number);

        $this->actingAs($planner)->get(route('projects.show', $this->project->ulid))
            ->assertInertia(fn ($page) => $page->where('project.baseline.number', 2)->where('summary.has_baseline', true)->has('summary.evm.cpi'));
    }

    public function test_risk_register(): void
    {
        $operator = $this->user('operator', 'FST');
        $this->actingAs($operator)->post(route('risks.store', $this->project->ulid), [
            'type' => 'risk', 'title' => 'Tender gagal', 'category' => 'Pengadaan', 'probability' => 4, 'impact' => 5,
            'strategy' => 'mitigasi', 'response' => 'Konsultasi pasar', 'owner_id' => $operator->id,
        ])->assertRedirect();
        $this->actingAs($operator)->post(route('risks.store', $this->project->ulid), ['type' => 'issue', 'title' => 'Penyedia mundur', 'impact' => 3])->assertRedirect();

        $risk = $this->project->risks()->where('type', 'risk')->first();
        $this->assertSame(20, $risk->score());
        $this->assertSame(15, $this->project->risks()->where('type', 'issue')->first()->score());

        $this->actingAs($operator)->patch(route('risks.update', $risk->ulid), ['status' => 'closed'])->assertRedirect();
        $this->assertNotNull($risk->refresh()->closed_at);

        $this->actingAs($this->user('keuangan'))->post(route('risks.store', $this->project->ulid), ['type' => 'risk', 'title' => 'x', 'impact' => 1])->assertForbidden();
        $this->actingAs($operator)->get(route('projects.show', $this->project->ulid))
            ->assertInertia(fn ($page) => $page->has('risks', 2)->where('risks.0.level', 'tinggi'));
    }

    public function test_change_request_approval_rebaselines(): void
    {
        $operator = $this->user('operator', 'FST');
        $this->actingAs($operator)->post(route('baseline.store', $this->project->ulid));
        $this->actingAs($operator)->post(route('changes.store', $this->project->ulid), [
            'title' => 'Perpanjangan waktu', 'description' => 'Mundur 5 hari', 'schedule_impact_days' => 5, 'cost_impact' => 2_000_000,
        ])->assertRedirect();
        $this->actingAs($operator)->post(route('changes.store', $this->project->ulid), ['title' => 'Kedua', 'description' => 'x']);
        $cr = $this->project->changeRequests()->where('number', 1)->first();
        $this->assertSame('PP-001', $cr->code());
        $this->assertSame(2, $this->project->changeRequests()->max('number'));

        $this->actingAs($operator)->post(route('changes.decide', $cr->ulid), ['decision' => 'approved'])->assertForbidden();
        $boss = $this->user('pimpinan');
        $this->actingAs($boss)->post(route('changes.decide', $cr->ulid), ['decision' => 'rejected'])->assertSessionHasErrors('decision_note');
        $this->actingAs($boss)->post(route('changes.decide', $cr->ulid), ['decision' => 'approved', 'rebaseline' => true])->assertRedirect();

        $cr->refresh();
        $this->assertSame('approved', $cr->status);
        $this->assertTrue($cr->rebaselined);
        $this->assertSame($boss->id, $cr->decided_by);
        $this->assertSame(2, $this->project->refresh()->baseline_number);
        $this->actingAs($boss)->post(route('changes.decide', $cr->ulid), ['decision' => 'rejected', 'decision_note' => 'x'])->assertStatus(422);

        $second = $this->project->changeRequests()->where('number', 2)->first();
        $this->actingAs($operator)->post(route('changes.withdraw', $second->ulid))->assertRedirect();
        $this->assertSame('withdrawn', $second->refresh()->status);

        $this->actingAs($boss)->get(route('dashboard'))->assertInertia(fn ($page) => $page->has('control.pending_changes', 0));
    }

    public function test_reports_and_exports(): void
    {
        $user = $this->user('pimpinan');
        $this->project->risks()->create(['type' => 'risk', 'title' => 'Hujan', 'probability' => 3, 'impact' => 3]);

        $this->actingAs($user)->get(route('projects.report', $this->project->ulid))->assertOk()->assertSee('Laporan status')->assertSee('Hujan');

        $res = $this->actingAs($user)->get(route('projects.export', $this->project->ulid));
        $res->assertOk()->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $path = tempnam(sys_get_temp_dir(), 'xl');
        file_put_contents($path, $res->streamedContent());
        $book = IOFactory::load($path);
        $this->assertSame(['Ringkasan', 'SRK & jadwal', 'Anggaran', 'Risiko & isu', 'Perubahan'], $book->getSheetNames());
        $this->assertSame('Hujan', $book->getSheetByName('Risiko & isu')->getCell('B2')->getValue());
        unlink($path);

        $this->actingAs($user)->get(route('portfolio.export'))->assertOk();
        $other = Organization::create(['name' => 'Lain']);
        $stranger = User::factory()->create(['current_organization_id' => $other->id]);
        $other->users()->attach($stranger->id, ['role' => 'admin']);
        $this->actingAs($stranger)->get(route('projects.report', $this->project->ulid))->assertNotFound();
    }

    public function test_workload_counts_overlapping_assignments(): void
    {
        $person = $this->user('operator', 'FST');
        foreach ($this->project->tasks as $t) {
            $t->assignees()->attach($person->id, ['role' => 'responsible']);
        }

        // Minggu 1 Mar 2027: Desain (Sen–Jum) + Pembangunan (Rab–Jum) = 100% + 60%.
        $this->actingAs($this->user('perencana'))->get(route('workload', ['mulai' => '2027-03-01']))->assertOk()
            ->assertInertia(fn ($page) => $page->component('Workload')
                ->where('people.0.name', $person->name)
                ->where('people.0.weeks.0.percent', 160)
                ->where('people.0.weeks.1.percent', 100)
                ->where('people.0.overloaded_weeks', 1));
    }
}
