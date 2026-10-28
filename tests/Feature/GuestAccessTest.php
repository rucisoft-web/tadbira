<?php

namespace Tests\Feature;

use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class GuestAccessTest extends TestCase
{
    use RefreshDatabase;

    private User $guest;

    private Organization $org;

    protected function setUp(): void
    {
        parent::setUp();

        config(['tadbira.guest.enabled' => true, 'tadbira.guest.email' => 'tamu@tadbira.test']);
        $this->org = Organization::create(['name' => 'Biro Perencanaan', 'sector' => 'kementerian']);
        $this->guest = User::factory()->create(['email' => 'tamu@tadbira.test', 'current_organization_id' => $this->org->id]);
        $this->org->users()->attach($this->guest->id, ['role' => 'tamu']);
        $this->org->fiscalYears()->create([
            'year' => 2027, 'code' => 'TA 2027', 'start_date' => '2027-01-01', 'end_date' => '2027-12-31', 'status' => 'active',
        ]);
    }

    public function test_guest_button_logs_in_and_can_view(): void
    {
        $this->get('/login')->assertInertia(fn ($page) => $page->where('demos.0.ulid', $this->org->ulid)->where('demos.0.projects', 0));
        $this->get('/')->assertInertia(fn ($page) => $page->has('demos', 1));

        $this->get(route('login.guest', ['organisasi' => $this->org->ulid]))->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($this->guest);
        $this->get(route('dashboard'))->assertOk();
        $this->get(route('projects.index'))->assertOk();
    }

    public function test_guest_cannot_change_data(): void
    {
        $this->actingAs($this->guest)
            ->from(route('projects.index'))
            ->post(route('projects.store'), ['name' => 'Coba'])
            ->assertRedirect(route('projects.index'))
            ->assertSessionHas('error');

        $this->assertDatabaseCount('projects', 0);

        $this->actingAs($this->guest)->patch(route('profile.update'), ['name' => 'X', 'email' => 'x@x.test'])->assertSessionHas('error');
        $this->assertSame('tamu@tadbira.test', $this->guest->fresh()->email);

        $this->actingAs($this->guest)->post(route('logout'));
        $this->assertGuest();
    }

    public function test_guest_choice_of_organization_is_per_session(): void
    {
        $other = Organization::create(['name' => 'PT Contoh', 'sector' => 'bumn']);
        $other->users()->attach($this->guest->id, ['role' => 'tamu']);

        $this->get(route('login.guest', ['organisasi' => $other->ulid]));
        $this->get(route('dashboard'))->assertInertia(fn ($page) => $page->where('organization.ulid', $other->ulid));
        $this->assertSame($this->org->id, $this->guest->fresh()->current_organization_id);

        $member = User::factory()->create();
        $this->actingAs($member)->get(route('login.guest'))->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($member);
    }

    public function test_guest_login_disabled(): void
    {
        config(['tadbira.guest.enabled' => false]);

        $this->get('/login')->assertInertia(fn ($page) => $page->where('demos', []));
        $this->get(route('login.guest'))->assertNotFound();
        $this->assertGuest();
    }

    public function test_reset_password_command(): void
    {
        $user = User::factory()->create(['email' => 'admin@contoh.test']);

        $this->artisan('tadbira:reset-sandi', ['email' => 'admin@contoh.test', 'password' => 'rahasia123'])
            ->expectsOutputToContain('rahasia123')
            ->assertSuccessful();
        $this->assertTrue(Hash::check('rahasia123', $user->fresh()->password));

        $this->artisan('tadbira:reset-sandi', ['email' => 'tidak@ada.test'])->assertFailed();
    }
}
