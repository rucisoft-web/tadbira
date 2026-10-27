<?php

use App\Models\User;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('tadbira:pengguna', function () {
    $this->table(['Surel', 'Nama', 'Organisasi'], User::with('organizations')->orderBy('email')->get()->map(fn ($u) => [
        $u->email, $u->name, $u->organizations->pluck('name')->join(', '),
    ]));
})->purpose('Daftar akun pengguna');

Artisan::command('tadbira:reset-sandi {email} {password?}', function (string $email, ?string $password = null) {
    $user = User::where('email', $email)->first();
    if ($user === null) {
        $this->error("Akun {$email} tidak ditemukan. Lihat daftar akun dengan: php artisan tadbira:pengguna");

        return 1;
    }
    $password ??= Str::password(12, symbols: false);
    $user->forceFill(['password' => Hash::make($password), 'remember_token' => Str::random(60)])->save();
    $this->info("Kata sandi {$email} diganti menjadi: {$password}");

    return 0;
})->purpose('Atur ulang kata sandi akun tanpa surel');
