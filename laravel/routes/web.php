<?php

use App\Http\Controllers\DriverController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\VehicleController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/drivers', [DriverController::class, 'index'])->name('drivers.index');                              //GET
    Route::post('/drivers/create', [DriverController::class, 'createDriver'])->name('drivers.create');              //CREATE
    Route::get('/drivers/edit/{driver}', [DriverController::class, 'showEditScreen'])->name('drivers.showEdit');    //GET
    Route::post('/drivers/edit/{driver}', [DriverController::class, 'editDriver'])->name('drivers.edit');           //EDIT
    Route::delete('/drivers/delete/{driver}', [DriverController::class, 'deleteDriver'])->name('drivers.delete');     //DELETE

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/vehicles', [VehicleController::class, 'index'])->name('vehicles.index');
    Route::post('/vehicles/create', [VehicleController::class, 'createVehicle'])->name('vehicles.create');
    Route::get('/vehicles/edit/{vehicle}', [VehicleController::class, 'showEditScreen'])->name('vehicles.showEdit');
    Route::post('/vehicles/edit/{vehicle}', [VehicleController::class, 'editVehicle'])->name('vehicles.edit');
    Route::delete('/vehicles/delete/{vehicle}', [VehicleController::class, 'deleteVehicle'])->name('vehicles.delete');

});

Route::get('test', function () {
    return view('test');
});

require __DIR__.'/auth.php';
