<?php

namespace App\Http\Middleware;

use App\Support\Access;
use App\Support\Terminology;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'auth' => [
                'user' => $request->user()?->only(['id', 'name', 'email']),
                'is_guest' => (bool) $request->user()?->isGuest(),
                'access' => fn () => $request->user() ? Access::for($request->user())->toArray() : null,
            ],
            'organization' => function () use ($request) {
                $org = $request->user()?->activeOrganization();

                return $org ? [
                    'ulid' => $org->ulid,
                    'name' => $org->name,
                    'short_name' => $org->short_name,
                    'sector' => $org->sector,
                    'sector_label' => config("tadbira.sectors.{$org->sector}"),
                    'is_government' => $org->isGovernment(),
                ] : null;
            },
            'organizations' => fn () => $request->user()?->activeOrganizations()->get(['organizations.ulid', 'organizations.name'])->map->only(['ulid', 'name']),
            'terms' => fn () => ($request->user()?->activeOrganization()?->terminology() ?? new Terminology)->all(),
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
            ],
        ];
    }
}
