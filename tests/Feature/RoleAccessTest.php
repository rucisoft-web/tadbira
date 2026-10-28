<?php

namespace Tests\Feature;

use App\Models\Organization;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleAccessTest extends TestCase
{
    use RefreshDatabase;

    private Organization $org;

    private Project $fst;

    private Project $fdk;

    protected function setUp(): void
    {
        parent::setUp();
        $this->org = Organization::create(['name' => 'UIN Contoh', 'sector' => 'perguruan_tinggi']);
        $fy = $this->org->fiscalYears()->create(['year' => 2027, 'code' => 'TA 2027', 'start_date' => '2027-01-01', 'end_date' => '2027-12-31', 'status' => 'active']);
        $this->org->units()->create(['name' => 'FST']);
        $this->org->units()->create(['name' => 'FDK']);
        $this->fst = Project::create(['organization_id' => $this->org->id, 'fiscal_year_id' => $fy->id, 'name' => 'Lab FST', 'category' => 'FST', 'start_date' => '2027-02-01']);
        $this->fdk = Project::create(['organization_id' => $this->org->id, 'fiscal_year_id' => $fy->id, 'name' => 'Seminar FDK', 'category' => 'FDK', 'start_date' => '2027-02-01']);
    }

    private function user(string $role, ?string $unit = null): User
    {
        $user = User::factory()->create(['current_organization_id' => $this->org->id]);
        $this->org->users()->attach($user->id, ['role' => $role, 'unit_id' => $unit ? $this->org->units()->where('name', $unit)->value('id') : null]);

        return $user;
    }

    public function test_operator_edits_only_projects_of_own_unit(): void
    {
        $op = $this->user('operator', 'FST');

        $this->actingAs($op)->post(route('tasks.store', $this->fst->ulid), ['title' => 'Survei', 'kind' => 'activity', 'duration_days' => 3])->assertRedirect();
        $this->assertSame(1, $this->fst->tasks()->count());
        $this->actingAs($op)->post(route('tasks.store', $this->fdk->ulid), ['title' => 'Survei', 'kind' => 'activity', 'duration_days' => 3])->assertForbidden();
        $this->actingAs($op)->delete(route('projects.destroy', $this->fst->ulid))->assertForbidden();

        $this->actingAs($op)->get(route('projects.show', $this->fdk->ulid))
            ->assertInertia(fn ($page) => $page->where('permissions.edit', false)->where('permissions.documents', false));
        $this->actingAs($op)->get(route('projects.show', $this->fst->ulid))
            ->assertInertia(fn ($page) => $page->where('permissions.edit', true)->where('permissions.delete', false));
    }

    public function test_operator_creates_projects_only_in_own_unit(): void
    {
        $op = $this->user('operator', 'FST');
        $this->actingAs($op)->post(route('projects.store'), [
            'name' => 'Kuliah Umum', 'category' => 'FDK', 'fiscal_year' => $this->org->fiscalYears()->value('ulid'),
            'scheduling_direction' => 'forward', 'anchor_date' => '2027-03-01',
        ])->assertRedirect();

        $this->assertSame('FST', Project::where('name', 'Kuliah Umum')->value('category'));
    }

    public function test_leadership_is_read_only(): void
    {
        $boss = $this->user('pimpinan');
        $this->actingAs($boss)->get(route('projects.show', $this->fst->ulid))->assertOk()
            ->assertInertia(fn ($page) => $page->where('permissions.edit', false)->where('auth.access.role_label', 'Pimpinan'));
        $this->actingAs($boss)->patch(route('projects.update', $this->fst->ulid), ['status' => 'executing'])->assertForbidden();
        $this->actingAs($boss)->get(route('projects.create'))->assertForbidden();
        $this->actingAs($boss)->get(route('budget.import'))->assertForbidden();
        $this->actingAs($boss)->post(route('fiscal-years.store'), ['year' => 2028])->assertForbidden();
    }

    public function test_finance_imports_budget_but_does_not_edit_schedule(): void
    {
        $finance = $this->user('keuangan');
        $this->actingAs($finance)->get(route('budget.import'))->assertOk();
        $this->actingAs($finance)->patch(route('projects.update', $this->fst->ulid), ['status' => 'executing'])->assertForbidden();
    }

    public function test_planner_manages_every_project(): void
    {
        $planner = $this->user('perencana');
        $this->actingAs($planner)->patch(route('projects.update', $this->fdk->ulid), ['status' => 'executing'])->assertRedirect();
        $this->assertSame('executing', $this->fdk->fresh()->status);
        $this->actingAs($planner)->get(route('settings.members'))->assertForbidden();
    }
}
