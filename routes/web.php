<?php

use App\Http\Controllers\BudgetImportController;
use App\Http\Controllers\Control\BaselineController;
use App\Http\Controllers\Control\ChangeRequestController;
use App\Http\Controllers\Control\ReportController;
use App\Http\Controllers\Control\RiskController;
use App\Http\Controllers\Control\WorkloadController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExpenditureController;
use App\Http\Controllers\FiscalYearController;
use App\Http\Controllers\OrganizationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ProjectDocumentController;
use App\Http\Controllers\Settings\FiscalYearSettingsController;
use App\Http\Controllers\Settings\HolidayController;
use App\Http\Controllers\Settings\MemberController;
use App\Http\Controllers\Settings\OrganizationSettingsController;
use App\Http\Controllers\Settings\UnitController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\TemplateController;
use App\Support\GuestDemo;
use App\Support\UserGuide;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('dashboard')
        : Inertia::render('Welcome', [
            'canRegister' => Route::has('register'),
            'demos' => GuestDemo::cards(),
            'links' => config('tadbira.landing'),
            'guide' => UserGuide::sections(),
        ]);
});

Route::get('/untuk-eksekutif', fn () => Inertia::render('Executive', [
    'demos' => GuestDemo::cards(),
    'links' => config('tadbira.landing'),
    'guide' => UserGuide::sections(),
]))->name('executive');

Route::get('/workshop', fn () => Inertia::render('Workshop', [
    'demos' => GuestDemo::cards(),
    'links' => config('tadbira.landing'),
    'guide' => UserGuide::sections(),
    'workshop' => config('tadbira.workshop'),
]))->name('workshop');

Route::get('/panduan', fn () => Inertia::render('Guide', [
    ...UserGuide::render(),
    'links' => config('tadbira.landing'),
]))->name('guide');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dasbor', DashboardController::class)->name('dashboard');
    Route::post('/tahun-anggaran', [FiscalYearController::class, 'store'])->middleware('can.tadbira:fiscal_years')->name('fiscal-years.store');
    Route::post('/organisasi/{organization}/aktifkan', [OrganizationController::class, 'switch'])->name('organizations.switch');

    Route::get('/proyek', [ProjectController::class, 'index'])->name('projects.index');
    Route::get('/proyek/buat', [ProjectController::class, 'create'])->middleware('can.tadbira:create_project')->name('projects.create');
    Route::post('/proyek', [ProjectController::class, 'store'])->middleware('can.tadbira:create_project')->name('projects.store');
    Route::get('/proyek/{project}', [ProjectController::class, 'show'])->name('projects.show');
    Route::patch('/proyek/{project}', [ProjectController::class, 'update'])->name('projects.update');
    Route::delete('/proyek/{project}', [ProjectController::class, 'destroy'])->name('projects.destroy');

    Route::post('/proyek/{project}/dokumen', [ProjectDocumentController::class, 'store'])->name('documents.store');
    Route::get('/dokumen/{document}', [ProjectDocumentController::class, 'show'])->name('documents.show');
    Route::patch('/dokumen/{document}', [ProjectDocumentController::class, 'update'])->name('documents.update');
    Route::delete('/dokumen/{document}', [ProjectDocumentController::class, 'destroy'])->name('documents.destroy');

    Route::get('/proyek/{project}/laporan', [ReportController::class, 'status'])->name('projects.report');
    Route::get('/proyek/{project}/excel', [ReportController::class, 'project'])->name('projects.export');
    Route::get('/beban-kerja', WorkloadController::class)->name('workload');
    Route::get('/portofolio/excel', [ReportController::class, 'portfolio'])->name('portfolio.export');
    Route::post('/proyek/{project}/baseline', [BaselineController::class, 'store'])->name('baseline.store');
    Route::post('/proyek/{project}/risiko', [RiskController::class, 'store'])->name('risks.store');
    Route::patch('/risiko/{risk}', [RiskController::class, 'update'])->name('risks.update');
    Route::delete('/risiko/{risk}', [RiskController::class, 'destroy'])->name('risks.destroy');
    Route::post('/proyek/{project}/perubahan', [ChangeRequestController::class, 'store'])->name('changes.store');
    Route::post('/perubahan/{changeRequest}/keputusan', [ChangeRequestController::class, 'decide'])->name('changes.decide');
    Route::post('/perubahan/{changeRequest}/tarik', [ChangeRequestController::class, 'withdraw'])->name('changes.withdraw');

    Route::post('/proyek/{project}/aktivitas', [TaskController::class, 'store'])->name('tasks.store');
    Route::patch('/aktivitas/{task}', [TaskController::class, 'update'])->name('tasks.update');
    Route::delete('/aktivitas/{task}', [TaskController::class, 'destroy'])->name('tasks.destroy');

    Route::post('/proyek/{project}/jadikan-templat', [TemplateController::class, 'store'])->name('templates.store');
    Route::post('/templat/{template}/pratinjau', [TemplateController::class, 'preview'])->name('templates.preview');

    Route::middleware('can.tadbira:import_budget')->group(function () {
        Route::get('/anggaran/impor', [BudgetImportController::class, 'create'])->name('budget.import');
        Route::post('/anggaran/impor', [BudgetImportController::class, 'store'])->name('budget.import.store');
    });
    Route::get('/anggaran/templat/{type}', [BudgetImportController::class, 'template'])->name('budget.template');
    Route::post('/anggaran/{line}/realisasi', [ExpenditureController::class, 'store'])->name('expenditures.store');

    Route::prefix('pengaturan')->name('settings.')->middleware('can.tadbira:settings')->group(function () {
        Route::redirect('/', '/pengaturan/organisasi')->name('index');
        Route::get('/organisasi', [OrganizationSettingsController::class, 'edit'])->name('organization');
        Route::patch('/organisasi', [OrganizationSettingsController::class, 'update'])->name('organization.update');
        Route::get('/peran', [OrganizationSettingsController::class, 'roles'])->name('roles');

        Route::get('/pengguna', [MemberController::class, 'index'])->name('members');
        Route::post('/pengguna', [MemberController::class, 'store'])->name('members.store');
        Route::patch('/pengguna/{user}', [MemberController::class, 'update'])->name('members.update');
        Route::post('/pengguna/{user}/sandi', [MemberController::class, 'resetPassword'])->name('members.password');
        Route::delete('/pengguna/{user}', [MemberController::class, 'destroy'])->name('members.destroy');

        Route::get('/unit', [UnitController::class, 'index'])->name('units');
        Route::post('/unit', [UnitController::class, 'store'])->name('units.store');
        Route::patch('/unit/{unit}', [UnitController::class, 'update'])->name('units.update');
        Route::delete('/unit/{unit}', [UnitController::class, 'destroy'])->name('units.destroy');

        Route::get('/tahun-anggaran', [FiscalYearSettingsController::class, 'index'])->name('fiscal-years');
        Route::patch('/tahun-anggaran/{fiscalYear}', [FiscalYearSettingsController::class, 'update'])->name('fiscal-years.update');
        Route::delete('/tahun-anggaran/{fiscalYear}', [FiscalYearSettingsController::class, 'destroy'])->name('fiscal-years.destroy');

        Route::get('/hari-libur', [HolidayController::class, 'index'])->name('holidays');
        Route::post('/hari-libur', [HolidayController::class, 'store'])->name('holidays.store');
        Route::delete('/hari-libur/{holiday}', [HolidayController::class, 'destroy'])->name('holidays.destroy');
    });

    Route::get('/profil', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profil', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profil', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
