<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin;
use App\Http\Controllers\Staff;
use App\Http\Controllers\User;

// ─── Root redirect ────────────────────────────────────────────────────────────
Route::get('/', function () {
    if (!auth()->check()) return redirect()->route('login');
    return match(auth()->user()->role) {
        'admin' => redirect()->route('admin.dashboard'),
        'staff' => redirect()->route('staff.dashboard'),
        default => redirect()->route('user.dashboard'),
    };
});

// ─── Breeze Auth Routes ───────────────────────────────────────────────────────
require __DIR__ . '/auth.php';

// ─── Profile Routes ───────────────────────────────────────────────────────────
Route::middleware('auth')->group(function () {
    Route::get('/profile', ['App\Http\Controllers\ProfileController', 'edit'])->name('profile.edit');
    Route::put('/profile', ['App\Http\Controllers\ProfileController', 'update'])->name('profile.update');
});

// ─── ADMIN Routes ─────────────────────────────────────────────────────────────
Route::prefix('admin')
    ->name('admin.')
    ->middleware(['auth', 'role:admin'])
    ->group(function () {

        Route::get('/dashboard', [Admin\DashboardController::class, 'index'])->name('dashboard');

        // Movies (full CRUD for admin)
        Route::resource('movies', Admin\MovieController::class);

        // Rentals
        Route::get('/rentals',                       [Admin\RentalController::class, 'index'])->name('rentals.index');
        Route::get('/rentals/create',                [Admin\RentalController::class, 'create'])->name('rentals.create');
        Route::post('/rentals',                      [Admin\RentalController::class, 'store'])->name('rentals.store');
        Route::patch('/rentals/{rental}/return',     [Admin\RentalController::class, 'returnMovie'])->name('rentals.return');

        // Users
        Route::resource('users', Admin\UserController::class);

        // Approvals
        Route::get('/approvals',                      [Admin\ApprovalController::class, 'index'])->name('approvals.index');
        Route::patch('/approvals/{approval}/approve', [Admin\ApprovalController::class, 'approve'])->name('approvals.approve');
        Route::patch('/approvals/{approval}/reject',  [Admin\ApprovalController::class, 'reject'])->name('approvals.reject');

        // Reports & Audit Logs
        Route::get('/reports',    [Admin\ReportController::class, 'index'])->name('reports.index');
        Route::get('/audit-logs', [Admin\ReportController::class, 'auditLogs'])->name('audit.index');
        Route::get('/audit-logs/export', [Admin\ReportController::class, 'exportAuditLogs'])->name('audit.export');
    });

// ─── STAFF Routes ─────────────────────────────────────────────────────────────
Route::prefix('staff')
    ->name('staff.')
    ->middleware(['auth', 'role:admin,staff'])
    ->group(function () {

        Route::get('/dashboard', [Staff\DashboardController::class, 'index'])->name('dashboard');

        // Movies (view + request actions — all changes go through approval)
        Route::get('/movies',                              [Staff\MovieController::class, 'index'])->name('movies.index');
        Route::post('/movies/request-add',                 [Staff\MovieController::class, 'requestAdd'])->name('movies.request-add');
        Route::get('/movies/{movie}/request-edit',         [Staff\MovieController::class, 'editRequest'])->name('movies.request-edit');
        Route::post('/movies/{movie}/request-edit',        [Staff\MovieController::class, 'requestEdit'])->name('movies.submit-edit');
        Route::post('/movies/{movie}/request-delete',      [Staff\MovieController::class, 'requestDelete'])->name('movies.request-delete');

        // Rentals
        Route::get('/rentals',                       [Staff\RentalController::class, 'index'])->name('rentals.index');
        Route::get('/rentals/create',                [Staff\RentalController::class, 'create'])->name('rentals.create');
        Route::post('/rentals',                      [Staff\RentalController::class, 'store'])->name('rentals.store');
        Route::patch('/rentals/{rental}/return',     [Staff\RentalController::class, 'returnMovie'])->name('rentals.return');

        // My approval requests
        Route::get('/approvals', [Staff\ApprovalController::class, 'index'])->name('approvals.index');
    });

// ─── USER Routes ──────────────────────────────────────────────────────────────
Route::prefix('user')
    ->name('user.')
    ->middleware(['auth', 'role:admin,staff,user'])
    ->group(function () {

        Route::get('/dashboard', [User\DashboardController::class, 'index'])->name('dashboard');

        // Browse movies
        Route::get('/movies',         [User\MovieController::class, 'index'])->name('movies.index');
        Route::get('/movies/{movie}', [User\MovieController::class, 'show'])->name('movies.show');

        // Rentals
        Route::get('/rentals',  [User\RentalController::class, 'index'])->name('rentals.index');
        Route::post('/rentals', [User\RentalController::class, 'store'])->name('rentals.store');
    });