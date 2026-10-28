<?php

namespace App\Support;

use App\Models\Organization;
use App\Models\Project;
use App\Models\Unit;
use App\Models\User;

/**
 * Hak akses pengguna di organisasi aktifnya, berdasarkan peran (config/tadbira.php "roles").
 */
final class Access
{
    private const LEGACY = ['anggota' => 'perencana', 'pengelola_portofolio' => 'perencana'];

    private function __construct(
        public readonly User $user,
        public readonly ?Organization $organization,
        public readonly string $role,
        public readonly ?Unit $unit,
    ) {}

    public static function for(User $user, ?Organization $organization = null): self
    {
        $organization ??= $user->activeOrganization();
        $pivot = $organization
            ? ($organization->pivot ?? $user->activeOrganizations()->whereKey($organization->id)->first()?->pivot)
            : null;

        $role = $user->isGuest() ? 'tamu' : (string) ($pivot?->role ?? 'tamu');
        $role = self::LEGACY[$role] ?? $role;
        $unit = $pivot?->unit_id ? Unit::find($pivot->unit_id) : null;

        return new self($user, $organization, $role, $unit);
    }

    public static function roleLabel(string $role): string
    {
        $role = self::LEGACY[$role] ?? $role;

        return config("tadbira.roles.{$role}.label") ?? ($role === 'tamu' ? 'Tamu' : ucfirst($role));
    }

    /** true, 'unit', atau false. */
    private function scope(string $ability): bool|string
    {
        if ($this->user->isGuest()) {
            return false;
        }

        return config("tadbira.roles.{$this->role}.can.{$ability}") ?? false;
    }

    /** Boleh melakukan $ability di sebagian atau semua kegiatan. */
    public function can(string $ability): bool
    {
        return $ability === 'view' || (bool) $this->scope($ability);
    }

    /** Boleh melakukan $ability pada kegiatan ini. */
    public function canOn(string $ability, Project $project): bool
    {
        $scope = $this->scope($ability);
        if ($scope !== 'unit') {
            return (bool) $scope;
        }

        return $this->ownsProject($project);
    }

    /** Kategori kegiatan yang boleh dipakai saat membuat kegiatan; null = bebas. */
    public function forcedCategory(): ?string
    {
        return $this->scope('create_project') === 'unit' ? $this->unit?->name : null;
    }

    private function ownsProject(Project $project): bool
    {
        $uid = (int) $this->user->id;
        if ((int) $project->leader_id === $uid || (int) $project->created_by === $uid) {
            return true;
        }
        if ($this->unit && $project->category === $this->unit->name) {
            return true;
        }

        return $project->members()->whereKey($uid)->exists();
    }

    /** Ringkasan untuk antarmuka. */
    public function toArray(): array
    {
        return [
            'role' => $this->role,
            'role_label' => self::roleLabel($this->role),
            'unit' => $this->unit?->name,
            'can' => collect(array_keys(config('tadbira.abilities')))->mapWithKeys(fn ($a) => [$a => $this->can($a)])->all(),
        ];
    }

    public function projectPermissions(Project $project): array
    {
        return [
            'edit' => $this->canOn('edit_project', $project),
            'delete' => $this->canOn('delete_project', $project),
            'documents' => $this->canOn('documents', $project),
            'import_budget' => $this->can('import_budget'),
            'record_expenditure' => $this->canOn('record_expenditure', $project),
            'approve_change' => $this->canOn('approve_change', $project),
        ];
    }
}
