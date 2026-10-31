<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\Admin\Users\Index as AdminUsersIndex;
use App\Livewire\Admin\Users\Create as AdminUsersCreate;
use App\Livewire\Admin\Users\Edit as AdminUsersEdit;
use App\Livewire\Cases\Index as CasesIndex;
use App\Http\Controllers\ActiveCompanyController;
use App\Livewire\Admin\Companies\Form as AdminCompaniesForm;
use App\Livewire\Admin\Companies\Index as AdminCompaniesIndex;
use App\Livewire\Cases\Show as CasesShow;
use App\Livewire\Dashboard;

Route::view('/', 'welcome');

Route::get('dashboard', Dashboard::class)
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
        Route::get('/usuarios/crear', AdminUsersCreate::class)->name('usuarios.create');
        Route::get('/usuarios/{user}/editar', AdminUsersEdit::class)->name('usuarios.edit');
        Route::get('/empresas', AdminCompaniesIndex::class)->name('empresas.index');
        Route::get('/empresas/crear', AdminCompaniesForm::class)->name('empresas.create');
        Route::get('/empresas/{company}/editar', AdminCompaniesForm::class)->name('empresas.edit');
    });


Route::get('/casos', CasesIndex::class)
    ->middleware(['auth', 'verified'])
    ->name('casos.index');

Route::post('/empresa-activa', ActiveCompanyController::class)
    ->middleware(['auth', 'verified'])
    ->name('empresa-activa.update');

Route::get('/casos/{caseId}', CasesShow::class)
    ->middleware(['auth', 'verified'])
    ->name('casos.show');

require __DIR__ . '/auth.php';
