<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class OrganizationController extends Controller
{
    public function switch(Request $request, Organization $organization): RedirectResponse
    {
        abort_unless($request->user()->organizations()->whereKey($organization->id)->exists(), 403);
        $request->user()->update(['current_organization_id' => $organization->id]);

        return redirect()->route('dashboard');
    }
}
