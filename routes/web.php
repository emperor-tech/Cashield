<?php
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\EmergencyResponseController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\ReportController as AdminReportController;
use App\Http\Controllers\Admin\SecurityController;
use App\Http\Controllers\Admin\SecurityTeamController;
use App\Http\Controllers\Admin\ZoneController;
use App\Http\Controllers\Admin\ShiftController;
use App\Http\Controllers\Admin\CheckpointController;
use App\Http\Controllers\Admin\AnalyticsController;
use App\Http\Controllers\Admin\BroadcastController;
use App\Http\Controllers\Admin\NotificationController;
use App\Http\Controllers\Admin\MessageController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\SystemController;
use App\Http\Controllers\Admin\ReportCategoryController;

// Include authentication routes with web middleware
Route::middleware('web')->group(function () {
    require __DIR__.'/auth.php';
});

Route::get('/test', function () {
    return 'Laravel is working!';
});

// Show landing page for guests, redirect authenticated users to dashboard
Route::get('/', function() {
    return Auth::check() ? redirect()->route('dashboard') : view('home');
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [HomeController::class, 'home'])->name('dashboard');
    Route::get('/reports', [ReportController::class, 'index'])->middleware(['auth'])->name('reports.index');
    Route::get('/reports/map-updates', [ReportController::class, 'mapUpdates'])->name('reports.map-updates');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::get('/reports/create', [ReportController::class, 'create'])->name('reports.create');
    Route::post('/reports', [ReportController::class, 'store'])->name('reports.store');
    Route::get('/reports/{report}', [ReportController::class, 'show'])->name('reports.show');
    Route::post('/reports/{report}/comments', [ReportController::class, 'addComment'])->name('reports.comments.store');
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

Route::get('/test-websocket', function () {
    event(new \App\Events\TestEvent('Hello WebSocket!'));
    return 'Event sent!';
});

// Admin Routes
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('index');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/reports/trend/{days}', [DashboardController::class, 'getTrendData'])->name('reports.trend');
    
    // User Management
    Route::resource('users', UserController::class);
    Route::patch('users/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('users.toggle-status');
    Route::get('users/roles', [UserController::class, 'roles'])->name('users.roles');
    
    // Report Management
    Route::resource('reports', AdminReportController::class);
    Route::get('reports/pending', [AdminReportController::class, 'pending'])->name('reports.pending');
    Route::get('reports/resolved', [AdminReportController::class, 'resolved'])->name('reports.resolved');
    Route::patch('reports/{report}/resolve', [AdminReportController::class, 'resolve'])->name('reports.resolve');
    Route::patch('reports/{report}/assign', [AdminReportController::class, 'assign'])->name('reports.assign');
    Route::get('reports/{report}/history', [AdminReportController::class, 'history'])->name('reports.history');
    Route::get('reports/export', [AdminReportController::class, 'export'])->name('reports.export');
    Route::get('reports/export/csv', [AdminReportController::class, 'exportCsv'])->name('reports.export.csv');
    Route::get('reports/export/pdf', [AdminReportController::class, 'exportPdf'])->name('reports.export.pdf');
    Route::patch('reports/{report}/status', [AdminReportController::class, 'updateStatus'])->name('report.status');
    Route::get('reports/{report}', [AdminReportController::class, 'show'])->name('report.show');
        
    // Report Categories
    Route::resource('reports/categories', ReportCategoryController::class)->names([
        'index' => 'reports.categories',
        'create' => 'reports.categories.create',
        'store' => 'reports.categories.store',
        'show' => 'reports.categories.show',
        'edit' => 'reports.categories.edit',
        'update' => 'reports.categories.update',
        'destroy' => 'reports.categories.destroy',
    ]);
    
    // Security Management
    Route::prefix('security')->name('security.')->group(function () {
        // Teams
        Route::resource('teams', SecurityTeamController::class);
        Route::patch('teams/{team}/toggle-status', [SecurityTeamController::class, 'toggleStatus'])->name('teams.toggle-status');
        Route::post('teams/{team}/members', [SecurityTeamController::class, 'addMember'])->name('teams.members.add');
        Route::delete('teams/{team}/members/{user}', [SecurityTeamController::class, 'removeMember'])->name('teams.members.remove');
        
        // Zones
        Route::resource('zones', ZoneController::class);
        Route::patch('zones/{zone}/toggle-status', [ZoneController::class, 'toggleStatus'])->name('zones.toggle-status');
        Route::patch('zones/{zone}/boundaries', [ZoneController::class, 'updateBoundaries'])->name('zones.boundaries');
        Route::patch('zones/{zone}/hours', [ZoneController::class, 'setOperatingHours'])->name('zones.hours');
        Route::get('zones/{zone}/stats', [ZoneController::class, 'getStats'])->name('zones.stats');
        
        // Shifts
        Route::resource('shifts', ShiftController::class);
        Route::patch('shifts/{shift}/status', [ShiftController::class, 'updateStatus'])->name('shifts.status');
        Route::post('shifts/{shift}/incidents', [ShiftController::class, 'recordIncident'])->name('shifts.incidents');
        Route::get('shifts/{shift}/stats', [ShiftController::class, 'getStats'])->name('shifts.stats');
        Route::get('shifts/{shift}/report', [ShiftController::class, 'exportReport'])->name('shifts.report');
        
        // Checkpoints
        Route::resource('checkpoints', CheckpointController::class);
        Route::post('checkpoints/{checkpoint}/scan', [CheckpointController::class, 'recordScan'])->name('checkpoints.scan');
        Route::get('checkpoints/{checkpoint}/stats', [CheckpointController::class, 'getStats'])->name('checkpoints.stats');
        Route::get('checkpoints/{checkpoint}/report', [CheckpointController::class, 'exportReport'])->name('checkpoints.report');
        Route::get('reports/categories', [AdminReportController::class, 'categories'])->name('reports.categories');
    });

    // Settings Routes
    Route::prefix('settings')->name('settings.')->group(function () {
        Route::get('general', [SettingsController::class, 'general'])->name('general');
        Route::post('general', [SettingsController::class, 'saveGeneral'])->name('general.save');
        Route::get('security', [SettingsController::class, 'security'])->name('security');
        Route::post('security', [SettingsController::class, 'saveSecurity'])->name('security.save');
        Route::get('notifications', [SettingsController::class, 'notifications'])->name('notifications');
        Route::post('notifications', [SettingsController::class, 'saveNotifications'])->name('notifications.save');
        Route::get('integrations', [SettingsController::class, 'integrations'])->name('integrations');
        Route::post('integrations', [SettingsController::class, 'saveIntegrations'])->name('integrations.save');
        Route::get('backup', [SettingsController::class, 'backup'])->name('backup');
        Route::post('backup', [SettingsController::class, 'saveBackup'])->name('backup.save');
    });

    // System Routes
    Route::prefix('system')->name('system.')->group(function () {
        Route::get('logs', [SystemController::class, 'logs'])->name('logs');
        Route::get('audit', [SystemController::class, 'audit'])->name('audit');
        Route::get('health', [SystemController::class, 'health'])->name('health');
    });

    // Analytics Routes
    Route::prefix('analytics')->name('analytics.')->group(function () {
        Route::get('/', [AnalyticsController::class, 'dashboard'])->name('dashboard');
        Route::get('/reports', [AnalyticsController::class, 'reports'])->name('reports');
        Route::get('/security', [AnalyticsController::class, 'security'])->name('security');
        Route::get('/heatmap', [AnalyticsController::class, 'heatmap'])->name('heatmap');
    });

    // Communication Routes
    Route::prefix('communication')->name('communication.')->group(function () {
        Route::get('/broadcasts', [BroadcastController::class, 'index'])->name('broadcasts');
        Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications');
        Route::get('/messages', [MessageController::class, 'index'])->name('messages');
        
        Route::resource('broadcasts', BroadcastController::class)->except(['index']);
        Route::post('broadcasts/{broadcast}/send', [BroadcastController::class, 'send'])->name('broadcasts.send');
        Route::post('broadcasts/{broadcast}/cancel', [BroadcastController::class, 'cancel'])->name('broadcasts.cancel');
        Route::get('broadcasts/{broadcast}/stats', [BroadcastController::class, 'getStats'])->name('broadcasts.stats');
        Route::resource('notifications', NotificationController::class)->except(['index']);
        Route::post('notifications/{notification}/send', [NotificationController::class, 'send'])->name('notifications.send');
        Route::post('notifications/{notification}/cancel', [NotificationController::class, 'cancel'])->name('notifications.cancel');
        Route::get('notifications/{notification}/stats', [NotificationController::class, 'getStats'])->name('notifications.stats');
        Route::resource('messages', MessageController::class)->except(['index']);
    });
});

// Security Resource API Routes
Route::middleware(['auth', 'role:admin,security'])->prefix('api')->group(function () {
    Route::get('/active-incidents', [\App\Http\Controllers\Api\SecurityResourceController::class, 'activeIncidents']);
    Route::get('/resources', [\App\Http\Controllers\Api\SecurityResourceController::class, 'resources']);
    Route::get('/available-teams', [\App\Http\Controllers\Api\SecurityResourceController::class, 'availableTeams']);
    Route::patch('/resources/{resource}/status', [\App\Http\Controllers\Api\SecurityResourceController::class, 'updateResourceStatus']);
});

// Anonymous Report Routes
Route::get('/reports/anonymous/create', [ReportController::class, 'createAnonymous'])->name('reports.anonymous.create');
Route::post('/reports/anonymous', [ReportController::class, 'storeAnonymous'])->name('reports.anonymous.store');

// Public API Routes
Route::prefix('api')->group(function () {
    Route::post('/panic', [ReportController::class, 'panic'])->name('api.panic.report');
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

// Security Management Routes
Route::prefix('admin/security')->name('admin.security.')->middleware(['auth', 'admin'])->group(function () {
    Route::get('/', [SecurityController::class, 'index'])->name('index');
});
