<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
    }

    public function test_new_users_can_register(): void
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'organization_name' => 'Biro Perencanaan',
            'sector' => 'kementerian',
        ]);

        $this->assertAuthenticated();
        $user = \App\Models\User::where('email', 'test@example.com')->first();
        $this->assertSame('Biro Perencanaan', $user->currentOrganization->name);
        $this->assertSame(1, $user->currentOrganization->fiscalYears()->count());
        $response->assertRedirect(route('dashboard', absolute: false));
    }
}
