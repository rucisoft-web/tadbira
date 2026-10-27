<?php

use App\Http\Controllers\BudgetImportController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExpenditureController;
use App\Http\Controllers\FiscalYearController;
use App\Http\Controllers\OrganizationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\TemplateController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('dashboard')
        : Inertia::render('Welcome', ['canRegister' => Route::has('register')]);
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dasbor', DashboardController::class)->name('dashboard');
    Route::post('/tahun-anggaran', [FiscalYearController::class, 'store'])->name('fiscal-years.store');
    Route::post('/organisasi/{organization}/aktifkan', [OrganizationController::class, 'switch'])->name('organizations.switch');

    Route::get('/proyek', [ProjectController::class, 'index'])->name('projects.index');
    Route::get('/proyek/buat', [ProjectController::class, 'create'])->name('projects.create');
    Route::post('/proyek', [ProjectController::class, 'store'])->name('projects.store');
    Route::get('/proyek/{project}', [ProjectController::class, 'show'])->name('projects.show');
    Route::patch('/proyek/{project}', [ProjectController::class, 'update'])->name('projects.update');
    Route::delete('/proyek/{project}', [ProjectController::class, 'destroy'])->name('projects.destroy');

    Route::post('/proyek/{project}/aktivitas', [TaskController::class, 'store'])->name('tasks.store');
    Route::patch('/aktivitas/{task}', [TaskController::class, 'update'])->name('tasks.update');
    Route::delete('/aktivitas/{task}', [TaskController::class, 'destroy'])->name('tasks.destroy');

    Route::post('/proyek/{project}/jadikan-templat', [TemplateController::class, 'store'])->name('templates.store');
    Route::post('/templat/{template}/pratinjau', [TemplateController::class, 'preview'])->name('templates.preview');

    Route::get('/anggaran/impor', [BudgetImportController::class, 'create'])->name('budget.import');
    Route::post('/anggaran/impor', [BudgetImportController::class, 'store'])->name('budget.import.store');
    Route::get('/anggaran/templat/{type}', [BudgetImportController::class, 'template'])->name('budget.template');
    Route::post('/anggaran/{line}/realisasi', [ExpenditureController::class, 'store'])->name('expenditures.store');

    Route::get('/profil', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profil', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profil', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
