<?php

use Inertia\Inertia;
use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Application;
use App\Http\Controllers\KidController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PlaceController;
use App\Http\Controllers\DriverController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\VehicleController;
use App\Http\Controllers\Auth\RegisteredUserController;

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
    //USERS
    Route::get('/users', [UserController::class, 'index'])->name('users.index');                              //GET
    Route::get('/users/create', [UserController::class, 'showCreateUserForm'])->name('users.create');
    Route::post('/users/create', [UserController::class, 'createUser'])->name('users.create');              //CREATE
    Route::get('/users/edit/{user}', [UserController::class, 'showEditScreen'])->name('users.showEdit');    //GET
    Route::post('/users/edit/{user}', [UserController::class, 'editUser'])->name('users.edit');           //EDIT
    Route::delete('/users/delete/{user}', [UserController::class, 'deleteUser'])->name('users.delete');     //DELETE

    //DRIVERS
    Route::get('/drivers', [DriverController::class, 'index'])->name('drivers.index');                              //GET
    Route::get('/drivers/create', [DriverController::class, 'showCreateDriverForm'])->name('drivers.create');
    Route::post('/drivers/create', [DriverController::class, 'createDriver'])->name('drivers.create');              //CREATE
    Route::get('/drivers/edit/{driver}', [DriverController::class, 'showEditScreen'])->name('drivers.showEdit');    //GET
    Route::post('/drivers/edit/{driver}', [DriverController::class, 'editDriver'])->name('drivers.edit');           //EDIT
    Route::delete('/drivers/delete/{driver}', [DriverController::class, 'deleteDriver'])->name('drivers.delete');     //DELETE
    
    //PROFILE
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    //VEHICLES
    Route::get('/vehicles', [VehicleController::class, 'index'])->name('vehicles.index');
    Route::get('/vehicles/create', [VehicleController::class, 'showCreateVehicleForm'])->name('vehicles.create');
    Route::post('/vehicles/create', [VehicleController::class, 'createVehicle'])->name('vehicles.create');
    Route::get('/vehicles/edit/{vehicle}', [VehicleController::class, 'showEditScreen'])->name('vehicles.showEdit');
    Route::post('/vehicles/edit/{vehicle}', [VehicleController::class, 'editVehicle'])->name('vehicles.edit');
    Route::delete('/vehicles/delete/{vehicle}', [VehicleController::class, 'deleteVehicle'])->name('vehicles.delete');

    //KIDS
    Route::get('/kids', [KidController::class, 'index'])->name('kids.index');
    Route::get('/kids/create', [KidController::class, 'showCreateKidForm'])->name('kids.create');
    Route::post('/kids/create', [KidController::class, 'createKid'])->name('kids.create');
    Route::get('/kids/edit/{kid}', [KidController::class, 'showEditScreen'])->name('kids.showEdit');
    Route::post('/kids/edit/{kid}', [KidController::class, 'editKid'])->name('kids.edit');
    Route::delete('/kids/delete/{kid}', [KidController::class, 'deleteKid'])->name('kids.delete');

    //PLACES
    Route::get('/places', [PlaceController::class, 'index'])->name('places.index');
    Route::get('/places/create', [PlaceController::class, 'showCreatePlaceForm'])->name('places.create');
    Route::post('/places/create', [PlaceController::class, 'createPlace'])->name('places.create');
    Route::get('/places/edit/{place}', [PlaceController::class, 'showEditScreen'])->name('places.showEdit');
    Route::post('/places/edit/{place}', [PlaceController::class, 'editPlace'])->name('places.edit');
    Route::delete('/places/delete/{place}', [PlaceController::class, 'deletePlace'])->name('places.delete');

});

Route::get('test', function () {
    return view('test');
});

require __DIR__.'/auth.php';
