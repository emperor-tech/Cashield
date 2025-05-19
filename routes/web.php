<?php
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;

Route::get('/', [ReportController::class, 'index']);

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::get('/reports/create', [ReportController::class, 'create'])->name('reports.create');
    Route::post('/reports', [ReportController::class, 'store'])->name('reports.store');
    Route::get('/reports/{report}', [ReportController::class, 'show'])->name('reports.show');
});

Route::post('/panic', [ReportController::class, 'panic'])->name('panic.report');

Route::get('/test-websocket', function () {
    event(new \App\Events\TestEvent('Hello WebSocket!'));
    return 'Event sent!';
});

Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('/admin', [\App\Http\Controllers\AdminController::class, 'index'])->name('admin.index');
    Route::patch('/admin/report/{report}/status', [\App\Http\Controllers\AdminController::class, 'updateReportStatus'])->name('admin.report.status');
    Route::get('/admin/report/{report}', [\App\Http\Controllers\AdminController::class, 'showReport'])->name('admin.report.show');
    Route::post('/admin/report/{report}/comment', [\App\Http\Controllers\AdminController::class, 'addComment'])->name('admin.report.comment');
    Route::get('/admin/reports/export/csv', [\App\Http\Controllers\AdminController::class, 'exportCsv'])->name('admin.reports.export.csv');
    Route::get('/admin/reports/export/pdf', [\App\Http\Controllers\AdminController::class, 'exportPdf'])->name('admin.reports.export.pdf');
});

require __DIR__.'/auth.php';
