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
use App\Http\Controllers\KidEmailController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\OrderStopController;
use App\Http\Controllers\OrderRouteController;
use App\Http\Controllers\TechnicianController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\KidPhoneNumberController;
use App\Http\Controllers\VehicleDocumentController;
use App\Http\Controllers\VehicleAccessoryController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\OrderOccurrenceController;
use App\Http\Controllers\VehicleKilometrageReportController;
use App\Http\Controllers\VehicleRefuelRequestController;

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
    Route::get('/users/drivers', [DriverController::class, 'index'])->name('drivers.index');                                  //GET all page
    Route::get('/users/drivers/create', [DriverController::class, 'showCreateDriverForm'])->name('drivers.showCreate');       //GET creation page
    Route::post('/users/drivers/create', [DriverController::class, 'createDriver'])->name('drivers.create');                  //CREATE action
    Route::get('/users/drivers/edit/{driver}', [DriverController::class, 'showEditDriverForm'])->name('drivers.showEdit');    //GET edit page
    Route::put('/users/drivers/edit/{driver}', [DriverController::class, 'editDriver'])->name('drivers.edit');                //EDIT action
    Route::delete('/users/drivers/delete/{driver}', [DriverController::class, 'deleteDriver'])->name('drivers.delete');       //DELETE action

    //KIDS
    Route::get('/kids', [KidController::class, 'index'])->name('kids.index');
    Route::get('/kids/create', [KidController::class, 'showCreateKidForm'])->name('kids.showCreate');
    Route::post('/kids/create', [KidController::class, 'createKid'])->name('kids.create');
    Route::get('/kids/edit/{kid}', [KidController::class, 'showEditKidForm'])->name('kids.showEdit');
    Route::put('/kids/edit/{kid}', [KidController::class, 'editKid'])->name('kids.edit');
    Route::delete('/kids/delete/{kid}', [KidController::class, 'deleteKid'])->name('kids.delete');
    Route::get('/kids/contacts/{kid}', [KidController::class, 'showKidContacts'])->name('kids.contacts');

    //KID EMAILS
    Route::get('/kids/emails/create', [KidEmailController::class, 'showCreateKidEmailForm'])->name('kidEmails.showCreate');
    Route::post('/kids/emails/create', [KidEmailController::class, 'createKidEmail'])->name('kidEmails.create');
    Route::get('/kids/emails/edit/{kidEmail}', [KidEmailController::class, 'showEditKidEmailForm'])->name('kidEmails.showEdit');
    Route::put('/kids/emails/edit/{kidEmail}', [KidEmailController::class, 'editKidEmail'])->name('kidEmails.edit');
    Route::delete('/kids/emails/delete/{kidEmail}', [KidEmailController::class, 'deleteKidEmail'])->name('kidEmails.delete');

    //KID PHONE NUMBERS
    Route::get('/kids/phoneNumbers/create', [KidPhoneNumberController::class, 'showCreateKidPhoneNumberForm'])->name('kidPhoneNumbers.showCreate');
    Route::post('/kids/phoneNumbers/create', [KidPhoneNumberController::class, 'createKidPhoneNumber'])->name('kidPhoneNumbers.create');
    Route::get('/kids/phoneNumbers/edit/{kidPhoneNumber}', [KidPhoneNumberController::class, 'showEditKidPhoneNumberForm'])->name('kidPhoneNumbers.showEdit');
    Route::put('/kids/phoneNumbers/edit/{kidPhoneNumber}', [KidPhoneNumberController::class, 'editKidPhoneNumber'])->name('kidPhoneNumbers.edit');
    Route::delete('/kids/phoneNumbers/delete/{kidPhoneNumber}', [KidPhoneNumberController::class, 'deleteKidPhoneNumber'])->name('kidPhoneNumbers.delete');
    
    //MANAGERS (USER MODEL WITH Gestor USER_TYPE)
    Route::get('/users/managers', [ManagerController::class, 'index'])->name('managers.index');
    Route::get('/users/managers/create', [ManagerController::class, 'showCreateManagerForm'])->name('managers.showCreate');
    Route::post('/users/managers/create', [ManagerController::class, 'createManager'])->name('managers.create');
    Route::get('/users/managers/edit/{user}', [ManagerController::class, 'showEditManagerForm'])->name('managers.showEdit');
    Route::put('/users/managers/edit/{user}', [ManagerController::class, 'editManager'])->name('managers.edit');
    Route::delete('/users/managers/delete/{user}', [ManagerController::class, 'deleteManager'])->name('managers.delete');
    Route::get('/users/managers/approved/{user}', [ManagerController::class, 'showManagerApprovedOrders'])->name('managers.approved'); 

    //NOTIFICATIONS
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::patch('/notifications/read/{notification}', [NotificationController::class, 'markAsRead'])->name('notifications.markAsRead');
    Route::delete('/notifications/delete/{notification}', [NotificationController::class, 'deleteNotification'])->name('notifications.delete');
    Route::get('/notifications/unread-count', [NotificationController::class, 'getUnreadCount'])->name('notifications.unreadCount');

    //ORDERS
    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/create', [OrderController::class, 'showCreateOrderForm'])->name('orders.showCreate');
    Route::post('/orders/create', [OrderController::class, 'createOrder'])->name('orders.create');
    Route::get('/orders/edit/{order}', [OrderController::class, 'showEditOrderForm'])->name('orders.showEdit');
    Route::put('/orders/edit/{order}', [OrderController::class, 'editOrder'])->name('orders.edit');
    Route::delete('/orders/delete/{order}', [OrderController::class, 'deleteOrder'])->name('orders.delete');
    Route::patch('/orders/approve/{order}',  [OrderController::class, 'approveOrder'])->name('orders.approve');
    Route::patch('/orders/removeApproval/{order}',  [OrderController::class, 'removeOrderApproval'])->name('orders.unapprove');
    Route::patch('/orders/started/{order}',  [OrderController::class, 'orderStarted'])->name('orders.start');
    Route::patch('/orders/ended/{order}',  [OrderController::class, 'orderEnded'])->name('orders.end');
    Route::get('/orders/orderOccurrences/{order}', [OrderController::class, 'showOrderOccurrences'])->name('orders.occurrences');
    Route::get('/orders/orderStops/{order}', [OrderController::class, 'showOrderStops'])->name('orders.stops');

    //ORDER OCCURRENCES
    Route::get('/orders/occurrences', [OrderOccurrenceController::class, 'index'])->name('orderOccurrences.index');
    Route::get('/orders/occurrences/create', [OrderOccurrenceController::class, 'showCreateOrderOccurrenceForm'])->name('orderOccurrences.showCreate');
    Route::post('/orders/occurrences/create', [OrderOccurrenceController::class, 'createOrderOccurrence'])->name('orderOccurrences.create');
    Route::get('/orders/occurrences/edit/{orderOccurrence}', [OrderOccurrenceController::class, 'showEditOrderOccurrenceForm'])->name('orderOccurrences.showEdit');
    Route::put('/orders/occurrences/edit/{orderOccurrence}', [OrderOccurrenceController::class, 'editOrderOccurrence'])->name('orderOccurrences.edit');
    Route::delete('/orders/occurrences/delete/{orderOccurrence}', [OrderOccurrenceController::class, 'deleteOrderOccurrence'])->name('orderOccurrences.delete');

    //ORDER ROUTES
    Route::get('/orders/routes', [OrderRouteController::class, 'index'])->name('orderRoutes.index');
    Route::get('/orders/routes/create', [OrderRouteController::class, 'showCreateOrderRouteForm'])->name('orderRoutes.showCreate');
    Route::post('/orders/routes/create', [OrderRouteController::class, 'createOrderRoute'])->name('orderRoutes.create');
    Route::get('/orders/routes/edit/{orderRoute}', [OrderRouteController::class, 'showEditOrderRouteForm'])->name('orderRoutes.showEdit');
    Route::put('/orders/routes/edit/{orderRoute}', [OrderRouteController::class, 'editOrderRoute'])->name('orderRoutes.edit');
    Route::delete('/orders/routes/delete/{orderRoute}', [OrderRouteController::class, 'deleteOrderRoute'])->name('orderRoutes.delete');

    //ORDER STOPS (only the tests use this routes)
    Route::post('/orders/stops/create', [OrderStopController::class, 'createOrderStop'])->name('orderStops.create');
    Route::put('/orders/stops/edit/{orderStop}', [OrderStopController::class, 'editOrderStop'])->name('orderStops.edit');
    Route::delete('/orders/stops/delete/{orderStop}', [OrderStopController::class, 'deleteOrderStop'])->name('orderStops.delete');
    Route::patch('/orders/stops/stopReached/{orderStop}',  [OrderStopController::class, 'orderStopReached'])->name('orderStops.stopReached');
    
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
    Route::get('/users/technicians', [TechnicianController::class, 'index'])->name('technicians.index');
    Route::get('/users/technicians/create', [TechnicianController::class, 'showCreateTechnicianForm'])->name('technicians.showCreate');
    Route::post('/users/technicians/create', [TechnicianController::class, 'createTechnician'])->name('technicians.create');
    Route::get('/users/technicians/edit/{user}', [TechnicianController::class, 'showEditTechnicianForm'])->name('technicians.showEdit');
    Route::put('/users/technicians/edit/{user}', [TechnicianController::class, 'editTechnician'])->name('technicians.edit');
    Route::delete('/users/technicians/delete/{user}', [TechnicianController::class, 'deleteTechnician'])->name('technicians.delete');

    //VEHICLES
    Route::get('/vehicles', [VehicleController::class, 'index'])->name('vehicles.index');
    Route::get('/vehicles/create', [VehicleController::class, 'showCreateVehicleForm'])->name('vehicles.showCreate');
    Route::post('/vehicles/create', [VehicleController::class, 'createVehicle'])->name('vehicles.create');
    Route::get('/vehicles/edit/{vehicle}', [VehicleController::class, 'showEditVehicleForm'])->name('vehicles.showEdit');
    Route::put('/vehicles/edit/{vehicle}', [VehicleController::class, 'editVehicle'])->name('vehicles.edit');
    Route::delete('/vehicles/delete/{vehicle}', [VehicleController::class, 'deleteVehicle'])->name('vehicles.delete');
    Route::get('/vehicles/documentsAndAccessories/{vehicle}', [VehicleController::class, 'showVehicleAccessoriesAndDocuments'])->name('vehicles.documentsAndAccessories');
    Route::get('/vehicles/kilometrageReports/{vehicle}', [VehicleController::class, 'showVehicleKilometrageReports'])->name('vehicles.kilometrageReports');
    Route::get('/vehicles/refuelRequests/{vehicle}', [VehicleController::class, 'showVehicleRefuelRequests'])->name('vehicles.refuelRequests');

    //VEHICLES ACCESSORIES
    Route::get('/vehicle/accessories', [VehicleAccessoryController::class, 'index'])->name('vehicleAccessories.index');
    Route::get('/vehicle/accessories/create', [VehicleAccessoryController::class, 'showCreateVehicleAccessoryForm'])->name('vehicleAccessories.showCreate');
    Route::post('/vehicle/accessories/create', [VehicleAccessoryController::class, 'createVehicleAccessory'])->name('vehicleAccessories.create');
    Route::get('/vehicle/accessories/edit/{vehicleAccessory}', [VehicleAccessoryController::class, 'showEditVehicleAccessoryForm'])->name('vehicleAccessories.showEdit');
    Route::put('/vehicle/accessories/edit/{vehicleAccessory}', [VehicleAccessoryController::class, 'editVehicleAccessory'])->name('vehicleAccessories.edit');
    Route::delete('/vehicle/accessories/delete/{vehicleAccessory}', [VehicleAccessoryController::class, 'deleteVehicleAccessory'])->name('vehicleAccessories.delete');
    
    //VEHICLES DOCUMENTS
    Route::get('/vehicle/documents', [VehicleDocumentController::class, 'index'])->name('vehicleDocuments.index');
    Route::get('/vehicle/documents/create', [VehicleDocumentController::class, 'showCreateVehicleDocumentForm'])->name('vehicleDocuments.showCreate');
    Route::post('/vehicle/documents/create', [VehicleDocumentController::class, 'createVehicleDocument'])->name('vehicleDocuments.create');
    Route::get('/vehicle/documents/edit/{vehicleDocument}', [VehicleDocumentController::class, 'showEditVehicleDocumentForm'])->name('vehicleDocuments.showEdit');
    Route::put('/vehicle/documents/edit/{vehicleDocument}', [VehicleDocumentController::class, 'editVehicleDocument'])->name('vehicleDocuments.edit');
    Route::delete('/vehicle/documents/delete/{vehicleDocument}', [VehicleDocumentController::class, 'deleteVehicleDocument'])->name('vehicleDocuments.delete');

    //VEHICLES KILOMETRAGE REPORTS
    Route::get('/vehicle/kilometrageReports/create', [VehicleKilometrageReportController::class, 'showCreateVehicleKilometrageReportForm'])->name('vehicleKilometrageReports.showCreate');
    Route::post('/vehicle/kilometrageReports/create', [VehicleKilometrageReportController::class, 'createVehicleKilometrageReport'])->name('vehicleKilometrageReports.create');
    Route::get('/vehicle/kilometrageReports/edit/{vehicleKilometrageReport}', [VehicleKilometrageReportController::class, 'showEditVehicleKilometrageReportForm'])->name('vehicleKilometrageReports.showEdit');
    Route::put('/vehicle/kilometrageReports/edit/{vehicleKilometrageReport}', [VehicleKilometrageReportController::class, 'editVehicleKilometrageReport'])->name('vehicleKilometrageReports.edit');
    Route::delete('/vehicle/kilometrageReports/delete/{vehicleKilometrageReport}', [VehicleKilometrageReportController::class, 'deleteVehicleKilometrageReport'])->name('vehicleKilometrageReports.delete');

    //VEHICLES REFUEL REQUESTS
    Route::get('/vehicle/refuelRequests/create', [VehicleRefuelRequestController::class, 'showCreateVehicleRefuelRequestForm'])->name('vehicleRefuelRequests.showCreate');
    Route::post('/vehicle/refuelRequests/create', [VehicleRefuelRequestController::class, 'createVehicleRefuelRequest'])->name('vehicleRefuelRequests.create');
    Route::get('/vehicle/refuelRequests/edit/{vehicleRefuelRequest}', [VehicleRefuelRequestController::class, 'showEditVehicleRefuelRequestForm'])->name('vehicleRefuelRequests.showEdit');
    Route::put('/vehicle/refuelRequests/edit/{vehicleRefuelRequest}', [VehicleRefuelRequestController::class, 'editVehicleRefuelRequest'])->name('vehicleRefuelRequests.edit');
    Route::delete('/vehicle/refuelRequests/delete/{vehicleRefuelRequest}', [VehicleRefuelRequestController::class, 'deleteVehicleRefuelRequest'])->name('vehicleRefuelRequests.delete');
});

require __DIR__.'/auth.php';
