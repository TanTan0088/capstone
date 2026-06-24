<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;

Route::get('/', [AuthController::class, 'showLogin'])->name('login');

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.submit');

Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register'])->name('register.submit');

Route::get('/guest-login', [AuthController::class, 'guestLogin'])->name('guest.login');

Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

Route::post('/dashboard/simulation/start', [DashboardController::class, 'startSimulation'])
    ->name('dashboard.simulation.start');

Route::post('/dashboard/simulation/generate', [DashboardController::class, 'generateSimulationReading'])
    ->name('dashboard.simulation.generate');

Route::post('/dashboard/simulation/stop', [DashboardController::class, 'stopSimulation'])
    ->name('dashboard.simulation.stop');
