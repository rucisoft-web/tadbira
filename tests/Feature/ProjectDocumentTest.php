<?php

namespace Tests\Feature;

use App\Models\Organization;
use App\Models\Project;
use App\Models\ProjectDocument;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProjectDocumentTest extends TestCase
{
    use RefreshDatabase;

    private Organization $org;

    private User $admin;

    private User $member;

    private Project $project;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');

        $this->org = Organization::create(['name' => 'Biro Perencanaan', 'sector' => 'kementerian']);
        $this->admin = User::factory()->create(['current_organization_id' => $this->org->id]);
        $this->member = User::factory()->create(['current_organization_id' => $this->org->id]);
        $this->org->users()->attach($this->admin->id, ['role' => 'admin']);
        $this->org->users()->attach($this->member->id, ['role' => 'keuangan']);
        $fy = $this->org->fiscalYears()->create([
            'year' => 2027, 'code' => 'TA 2027', 'start_date' => '2027-01-01', 'end_date' => '2027-12-31', 'status' => 'active',
        ]);

        $this->actingAs($this->admin)->post(route('projects.store'), [
            'name' => 'Renovasi Aula', 'fiscal_year' => $fy->ulid, 'scheduling_direction' => 'forward', 'anchor_date' => '2027-03-01',
        ]);
        $this->project = Project::where('name', 'Renovasi Aula')->firstOrFail();
        $this->actingAs($this->admin)->post(route('tasks.store', $this->project->ulid), ['title' => 'BAST', 'kind' => 'milestone', 'duration_days' => 0]);
    }

    public function test_member_uploads_several_files_of_different_types(): void
    {
        $task = $this->project->tasks()->firstOrFail();

        $this->actingAs($this->member)->post(route('documents.store', $this->project->ulid), [
            'files' => [
                UploadedFile::fake()->create('TOR Renovasi.pdf', 300, 'application/pdf'),
                UploadedFile::fake()->create('RAB.xlsx', 50, 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'),
                UploadedFile::fake()->create('Kontrak.docx', 80, 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'),
            ],
            'category' => 'Kontrak / SPK',
            'task' => $task->ulid,
        ])->assertRedirect()->assertSessionHasNoErrors();

        $docs = $this->project->documents()->get();
        $this->assertCount(3, $docs);
        $this->assertEqualsCanonicalizing(['pdf', 'xlsx', 'docx'], $docs->pluck('extension')->all());
        $this->assertSame($task->id, $docs->first()->task_id);
        $docs->each(fn ($d) => Storage::disk('local')->assertExists($d->path));

        $this->get(route('projects.show', $this->project->ulid))
            ->assertInertia(fn ($page) => $page->has('documents', 3)->where('documents.0.can_manage', true));
    }

    public function test_rejects_disallowed_types(): void
    {
        $this->actingAs($this->member)->post(route('documents.store', $this->project->ulid), [
            'files' => [UploadedFile::fake()->create('skrip.php', 1, 'text/plain')],
        ])->assertSessionHasErrors('files.0');

        $this->assertSame(0, ProjectDocument::count());
    }

    public function test_download_preview_and_delete_permissions(): void
    {
        $this->actingAs($this->admin)->post(route('documents.store', $this->project->ulid), [
            'files' => [UploadedFile::fake()->create('Laporan.pdf', 10, 'application/pdf')],
            'title' => 'Laporan akhir',
        ]);
        $doc = ProjectDocument::firstOrFail();
        $this->assertSame('Laporan akhir', $doc->title);

        $this->actingAs($this->member)->get(route('documents.show', $doc->ulid))
            ->assertOk()->assertDownload('Laporan.pdf');
        $this->actingAs($this->member)->get(route('documents.show', ['document' => $doc->ulid, 'lihat' => 1]))
            ->assertOk()->assertHeader('Content-Security-Policy');

        // Peran keuangan tidak boleh menghapus berkas unggahan orang lain.
        $this->actingAs($this->member)->delete(route('documents.destroy', $doc->ulid))->assertForbidden();

        $this->actingAs($this->admin)->delete(route('documents.destroy', $doc->ulid))->assertRedirect();
        $this->assertSame(0, ProjectDocument::count());
        Storage::disk('local')->assertMissing($doc->path);
    }

    public function test_other_organization_cannot_reach_documents(): void
    {
        $this->actingAs($this->admin)->post(route('documents.store', $this->project->ulid), [
            'files' => [UploadedFile::fake()->create('Rahasia.pdf', 10, 'application/pdf')],
        ]);
        $doc = ProjectDocument::firstOrFail();

        $other = Organization::create(['name' => 'Instansi Lain', 'sector' => 'kementerian']);
        $outsider = User::factory()->create(['current_organization_id' => $other->id]);
        $other->users()->attach($outsider->id, ['role' => 'admin']);

        $this->actingAs($outsider)->get(route('documents.show', $doc->ulid))->assertNotFound();
        $this->actingAs($outsider)->post(route('documents.store', $this->project->ulid), [
            'files' => [UploadedFile::fake()->create('x.pdf', 1, 'application/pdf')],
        ])->assertNotFound();
    }
}
