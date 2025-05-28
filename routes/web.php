<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CertController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\TemplateController;

// Redirect root to login page
Route::get('/', function () {
    return redirect()->route('show.login');
})->name('welcome');

// Auth routes that don't require authentication
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Guest routes (only accessible if not logged in)
Route::middleware(['guest'])->group(function () {
    Route::get('/register', [AuthController::class, 'showRegister'])->name('show.register');
    Route::get('/login', [AuthController::class, 'showLogin'])->name('show.login');
    Route::post('/register', [AuthController::class, 'register'])->name('register');
    Route::post('/login', [AuthController::class, 'login'])->name('login');
});

// Protected routes
Route::middleware(['auth'])->group(function () {
    // Home redirection
    Route::get('/home', function () {
        return redirect()->route('dashboard');
    })->name('home');

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

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
    Route::get('/certificate/{cert}', [CertController::class, 'preview'])->name('certificates.preview');

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
    Route::get('/template-management', [TemplateController::class, 'index'])->name('templates.index');
    Route::post('/template-management', [TemplateController::class, 'store'])->name('templates.store');
    Route::get('/template-management/{template}/preview', [TemplateController::class, 'preview'])->name('templates.preview');
    Route::delete('/template-management/{template}', [TemplateController::class, 'destroy'])->name('templates.destroy');
});