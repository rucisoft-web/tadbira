<?php

namespace Database\Seeders;

use App\Models\Organization;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([DemoSeeder::class, PtkinDemoSeeder::class]);

        // Akun tamu (hanya-baca) yang bisa melihat semua organisasi demo.
        $guest = User::firstOrCreate(['email' => config('tadbira.guest.email')], [
            'name' => 'Tamu', 'password' => Hash::make(Str::random(40)), 'email_verified_at' => now(),
        ]);
        $guest->organizations()->syncWithPivotValues(Organization::pluck('id'), ['role' => 'tamu']);
        $guest->update(['current_organization_id' => User::where('email', 'admin@tadbira.test')->value('current_organization_id')]);
    }
}
