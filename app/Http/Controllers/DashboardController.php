<?php

namespace App\Http\Controllers;

use App\Services\PortfolioService;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(Request $request, PortfolioService $portfolio): Response
    {
        $org = $this->organization($request);
        $fy = $this->fiscalYear($request, $org);

        return Inertia::render('Dashboard', [
            'fiscalYears' => $org->fiscalYears()->get(['ulid', 'code', 'year', 'status']),
            'fiscalYear' => $fy ? Arr::only($fy->toArray(), ['ulid', 'code', 'year', 'status', 'start_date', 'end_date', 'ceiling_amount']) : null,
            'portfolio' => $fy ? $portfolio->summary($fy) : null,
            'today' => now()->toDateString(),
        ]);
    }
}
