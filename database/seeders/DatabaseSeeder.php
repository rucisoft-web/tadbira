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
        $this->call([DemoSeeder::class, PtkinDemoSeeder::class, MedcoDemoSeeder::class, UnjDemoSeeder::class, DemoDocumentSeeder::class]);

        // Organisasi tanpa daftar unit kerja: pakai kategori kegiatannya.
        Organization::doesntHave('units')->each(function (Organization $org) {
            $org->projects()->where('is_template', false)->whereNotNull('category')->distinct()->orderBy('category')
                ->pluck('category')->each(fn ($name, $i) => $org->units()->create(['name' => $name, 'position' => $i]));
        });

        // Akun tamu (hanya-baca) untuk tiga organisasi demo yang tampil sebagai kartu.
        $guest = User::firstOrCreate(['email' => config('tadbira.guest.email')], [
            'name' => 'Tamu', 'password' => Hash::make(Str::random(40)), 'email_verified_at' => now(),
        ]);
        $guest->organizations()->syncWithPivotValues(Organization::whereIn('code', ['423755', 'MEDC', 'UNJ'])->pluck('id'), ['role' => 'tamu']);
        $guest->update(['current_organization_id' => User::where('email', 'admin@tadbira.test')->value('current_organization_id')]);
    }
}
