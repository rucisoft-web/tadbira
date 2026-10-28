<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\ProjectDocument;
use App\Models\User;
use Database\Seeders\DemoDocumentSeeder;
use Database\Seeders\DemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DemoDocumentTest extends TestCase
{
    use RefreshDatabase;

    public function test_demo_projects_get_sample_documents_and_reseeding_is_idempotent(): void
    {
        $this->seed([DemoSeeder::class, DemoDocumentSeeder::class]);
        $count = ProjectDocument::count();

        $projects = Project::where('is_template', false)->whereNotNull('fiscal_year_id')
            ->whereHas('organization', fn ($q) => $q->where('code', '123456'))->get();
        $this->assertNotEmpty($projects);
        foreach ($projects as $project) {
            $this->assertEqualsCanonicalizing(['TOR / KAK', 'RAB'], $project->documents()->whereIn('category', ['TOR / KAK', 'RAB'])->pluck('category')->all());
        }
        $this->assertTrue(ProjectDocument::where('created_at', '>', now())->doesntExist());

        $this->seed(DemoDocumentSeeder::class);
        $this->assertSame($count, ProjectDocument::count());
    }

    public function test_demo_file_is_served_and_survives_deletion(): void
    {
        $this->seed([DemoSeeder::class, DemoDocumentSeeder::class]);
        $doc = ProjectDocument::where('path', 'demo/tor.pdf')->firstOrFail();
        $user = User::findOrFail($doc->uploaded_by);

        $this->actingAs($user)->get(route('documents.show', $doc).'?lihat=1')
            ->assertOk()
            ->assertHeader('Content-Type', 'application/pdf');

        $this->actingAs($user)->delete(route('documents.destroy', $doc))->assertRedirect();
        $this->assertModelMissing($doc);
        $this->assertFileExists(resource_path('demo-dokumen/tor.pdf'));
    }
}
