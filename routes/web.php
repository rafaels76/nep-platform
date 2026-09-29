<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\Admin\Users\Index as AdminUsersIndex;
use App\Livewire\Admin\Users\Create as AdminUsersCreate;



Route::view('/', 'welcome');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

Route::middleware(['auth', 'verified', 'admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/usuarios', AdminUsersIndex::class)->name('usuarios.index');
    });

Route::middleware(['auth', 'verified', 'admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/usuarios', AdminUsersIndex::class)->name('usuarios.index');
        Route::get('/usuarios/crear', AdminUsersCreate::class)->name('usuarios.create');
    });

require __DIR__ . '/auth.php';
