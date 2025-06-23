<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CertController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\TemplateController;
use App\Http\Controllers\NotificationController;

// Redirect root to login page
Route::get('/', function () {
    return redirect()->route('show.login');
})->name('welcome');

// Auth routes that don't require authentication
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Guest routes (only accessible if not logged in)
Route::middleware(['guest'])->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('show.login');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('show.register');
    Route::post('/login', [AuthController::class, 'login'])->name('login');
    Route::post('/register', [AuthController::class, 'register'])->name('register');
    Route::post('/login', [AuthController::class, 'login'])->name('login');
    Route::get('/verify-email/{user}', [AuthController::class, 'showVerifyEmail'])->name('show.verify.email');
    Route::post('/verify-email/{user}', [AuthController::class, 'verifyEmail'])->name('verify.email');
    Route::post('/resend-verification/{user}', [AuthController::class, 'resendVerificationCode'])->name('resend.verification');
});

// Protected routes
Route::middleware(['auth'])->group(function () {
    // Home redirection
    Route::get('/home', function () {
        return redirect()->route('dashboard');
    })->name('home');

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/api/notifications', [NotificationController::class, 'getNotifications'])->name('notifications.get');
    Route::get('/manual', function () {
        return view('manual.user_manual');
    })->name('user.manual');

    // Client routes
    Route::get('/client', [ClientController::class, 'index'])->name('clients.index');
    Route::get('/client/create', [ClientController::class, 'create'])->name('clients.create');
    Route::post('/client', [ClientController::class, 'store'])->name('clients.store');
    Route::get('/client/{client}/edit', [ClientController::class, 'edit'])->name('clients.edit');
    Route::put('/client/{client}/update', [ClientController::class, 'update'])->name('clients.update');
    Route::delete('/client/{client}/destroy', [ClientController::class, 'destroy'])->name('clients.destroy');
    Route::get('/client/{id}/data', [CertController::class, 'getClientData'])->name('client.data');

    // Certificate routes
    Route::get('/certificate', [CertController::class, 'index'])->name('certificates.index');
    Route::get('/certificate/create', [CertController::class, 'create'])->name('certificates.create');
    Route::get('/certificate/view', [CertController::class, 'view'])->name('certificates.view');
    Route::post('/certificate', [CertController::class, 'store'])->name('certificates.store');
    Route::get('/certificate/{cert}/edit', [CertController::class, 'edit'])->name('certificates.edit');
    Route::put('/certificate/{cert}/update', [CertController::class, 'update'])->name('certificates.update');
    Route::delete('/certificate/{cert}/destroy', [CertController::class, 'destroy'])->name('certificates.destroy');
    Route::get('/certificate/preview/{cert}', [CertController::class, 'preview'])->name('certificates.preview');
    Route::post('/certificates/{cert}/confirm', [CertController::class, 'confirm'])->name('certificates.confirm');
    Route::get('certificates/{cert}/verification-link', [CertController::class, 'getVerificationLink'])->name('certificates.verification-link');
    Route::get('certificates/verify/{token}', [CertController::class, 'verify'])->name('certificates.verify');
    Route::post('certificates/verify/{token}', [CertController::class, 'processVerification'])->name('certificates.process-verification');
    Route::post('certificates/{cert}/renew-verification', [CertController::class, 'renewVerificationLink'])->name('certificates.renew-verification');
    Route::get('/certificates/{cert}/preview-draft', [CertController::class, 'previewDraft'])->name('certificates.previewDraft');
    Route::post('/certificates/{cert}/hod-approval', [CertController::class, 'hodApproval'])->name('certificates.hod-approval');
    Route::post('certificates/{cert}/confirm', [CertController::class, 'confirm'])->name('certificates.confirm');
    Route::get('/certificates/{cert}/assign-number', [CertController::class, 'showAssignNumberForm'])->name('certificates.assign-number.form');
    Route::post('/certificates/{cert}/assign-number', [CertController::class, 'assignNumber'])->name('certificates.assign-number');
    Route::get('/certificates/{cert}/preview-final', [CertController::class, 'previewFinal'])->name('certificates.preview-final');

    // User management routes
    Route::get('/user', [UserController::class, 'index'])->name('users.index');
    Route::get('/user/create', [UserController::class, 'create'])->name('users.create');
    Route::post('/user', [UserController::class, 'store'])->name('users.store');
    Route::get('/user/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
    Route::put('/user/{user}/update', [UserController::class, 'update'])->name('users.update');
    Route::delete('/user/{user}/destroy', [UserController::class, 'destroy'])->name('users.destroy');

    // Password reset routes - only accessible through user management
    Route::get('/user/{user}/reset-password', [UserController::class, 'showResetPasswordForm'])->name('users.reset-password');
    Route::post('/user/{user}/reset-password', [UserController::class, 'resetPassword'])->name('users.reset-password.update');

    // Template management routes
    Route::get('/template', [TemplateController::class, 'index'])->name('templates.index');
    Route::post('/template', [TemplateController::class, 'store'])->name('templates.store');
    Route::get('/template/{template}/preview', [TemplateController::class, 'preview'])->name('templates.preview');
    Route::patch('/template/{template}/toggle', [TemplateController::class, 'toggleActive'])->name('templates.toggle');
    Route::delete('/template/{template}', [TemplateController::class, 'destroy'])->name('templates.destroy');

    // Additional routes for full functionality (add these)
    Route::get('/template/{template}/edit', [TemplateController::class, 'edit'])->name('templates.edit');
    Route::put('/template/{template}', [TemplateController::class, 'update'])->name('templates.update');
    Route::get('/template/{template}/download', [TemplateController::class, 'download'])->name('templates.download');
    Route::patch('/template/{template}/toggle-active', [TemplateController::class, 'toggleActive'])->name('templates.toggle-active');
});
