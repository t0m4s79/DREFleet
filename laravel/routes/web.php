<?php

use Inertia\Inertia;
use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Application;
use App\Http\Controllers\KidController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PlaceController;
use App\Http\Controllers\DriverController;
use App\Http\Controllers\ManagerController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\VehicleController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\OrderStopController;
use App\Http\Controllers\OrderRouteController;
use App\Http\Controllers\TechnicianController;
use App\Http\Controllers\VehicleDocumentController;
use App\Http\Controllers\VehicleAccessoryController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\NotificationController;

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
    Route::get('/drivers/create', [DriverController::class, 'showCreateDriverForm'])->name('drivers.showCreate');       //GET creation page
    Route::post('/drivers/create', [DriverController::class, 'createDriver'])->name('drivers.create');              //CREATE action
    Route::get('/drivers/edit/{driver}', [DriverController::class, 'showEditDriverForm'])->name('drivers.showEdit');    //GET edit page
    Route::put('/drivers/edit/{driver}', [DriverController::class, 'editDriver'])->name('drivers.edit');            //EDIT action
    Route::delete('/drivers/delete/{driver}', [DriverController::class, 'deleteDriver'])->name('drivers.delete');   //DELETE action

    //KIDS
    Route::get('/kids', [KidController::class, 'index'])->name('kids.index');
    Route::get('/kids/create', [KidController::class, 'showCreateKidForm'])->name('kids.create');
    Route::post('/kids/create', [KidController::class, 'createKid'])->name('kids.showCreate');
    Route::get('/kids/edit/{kid}', [KidController::class, 'showEditKidForm'])->name('kids.showEdit');
    Route::put('/kids/edit/{kid}', [KidController::class, 'editKid'])->name('kids.edit');
    Route::delete('/kids/delete/{kid}', [KidController::class, 'deleteKid'])->name('kids.delete');
    
    //MANAGERS (USER MODEL WITH Gestor USER_TYPE)
    Route::get('/managers', [ManagerController::class, 'index'])->name('managers.index');
    Route::get('/managers/create', [ManagerController::class, 'showCreateManagerForm'])->name('managers.showCreate');
    Route::post('/managers/create', [ManagerController::class, 'createManager'])->name('managers.create');
    Route::get('/managers/edit/{user}', [ManagerController::class, 'showEditManagerForm'])->name('managers.showEdit');
    Route::put('/managers/edit/{user}', [ManagerController::class, 'editManager'])->name('managers.edit');
    Route::delete('/managers/delete/{user}', [ManagerController::class, 'deleteManager'])->name('managers.delete');
    Route::get('/managers/showApproved/{user}', [ManagerController::class, 'showManagerApprovedOrders'])->name('managers.showApproved'); 

    //NOTIFICATIONS
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::patch('/notifications/read/{notification}', [NotificationController::class, 'markAsRead'])->name('notifications.markAsRead');
    Route::delete('/notifications/delete/{notification}', [NotificationController::class, 'deleteNotification'])->name('notifications.delete');

    //ORDERS
    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/create', [OrderController::class, 'showCreateOrderForm'])->name('orders.showCreate');
    Route::post('/orders/create', [OrderController::class, 'createOrder'])->name('orders.create');
    Route::get('/orders/edit/{order}', [OrderController::class, 'showEditOrderForm'])->name('orders.showEditOrder');
    Route::put('/orders/edit/{order}', [OrderController::class, 'editOrder'])->name('orders.edit');
    Route::delete('/orders/delete/{order}', [OrderController::class, 'deleteOrder'])->name('orders.delete');
    Route::patch('/orders/approve/{order}',  [OrderController::class, 'approveOrder'])->name('orders.approve');
    Route::patch('/orders/removeApproval/{order}',  [OrderController::class, 'removeOrderApproval'])->name('orders.removeApproval');

    //ORDER ROUTES
    Route::get('/orderRoutes', [OrderRouteController::class, 'index'])->name('orderRoutes.index');
    Route::get('/orderRoutes/create', [OrderRouteController::class, 'showCreateOrderRouteForm'])->name('orderRoutes.showCreate');
    Route::post('/orderRoutes/create', [OrderRouteController::class, 'createOrderRoute'])->name('orderRoutes.create');
    Route::get('/orderRoutes/edit/{orderRoute}', [OrderRouteController::class, 'showEditOrderRouteForm'])->name('orderRoutes.showEditOrder');
    Route::put('/orderRoutes/edit/{orderRoute}', [OrderRouteController::class, 'editOrderRoute'])->name('orderRoutes.edit');
    Route::delete('/orderRoutes/delete/{orderRoute}', [OrderRouteController::class, 'deleteOrderRoute'])->name('orderRoutes.delete');

    //ORDER STOPS
    Route::post('/orderStops/create', [OrderStopController::class, 'createOrderStop'])->name('orderStops.create');
    Route::put('/orderStops/edit/{orderStop}', [OrderStopController::class, 'editOrderStop'])->name('orderStops.edit');
    Route::delete('/orderStops/delete/{orderStop}', [OrderStopController::class, 'deleteOrderStop'])->name('orderStops.delete');
    Route::patch('/orderStops/stopReached/{orderStop}',  [OrderStopController::class, 'orderStopReached'])->name('orderStops.stopReached');
    
    //PLACES
    Route::get('/places', [PlaceController::class, 'index'])->name('places.index');
    Route::get('/places/create', [PlaceController::class, 'showCreatePlaceForm'])->name('places.showCreate');
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
    Route::get('/users/create', [UserController::class, 'showCreateUserForm'])->name('users.showCreate');
    Route::post('/users/create', [UserController::class, 'createUser'])->name('users.create');
    Route::get('/users/edit/{user}', [UserController::class, 'showEditUserForm'])->name('users.showEdit');
    Route::put('/users/edit/{user}', [UserController::class, 'editUser'])->name('users.edit');
    Route::delete('/users/delete/{user}', [UserController::class, 'deleteUser'])->name('users.delete');

    //TECHNICIANS (USER MODEL WITH Técnico USER_TYPE)
    Route::get('/technicians', [TechnicianController::class, 'index'])->name('technicians.index');
    Route::get('/technicians/create', [TechnicianController::class, 'showCreateTechnicianForm'])->name('technicians.showCreate');
    Route::post('/technicians/create', [TechnicianController::class, 'createTechnician'])->name('technicians.create');
    Route::get('/technicians/edit/{user}', [TechnicianController::class, 'showEditTechnicianForm'])->name('technicians.showEdit');
    Route::put('/technicians/edit/{user}', [TechnicianController::class, 'editTechnician'])->name('technicians.edit');
    Route::delete('/technicians/delete/{user}', [TechnicianController::class, 'deleteTechnician'])->name('technicians.delete');

    //VEHICLES
    Route::get('/vehicles', [VehicleController::class, 'index'])->name('vehicles.index');
    Route::get('/vehicles/create', [VehicleController::class, 'showCreateVehicleForm'])->name('vehicles.showCreate');
    Route::post('/vehicles/create', [VehicleController::class, 'createVehicle'])->name('vehicles.create');
    Route::get('/vehicles/edit/{vehicle}', [VehicleController::class, 'showEditVehicleForm'])->name('vehicles.showEdit');
    Route::put('/vehicles/edit/{vehicle}', [VehicleController::class, 'editVehicle'])->name('vehicles.edit');
    Route::delete('/vehicles/delete/{vehicle}', [VehicleController::class, 'deleteVehicle'])->name('vehicles.delete');

    //VEHICLES ACCESSORIES
    Route::get('/vehicleAccessories', [VehicleAccessoryController::class, 'index'])->name('vehicleAccessories.index');
    Route::get('/vehicleAccessories/create', [VehicleAccessoryController::class, 'showCreateVehicleAccessoryForm'])->name('vehicleAccessories.showCreate');
    Route::post('/vehicleAccessories/create', [VehicleAccessoryController::class, 'createVehicleAccessory'])->name('vehicleAccessories.create');
    Route::get('/vehicleAccessories/edit/{vehicleAccessory}', [VehicleAccessoryController::class, 'showEditVehicleAccessoryForm'])->name('vehicleAccessories.showEdit');
    Route::put('/vehicleAccessories/edit/{vehicleAccessory}', [VehicleAccessoryController::class, 'editVehicleAccessory'])->name('vehicleAccessories.edit');
    Route::delete('/vehicleAccessories/delete/{vehicleAccessory}', [VehicleAccessoryController::class, 'deleteVehicleAccessory'])->name('vehicleAccessories.delete');
    
    //VEHICLES DOCUMENTS
    Route::get('/vehicleDocuments', [VehicleDocumentController::class, 'index'])->name('vehicleDocuments.index');
    Route::get('/vehicleDocuments/create', [VehicleDocumentController::class, 'showCreateVehicleDocumentForm'])->name('vehicleDocuments.showCreate');
    Route::post('/vehicleDocuments/create', [VehicleDocumentController::class, 'createVehicleDocument'])->name('vehicleDocuments.create');
    Route::get('/vehicleDocuments/edit/{vehicleDocument}', [VehicleDocumentController::class, 'showEditVehicleDocumentForm'])->name('vehicleDocuments.showEdit');
    Route::put('/vehicleDocuments/edit/{vehicleDocument}', [VehicleDocumentController::class, 'editVehicleDocument'])->name('vehicleDocuments.edit');
    Route::delete('/vehicleDocuments/delete/{vehicleDocument}', [VehicleDocumentController::class, 'deleteVehicleDocument'])->name('vehicleDocuments.delete');
});

require __DIR__.'/auth.php';
