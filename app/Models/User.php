<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'current_organization_id',
        'capacity_hours_per_day',
        'last_login_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'last_login_at' => 'datetime',
        ];
    }

    public function organizations(): BelongsToMany
    {
        return $this->belongsToMany(Organization::class)->withPivot(['role', 'unit_id', 'is_active']);
    }

    /** Keanggotaan yang tidak dinonaktifkan admin. */
    public function activeOrganizations(): BelongsToMany
    {
        return $this->organizations()->wherePivot('is_active', true);
    }

    public function currentOrganization(): BelongsTo
    {
        return $this->belongsTo(Organization::class, 'current_organization_id');
    }

    /**
     * Organisasi aktif pengguna; jatuh ke organisasi pertama yang diikutinya.
     */
    public function activeOrganization(): ?Organization
    {
        // Akun tamu dipakai bersama; pilihan organisasinya disimpan per sesi, bukan di basis data.
        if ($this->isGuest() && ($id = session('guest_organization_id'))) {
            $org = $this->activeOrganizations()->whereKey($id)->first();
            if ($org) {
                return $org;
            }
        }

        return $this->activeOrganizations()->orderByRaw('organizations.id = ? desc', [(int) $this->current_organization_id])
            ->orderBy('organizations.id')->first();
    }

    /** Akun tamu bila fitur masuk sebagai tamu diaktifkan dan akunnya ada. */
    public static function guestAccount(): ?self
    {
        if (! config('tadbira.guest.enabled')) {
            return null;
        }

        return static::where('email', config('tadbira.guest.email'))->first();
    }

    /** Akun tamu bersama: hanya-baca. */
    public function isGuest(): bool
    {
        return strcasecmp($this->email, (string) config('tadbira.guest.email')) === 0;
    }
}
