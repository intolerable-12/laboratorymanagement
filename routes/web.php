<?php



use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Coordinator\Chemical\ChemicalBarcodePrintController;
use App\Http\Controllers\Coordinator\Chemical\ChemicalCategoryController;
use App\Http\Controllers\Coordinator\Chemical\ChemicalController;
use App\Http\Controllers\Coordinator\EquipmentCategoryController;
use App\Http\Controllers\Coordinator\EquipmentController;
use App\Http\Controllers\Coordinator\EquipmentBarcodePrintController;
use App\Http\Controllers\Coordinator\LaboratoryController;
use App\Http\Controllers\Coordinator\UserManagementController;

Route::get('/', [LoginController::class, 'create'])->name('login');

Route::post('/login', [LoginController::class, 'store'])
    ->name('login.store');

Route::post('/logout', [LoginController::class, 'destroy'])
    ->name('logout');


Route::middleware('auth')
    ->prefix('coordinator')
    ->name('coordinator.')
    ->group(function () {

        Route::get('/dashboard', function () {
            return view('users.coordinator.dashboard');
        })->name('dashboard');

        Route::resource('users', UserManagementController::class);

        Route::prefix('equipment')
            ->name('equipment.')
            ->group(function () {
                Route::get('/', [EquipmentController::class, 'index'])->name('index');
                Route::get('/create', [EquipmentController::class, 'create'])->name('create');
                Route::post('/', [EquipmentController::class, 'store'])->name('store');
                Route::get('/{equipment}', [EquipmentController::class, 'show'])->name('show');
                Route::get('/{equipment}/barcode-print', EquipmentBarcodePrintController::class)->name('barcode-print');
                Route::get('/{equipment}/edit', [EquipmentController::class, 'edit'])->name('edit');
                Route::put('/{equipment}', [EquipmentController::class, 'update'])->name('update');
                Route::delete('/{equipment}', [EquipmentController::class, 'destroy'])->name('destroy');
            });

        Route::prefix('equipment-categories')
            ->name('equipment.categories.')
            ->group(function () {
                Route::get('/', [EquipmentCategoryController::class, 'index'])->name('index');
                Route::get('/create', [EquipmentCategoryController::class, 'create'])->name('create');
                Route::post('/', [EquipmentCategoryController::class, 'store'])->name('store');
                Route::get('/{equipmentCategory}', [EquipmentCategoryController::class, 'show'])->name('show');
                Route::get('/{equipmentCategory}/edit', [EquipmentCategoryController::class, 'edit'])->name('edit');
                Route::put('/{equipmentCategory}', [EquipmentCategoryController::class, 'update'])->name('update');
                Route::delete('/{equipmentCategory}', [EquipmentCategoryController::class, 'destroy'])->name('destroy');
            });

        Route::prefix('laboratories')
            ->name('laboratories.')
            ->group(function () {
                Route::get('/', [LaboratoryController::class, 'index'])->name('index');
                Route::get('/create', [LaboratoryController::class, 'create'])->name('create');
                Route::post('/', [LaboratoryController::class, 'store'])->name('store');
                Route::get('/{laboratory}', [LaboratoryController::class, 'show'])->name('show');
                Route::get('/{laboratory}/edit', [LaboratoryController::class, 'edit'])->name('edit');
                Route::put('/{laboratory}', [LaboratoryController::class, 'update'])->name('update');
                Route::delete('/{laboratory}', [LaboratoryController::class, 'destroy'])->name('destroy');
            });

        Route::prefix('chemicals')
            ->name('chemicals.')
            ->group(function () {
                Route::get('/', [ChemicalController::class, 'index'])->name('index');
                Route::get('/create', [ChemicalController::class, 'create'])->name('create');
                Route::post('/', [ChemicalController::class, 'store'])->name('store');
                Route::get('/{chemical}', [ChemicalController::class, 'show'])->name('show');
                Route::get('/{chemical}/barcode-print', ChemicalBarcodePrintController::class)->name('barcode-print');
                Route::get('/{chemical}/edit', [ChemicalController::class, 'edit'])->name('edit');
                Route::put('/{chemical}', [ChemicalController::class, 'update'])->name('update');
                Route::delete('/{chemical}', [ChemicalController::class, 'destroy'])->name('destroy');
            });

        Route::prefix('chemical-categories')
            ->name('chemical.categories.')
            ->group(function () {
                Route::get('/', [ChemicalCategoryController::class, 'index'])->name('index');
                Route::get('/create', [ChemicalCategoryController::class, 'create'])->name('create');
                Route::post('/', [ChemicalCategoryController::class, 'store'])->name('store');
                Route::get('/{chemicalCategory}', [ChemicalCategoryController::class, 'show'])->name('show');
                Route::get('/{chemicalCategory}/edit', [ChemicalCategoryController::class, 'edit'])->name('edit');
                Route::put('/{chemicalCategory}', [ChemicalCategoryController::class, 'update'])->name('update');
                Route::delete('/{chemicalCategory}', [ChemicalCategoryController::class, 'destroy'])->name('destroy');
            });

    });


Route::middleware('auth')
    ->prefix('student')
    ->name('student.')
    ->group(function () {
        Route::get('/dashboard', function () {
            return view('users.student.dashboard');
        })->name('dashboard');

        Route::get('/my-account', function () {
            return view('users.student.myaccount');
        })->name('myaccount');
    });

Route::middleware('auth')
    ->prefix('facilitator')
    ->name('facilitator.')
    ->group(function () {
        Route::get('/dashboard', function () {
            return view('users.facilitator.dashboard');
        })->name('dashboard');

        Route::get('/my-account', function () {
            return view('users.facilitator.myaccount');
        })->name('myaccount');
    });

Route::middleware('auth')
    ->prefix('instructor')
    ->name('instructor.')
    ->group(function () {
        Route::get('/dashboard', function () {
            return view('users.instructor.dashboard');
        })->name('dashboard');

        Route::get('/my-account', function () {
            return view('users.instructor.myaccount');
        })->name('myaccount');
    });