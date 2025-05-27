<?php
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\EmergencyResponseController;

// Include authentication routes with web middleware
Route::middleware('web')->group(function () {
    require __DIR__.'/auth.php';
});

Route::get('/test', function () {
    return 'Laravel is working!';
});

Route::get('/', [App\Http\Controllers\HomeController::class, 'home'])->name('home');

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
    Route::get('/reports/{report}/chat', [\App\Http\Controllers\ChatController::class, 'fetch'])->name('reports.chat.fetch');
    Route::post('/reports/{report}/chat', [\App\Http\Controllers\ChatController::class, 'send'])->name('reports.chat.send');
    Route::post('/profile/subscribe-area', [ProfileController::class, 'subscribeArea'])->name('profile.subscribe-area');
    Route::delete('/profile/unsubscribe-area/{area}', [ProfileController::class, 'unsubscribeArea'])->name('profile.unsubscribe-area');

    // Emergency Response Routes
    Route::post('/reports/{report}/respond', [EmergencyResponseController::class, 'respond'])->name('reports.respond');
    Route::get('/reports/{report}/responses', [EmergencyResponseController::class, 'index'])->name('reports.responses.index');
    Route::get('/responses/{response}', [EmergencyResponseController::class, 'show'])->name('responses.show');
    Route::post('/responses/{response}/update', [EmergencyResponseController::class, 'update'])->name('responses.update');
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

// Security Resource API Routes
Route::middleware(['auth', 'role:admin,security'])->prefix('api')->group(function () {
    Route::get('/active-incidents', [\App\Http\Controllers\Api\SecurityResourceController::class, 'activeIncidents']);
    Route::get('/resources', [\App\Http\Controllers\Api\SecurityResourceController::class, 'resources']);
    Route::get('/available-teams', [\App\Http\Controllers\Api\SecurityResourceController::class, 'availableTeams']);
    Route::patch('/resources/{resource}/status', [\App\Http\Controllers\Api\SecurityResourceController::class, 'updateResourceStatus']);
});

// Security Dispatch Dashboard Route
Route::middleware(['auth', 'role:admin,security'])->group(function () {
    Route::get('/security/dispatch', function () {
        return view('security.dispatch');
    })->name('security.dispatch');
});

// Analytics Routes
Route::middleware(['auth', 'role:admin,security'])->group(function () {
    Route::get('/analytics', function () {
        return view('analytics.dashboard');
    })->name('analytics.dashboard');
    Route::get('/api/analytics', [App\Http\Controllers\Api\AnalyticsController::class, 'index']);
});

// Notification Preferences Routes
Route::middleware(['auth'])->prefix('api')->group(function () {
    Route::get('/notification-preferences', [App\Http\Controllers\Api\NotificationPreferencesController::class, 'index']);
    Route::post('/notification-preferences', [App\Http\Controllers\Api\NotificationPreferencesController::class, 'store']);
    Route::post('/notification-preferences/test', [App\Http\Controllers\Api\NotificationPreferencesController::class, 'test']);
});

// User Stats and Achievements Routes
Route::middleware(['auth'])->prefix('api')->group(function () {
    Route::get('/users/{user}/stats', [App\Http\Controllers\Api\UserStatsController::class, 'stats']);
    Route::get('/users/{user}/achievements', [App\Http\Controllers\Api\UserStatsController::class, 'achievements']);
});
