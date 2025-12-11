<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProcurementController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\ColumnController;

Route::get('/', function () {
    return redirect('/login');
});

Route::get('/login', function () {
    return view('auth.login');
})->name('login');

Route::post('/login', [AuthController::class, 'login'])->name('login.post');

Route::get('/auth/google', [AuthController::class, 'redirectToGoogle'])->name('auth.google');
Route::get('/auth/google/callback', [AuthController::class, 'handleGoogleCallback']);

Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [ProcurementController::class, 'index'])->name('dashboard');
    Route::get('/procurement/export', [ProcurementController::class, 'export'])->name('procurement.export');
    Route::get('/procurement/create', [ProcurementController::class, 'create'])->name('procurement.create');
    Route::post('/procurement', [ProcurementController::class, 'store'])->name('procurement.store');
    Route::post('/procurement/{id}/quick-update', [ProcurementController::class, 'quickUpdate'])->name('procurement.quick-update');
    Route::resource('procurement', ProcurementController::class)->except(['create', 'store']);

    
    // Admin Routes
    Route::get('/history', [\App\Http\Controllers\HistoryController::class, 'index'])->name('history.index');

    Route::middleware([\App\Http\Middleware\EnsureUserIsAdmin::class])->prefix('admin')->name('admin.')->group(function () {
         Route::resource('users', AdminController::class)->only(['index', 'store', 'destroy', 'edit', 'update']);
         
         // New Import Flow
         Route::get('/import', [\App\Http\Controllers\AdminImportController::class, 'show'])->name('import.form');
         Route::post('/import/parse', [\App\Http\Controllers\AdminImportController::class, 'parse'])->name('import.parse');
         Route::post('/import/process', [\App\Http\Controllers\AdminImportController::class, 'process'])->name('import.process');

         // Column Management
         Route::resource('columns', \App\Http\Controllers\Admin\ColumnSettingsController::class);
         // Keep legacy reorder if needed, or remove. Assuming new page handles order edits via manual input, we can keep reorder for API completeness if drag n drop returns.
         Route::post('/columns/reorder', [ColumnController::class, 'reorder'])->name('columns.reorder'); 
         
         // Delete operations
         Route::post('/procurement/bulk-delete', [ProcurementController::class, 'bulkDestroy'])->name('procurement.bulk-delete');
         Route::post('/procurement/delete-all', [ProcurementController::class, 'truncate'])->name('procurement.delete-all');
    });
});
