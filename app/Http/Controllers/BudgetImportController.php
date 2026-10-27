<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Services\Budget\BudgetImporter;
use App\Services\Budget\BudgetSheetParser;
use App\Services\Budget\BudgetTemplate;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;
use RuntimeException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Throwable;

class BudgetImportController extends Controller
{
    public function create(Request $request): Response
    {
        $org = $this->organization($request);

        return Inertia::render('Budget/Import', [
            'fiscalYears' => $org->fiscalYears()->get(['ulid', 'code', 'year']),
            'selectedFiscalYear' => $this->fiscalYear($request, $org)?->ulid,
            'projects' => Project::real()->where('organization_id', $org->id)->orderBy('name')->get(['ulid', 'code', 'name']),
            'isGovernment' => $org->isGovernment(),
        ]);
    }

    public function store(Request $request, BudgetSheetParser $parser, BudgetImporter $importer): RedirectResponse
    {
        $org = $this->organization($request);
        $data = $request->validate([
            'file' => ['required', 'file', 'max:10240', 'mimes:xls,xlsx,csv,ods'],
            'mode' => ['required', Rule::in(['projects', 'existing'])],
            'fiscal_year' => ['required_if:mode,projects', 'nullable', Rule::exists('fiscal_years', 'ulid')->where('organization_id', $org->id)],
            'project' => ['required_if:mode,existing', 'nullable', Rule::exists('projects', 'ulid')->where('organization_id', $org->id)],
            'build_wbs' => ['boolean'],
        ]);

        try {
            $parsed = $parser->parseFile($request->file('file')->getRealPath());
        } catch (RuntimeException $e) {
            throw ValidationException::withMessages(['file' => $e->getMessage()]);
        } catch (Throwable $e) {
            report($e);
            throw ValidationException::withMessages(['file' => 'Berkas tidak dapat dibaca. Pastikan formatnya XLS/XLSX.']);
        }

        $buildWbs = $data['build_wbs'] ?? true;

        if ($data['mode'] === 'existing') {
            $project = Project::where('ulid', $data['project'])->firstOrFail();
            $count = $importer->importIntoProject($project, $parsed, $buildWbs);

            return redirect()->route('projects.show', $project)->with('success', "{$count} baris anggaran diimpor.");
        }

        $fy = $org->fiscalYears()->where('ulid', $data['fiscal_year'])->firstOrFail();
        $projects = $importer->importAsProjects($fy, $parsed, $request->user()->id, $buildWbs);

        return redirect()->route('projects.index', ['ta' => $fy->ulid])
            ->with('success', count($projects).' proyek dibuat dari '.($parsed['format'] === 'pok' ? 'POK' : 'RAB').'.');
    }

    public function template(string $type): BinaryFileResponse
    {
        abort_unless(in_array($type, ['pok', 'rab'], true), 404);
        $path = storage_path("app/templat-{$type}.xlsx");
        if ($type === 'pok') {
            BudgetTemplate::write(BudgetTemplate::pokSample((int) now()->format('Y') + 1), $path, 'POK');
        } else {
            BudgetTemplate::write(BudgetTemplate::rabSample(), $path, 'RAB');
        }

        return response()->download($path, $type === 'pok' ? 'templat-pok-rka-kl.xlsx' : 'templat-rab-rkap.xlsx')->deleteFileAfterSend();
    }
}
