<?php

use Inertia\Inertia;
use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Application;
use App\Http\Controllers\KidController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PlaceController;
use App\Http\Controllers\RouteController;
use App\Http\Controllers\DriverController;
use App\Http\Controllers\ManagerController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\VehicleController;
use App\Http\Controllers\TechnicianController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\DashboardController;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

// Route::get('/a', function () {
//     return Inertia::render('Dashboard');
// })->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    //DASHBOARD
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard.index');

    //DRIVERS
    Route::get('/drivers', [DriverController::class, 'index'])->name('drivers.index');                              //GET all page
    Route::get('/drivers/create', [DriverController::class, 'showCreateDriverForm'])->name('drivers.create');       //GET creation page
    Route::post('/drivers/create', [DriverController::class, 'createDriver'])->name('drivers.create');              //CREATE action
    Route::get('/drivers/edit/{driver}', [DriverController::class, 'showEditDriverForm'])->name('drivers.showEdit');    //GET edit page
    Route::put('/drivers/edit/{driver}', [DriverController::class, 'editDriver'])->name('drivers.edit');            //EDIT action
    Route::delete('/drivers/delete/{driver}', [DriverController::class, 'deleteDriver'])->name('drivers.delete');   //DELETE action

    //KIDS
    Route::get('/kids', [KidController::class, 'index'])->name('kids.index');
    Route::get('/kids/create', [KidController::class, 'showCreateKidForm'])->name('kids.create');
    Route::post('/kids/create', [KidController::class, 'createKid'])->name('kids.create');
    Route::get('/kids/edit/{kid}', [KidController::class, 'showEditKidForm'])->name('kids.showEdit');
    Route::put('/kids/edit/{kid}', [KidController::class, 'editKid'])->name('kids.edit');
    Route::delete('/kids/delete/{kid}', [KidController::class, 'deleteKid'])->name('kids.delete');

    //ORDERS (MAPS)
    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/create', [OrderController::class, 'showCreateOrderForm'])->name('orders.showCreate');
    Route::post('/orders/create', [OrderController::class, 'createOrder'])->name('orders.create');
    Route::get('/orders/edit/{order}', [OrderController::class, 'showEditOrderForm'])->name('orders.showEditOrder');
    Route::put('/orders/edit/{order}', [OrderController::class, 'editOrder'])->name('orders.edit');
    Route::delete('/orders/delete/{order}', [OrderController::class, 'deleteOrder'])->name('orders.delete');
    
    //MANAGERS (USER MODEL WITH Gestor USER_TYPE)
    Route::get('/managers', [ManagerController::class, 'index'])->name('managers.index');
    Route::get('/managers/create', [ManagerController::class, 'showCreateManagerForm'])->name('managers.create');
    Route::post('/managers/create', [ManagerController::class, 'createManager'])->name('managers.create');
    Route::get('/managers/edit/{user}', [ManagerController::class, 'showEditManagerForm'])->name('managers.showEdit');
    Route::put('/managers/edit/{user}', [ManagerController::class, 'editManager'])->name('managers.edit');
    Route::delete('/managers/delete/{user}', [ManagerController::class, 'deleteManager'])->name('managers.delete');
    
    //PLACES
    Route::get('/places', [PlaceController::class, 'index'])->name('places.index');
    Route::get('/places/create', [PlaceController::class, 'showCreatePlaceForm'])->name('places.create');
    Route::post('/places/create', [PlaceController::class, 'createPlace'])->name('places.create');
    Route::get('/places/edit/{place}', [PlaceController::class, 'showEditPlaceForm'])->name('places.showEdit');
    Route::put('/places/edit/{place}', [PlaceController::class, 'editPlace'])->name('places.edit');
    Route::delete('/places/delete/{place}', [PlaceController::class, 'deletePlace'])->name('places.delete');

    //PROFILE
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    //USERS
    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::get('/users/create', [UserController::class, 'showCreateUserForm'])->name('users.create');
    Route::post('/users/create', [UserController::class, 'createUser'])->name('users.create');
    Route::get('/users/edit/{user}', [UserController::class, 'showEditUserForm'])->name('users.showEdit');
    Route::put('/users/edit/{user}', [UserController::class, 'editUser'])->name('users.edit');
    Route::delete('/users/delete/{user}', [UserController::class, 'deleteUser'])->name('users.delete');

    //TECHNICIANS (USER MODEL WITH Técnico USER_TYPE)
    Route::get('/technicians', [TechnicianController::class, 'index'])->name('technicians.index');
    Route::get('/technicians/create', [TechnicianController::class, 'showCreateTechnicianForm'])->name('technicians.create');
    Route::post('/technicians/create', [TechnicianController::class, 'createTechnician'])->name('technicians.create');
    Route::get('/technicians/edit/{user}', [TechnicianController::class, 'showEditTechnicianForm'])->name('technicians.showEdit');
    Route::put('/technicians/edit/{user}', [TechnicianController::class, 'editTechnician'])->name('technicians.edit');
    Route::delete('/technicians/delete/{user}', [TechnicianController::class, 'deleteTechnician'])->name('technicians.delete');

    //VEHICLES
    Route::get('/vehicles', [VehicleController::class, 'index'])->name('vehicles.index');
    Route::get('/vehicles/create', [VehicleController::class, 'showCreateVehicleForm'])->name('vehicles.create');
    Route::post('/vehicles/create', [VehicleController::class, 'createVehicle'])->name('vehicles.create');
    Route::get('/vehicles/edit/{vehicle}', [VehicleController::class, 'showEditVehicleForm'])->name('vehicles.showEdit');
    Route::put('/vehicles/edit/{vehicle}', [VehicleController::class, 'editVehicle'])->name('vehicles.edit');
    Route::delete('/vehicles/delete/{vehicle}', [VehicleController::class, 'deleteVehicle'])->name('vehicles.delete');

});

require __DIR__.'/auth.php';
