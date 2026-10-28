<?php

namespace Tests\Feature;

use App\Models\Organization;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SettingsTest extends TestCase
{
    use RefreshDatabase;

    private Organization $org;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->org = Organization::create(['name' => 'Biro Perencanaan', 'sector' => 'kementerian']);
        $this->admin = $this->member('admin');
        $this->org->fiscalYears()->create(['year' => 2027, 'code' => 'TA 2027', 'start_date' => '2027-01-01', 'end_date' => '2027-12-31', 'status' => 'active']);
    }

    private function member(string $role, ?int $unitId = null, array $attributes = []): User
    {
        $user = User::factory()->create(['current_organization_id' => $this->org->id] + $attributes);
        $this->org->users()->attach($user->id, ['role' => $role, 'unit_id' => $unitId]);

        return $user;
    }

    public function test_only_admin_can_open_settings(): void
    {
        $this->actingAs($this->admin)->get(route('settings.members'))->assertOk()
            ->assertInertia(fn ($page) => $page->component('Settings/Members')->has('members', 1));

        foreach (['pimpinan', 'perencana', 'keuangan', 'operator'] as $role) {
            $this->actingAs($this->member($role))->get(route('settings.organization'))->assertForbidden();
        }
    }

    public function test_admin_updates_organization_profile(): void
    {
        $this->actingAs($this->admin)->patch(route('settings.organization.update'), [
            'name' => 'Biro Perencanaan dan Keuangan', 'short_name' => 'BPK', 'code' => '999', 'sector' => 'lembaga', 'timezone' => 'Asia/Makassar',
        ])->assertRedirect();

        $this->assertSame('Biro Perencanaan dan Keuangan', $this->org->fresh()->name);
        $this->assertSame('lembaga', $this->org->fresh()->sector);
    }

    public function test_admin_adds_new_user_and_existing_account(): void
    {
        $unit = $this->org->units()->create(['name' => 'FST']);
        $this->actingAs($this->admin)->post(route('settings.members.store'), [
            'name' => 'Operator FST', 'email' => 'fst@contoh.test', 'role' => 'operator', 'unit_id' => $unit->id, 'password' => 'rahasia123',
        ])->assertRedirect()->assertSessionHas('new_password');

        $user = User::where('email', 'fst@contoh.test')->firstOrFail();
        $this->assertTrue(Hash::check('rahasia123', $user->password));
        $this->assertSame($unit->id, (int) $this->org->users()->whereKey($user->id)->first()->pivot->unit_id);

        // Akun yang sudah ada ditambahkan tanpa mengganti kata sandinya.
        $other = User::factory()->create(['email' => 'lain@contoh.test']);
        $this->actingAs($this->admin)->post(route('settings.members.store'), ['name' => 'X', 'email' => 'lain@contoh.test', 'role' => 'pimpinan'])
            ->assertSessionMissing('new_password');
        $this->assertSame('pimpinan', $this->org->users()->whereKey($other->id)->first()->pivot->role);

        $this->actingAs($this->admin)->post(route('settings.members.store'), ['name' => 'X', 'email' => 'lain@contoh.test', 'role' => 'pimpinan'])
            ->assertSessionHasErrors('email');
    }

    public function test_deactivated_user_cannot_log_in(): void
    {
        $user = $this->member('perencana', null, ['password' => Hash::make('rahasia123')]);
        $this->actingAs($this->admin)->patch(route('settings.members.update', $user->id), ['is_active' => false])->assertRedirect();
        auth()->logout();

        $this->post('/login', ['email' => $user->email, 'password' => 'rahasia123'])->assertSessionHasErrors('email');
        $this->assertGuest();

        $this->actingAs($this->admin)->patch(route('settings.members.update', $user->id), ['is_active' => true]);
        auth()->logout();
        $this->post('/login', ['email' => $user->email, 'password' => 'rahasia123'])->assertRedirect();
        $this->assertAuthenticatedAs($user);
        $this->assertNotNull($user->fresh()->last_login_at);
    }

    public function test_last_admin_is_protected(): void
    {
        $this->actingAs($this->admin)->patch(route('settings.members.update', $this->admin->id), ['role' => 'perencana'])
            ->assertSessionHas('error');
        $this->assertSame('admin', $this->org->users()->whereKey($this->admin->id)->first()->pivot->role);

        $second = $this->member('admin');
        $this->actingAs($second)->delete(route('settings.members.destroy', $this->admin->id))->assertSessionHas('success');
        $this->actingAs($second)->patch(route('settings.members.update', $second->id), ['is_active' => false])->assertSessionHas('error');
    }

    public function test_reset_password_returns_new_password_once(): void
    {
        $user = $this->member('keuangan');
        $this->actingAs($this->admin)->post(route('settings.members.password', $user->id))
            ->assertSessionHas('new_password', fn ($v) => $v['email'] === $user->email && Hash::check($v['password'], $user->fresh()->password));

        // Pengguna organisasi lain tidak bisa disentuh.
        $stranger = User::factory()->create();
        $this->actingAs($this->admin)->post(route('settings.members.password', $stranger->id))->assertNotFound();
    }

    public function test_renaming_unit_updates_project_categories(): void
    {
        $unit = $this->org->units()->create(['name' => 'FST']);
        $project = Project::create(['organization_id' => $this->org->id, 'name' => 'Lab', 'category' => 'FST']);

        $this->actingAs($this->admin)->patch(route('settings.units.update', $unit->ulid), ['name' => 'Saintek', 'full_name' => 'Fakultas Sains dan Teknologi'])
            ->assertRedirect();
        $this->assertSame('Saintek', $project->fresh()->category);

        $this->actingAs($this->admin)->delete(route('settings.units.destroy', $unit->ulid))->assertSessionHas('error');
    }

    public function test_fiscal_year_and_holiday_settings(): void
    {
        $fy = $this->org->fiscalYears()->first();
        $this->actingAs($this->admin)->patch(route('settings.fiscal-years.update', $fy->ulid), [
            'code' => 'TA 2027', 'status' => 'closed', 'start_date' => '2027-01-01', 'end_date' => '2027-12-31', 'ceiling_amount' => 1000000,
        ])->assertRedirect();
        $this->assertTrue($fy->fresh()->isClosed());

        $this->actingAs($this->admin)->post(route('settings.holidays.store'), ['date' => '2027-03-02', 'name' => 'Dies Natalis'])->assertRedirect();
        $holiday = $this->org->holidays()->firstOrFail();
        $this->actingAs($this->admin)->get(route('settings.holidays', ['tahun' => 2027]))
            ->assertInertia(fn ($page) => $page->where('holidays.0.name', 'Dies Natalis'));
        $this->actingAs($this->admin)->delete(route('settings.holidays.destroy', $holiday->id))->assertRedirect();
        $this->assertSame(0, $this->org->holidays()->count());
    }
}
