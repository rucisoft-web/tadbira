<?php

namespace Tests\Feature;

use App\Models\FiscalYear;
use App\Models\Organization;
use App\Models\Project;
use App\Models\User;
use App\Services\Budget\BudgetTemplate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class TadbiraFlowTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Organization $org;

    private FiscalYear $fy;

    protected function setUp(): void
    {
        parent::setUp();

        $this->org = Organization::create(['name' => 'Biro Perencanaan', 'sector' => 'kementerian']);
        $this->user = User::factory()->create(['current_organization_id' => $this->org->id]);
        $this->org->users()->attach($this->user->id, ['role' => 'admin']);
        $this->fy = $this->org->fiscalYears()->create([
            'year' => 2027, 'code' => 'TA 2027', 'start_date' => '2027-01-01', 'end_date' => '2027-12-31', 'status' => 'active',
        ]);
    }

    private function newProject(string $name = 'Seminar Nasional'): Project
    {
        $this->actingAs($this->user)->post(route('projects.store'), [
            'name' => $name,
            'fiscal_year' => $this->fy->ulid,
            'scheduling_direction' => 'forward',
            'anchor_date' => '2027-03-01',
        ])->assertRedirect();

        return Project::where('name', $name)->firstOrFail();
    }

    public function test_dashboard_renders_portfolio(): void
    {
        $this->newProject();

        $this->actingAs($this->user)->get(route('dashboard', ['ta' => $this->fy->ulid]))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('Dashboard')->where('terms.budget_ceiling', 'Pagu DIPA')->etc());
    }

    public function test_adding_tasks_with_predecessors_schedules_them(): void
    {
        $project = $this->newProject();

        $this->actingAs($this->user)->post(route('tasks.store', $project), ['title' => 'Persiapan', 'duration_days' => 5]);
        $this->actingAs($this->user)->post(route('tasks.store', $project), ['title' => 'Pelaksanaan', 'duration_days' => 3, 'predecessors' => '1']);

        $tasks = $project->tasks()->orderBy('position')->get();
        // 1 Mar 2027 Senin; 5 hari kerja selesai Jumat 5 Mar, penerus mulai Senin 8 Mar.
        $this->assertSame('2027-03-05', $tasks[0]->finish_date->toDateString());
        $this->assertSame('2027-03-08', $tasks[1]->start_date->toDateString());
        $this->assertSame('2027-03-10', $project->fresh()->computed_finish_date->toDateString());

        // Mengubah durasi menjadwal ulang penerusnya.
        $this->actingAs($this->user)->patch(route('tasks.update', $tasks[0]), ['duration_days' => 10]);
        $this->assertSame('2027-03-15', $tasks[1]->fresh()->start_date->toDateString());
    }

    public function test_project_from_template_only_needs_a_start_date(): void
    {
        $project = $this->newProject('Calon templat');
        $this->actingAs($this->user)->post(route('tasks.store', $project), ['title' => 'Tahap A', 'duration_days' => 10]);
        $this->actingAs($this->user)->post(route('templates.store', $project), ['name' => 'Templat A']);
        $template = Project::templates()->where('name', 'Templat A')->firstOrFail();

        $preview = $this->actingAs($this->user)->postJson(route('templates.preview', $template), [
            'direction' => 'forward', 'date' => '2027-05-03', 'fiscal_year' => $this->fy->ulid,
        ])->assertOk()->json();
        $this->assertSame('2027-05-14', $preview['finish_date']);

        $this->actingAs($this->user)->post(route('projects.store'), [
            'name' => 'Dari templat', 'fiscal_year' => $this->fy->ulid, 'template' => $template->ulid,
            'scheduling_direction' => 'forward', 'anchor_date' => '2027-05-03',
        ])->assertRedirect();
        $this->assertSame('2027-05-14', Project::where('name', 'Dari templat')->first()->computed_finish_date->toDateString());
    }

    public function test_pok_import_creates_one_project_per_ro(): void
    {
        $path = tempnam(sys_get_temp_dir(), 'pok').'.xlsx';
        BudgetTemplate::write(BudgetTemplate::pokSample(2027), $path, 'POK');

        $this->actingAs($this->user)->post(route('budget.import.store'), [
            'file' => new UploadedFile($path, 'pok.xlsx', null, null, true),
            'mode' => 'projects',
            'fiscal_year' => $this->fy->ulid,
            'build_wbs' => true,
        ])->assertRedirect()->assertSessionHasNoErrors();

        $projects = Project::real()->where('fiscal_year_id', $this->fy->id)->get();
        $this->assertGreaterThanOrEqual(2, $projects->count());
        $this->assertTrue($projects->every(fn ($p) => $p->budget_amount > 0 && $p->tasks()->exists()));
    }

    public function test_other_organizations_cannot_open_a_project(): void
    {
        $project = $this->newProject();
        $other = Organization::create(['name' => 'PT Lain', 'sector' => 'swasta']);
        $stranger = User::factory()->create(['current_organization_id' => $other->id]);
        $other->users()->attach($stranger->id, ['role' => 'admin']);

        $this->actingAs($stranger)->get(route('projects.show', $project))->assertNotFound();
    }
}
