<?php
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\CustomerController;
use App\Http\Controllers\Api\AppointmentController;
use App\Http\Controllers\Api\EmployeeController;
use App\Http\Controllers\Api\ServiceController;
use App\Http\Controllers\Api\PaymentController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\LogoutController;
use App\Http\Controllers\Api\AppVersionController;
//
Route::post('/login', [AuthController::class, 'login']); 

// Dashboard
Route::get('/dashboard', [DashboardController::class, 'index'])->middleware('auth:sanctum', 'active.token');

// Profile
Route::prefix('profile')->middleware('auth:sanctum', 'active.token')->group(function() {
    Route::get('/', [ProfileController::class, 'profile']);
    Route::post('/update', [ProfileController::class, 'updateProfile']);
    Route::post('/password-update', [ProfileController::class, 'updatePassword']);
});

// Customers
Route::middleware('auth:sanctum', 'active.token')->prefix('customers')->group(function () {
    Route::get('/', [CustomerController::class, 'index']);
    Route::post('/', [CustomerController::class, 'store']);
    Route::put('/{customer}', [CustomerController::class, 'update']);
    Route::delete('/{customer}', [CustomerController::class, 'destroy']);
    Route::get('/{customer}', [CustomerController::class, 'show']); 
});
// Hizmet
Route::middleware('auth:sanctum', 'active.token')->prefix('service')->group(function () {
    Route::get('/', [ServiceController::class, 'index']);
    Route::post('/', [ServiceController::class, 'store']);
    Route::put('/{service}', [ServiceController::class, 'update']);
    Route::delete('/{service}', [ServiceController::class, 'destroy']);
    Route::get('/{service}', [ServiceController::class, 'show']);
});
// Employees
Route::middleware('auth:sanctum', 'active.token')->prefix('employees')->group(function () {
    Route::get('/', [EmployeeController::class, 'index']);
    Route::post('/', [EmployeeController::class, 'store']);
    Route::put('/{employee}', [EmployeeController::class, 'update']);
    Route::delete('/{employee}', [EmployeeController::class, 'destroy']);
});

// Appointments
Route::middleware('auth:sanctum', 'active.token')->get('/appointments', [AppointmentController::class, 'index']);
Route::middleware('auth:sanctum', 'active.token')->post('/appointments', [AppointmentController::class, 'store']);
Route::middleware('auth:sanctum', 'active.token')->get('/appointments/customer/{customerId}', [AppointmentController::class, 'showByCustomer']);
Route::middleware('auth:sanctum', 'active.token')->put('/appointments/{id}', [AppointmentController::class, 'update']);

Route::prefix('appointments/{appointment}')->group(function () {
    Route::middleware('auth:sanctum', 'active.token')->get('payments', [PaymentController::class, 'index']);   // Ödemeleri listele
    Route::middleware('auth:sanctum', 'active.token')->post('payments', [PaymentController::class, 'store']);  // Yeni ödeme ekle
});
//Logout
Route::middleware('auth:sanctum', 'active.token')->post('/logout', [LogoutController::class, 'logout']);
//upload app version
Route::post('/app-version/latest', [AppVersionController::class, 'latest']);
Route::post('/app-version', [AppVersionController::class, 'store']); // admin upload için opsiyonel
Route::get('/app-version/latest', [AppVersionController::class, 'latest']);// son sürüm bilgisi
Route::get('/updates/{filename}', function ($filename) {
    $path = public_path('updates/' . $filename);

    if (!file_exists($path)) {
        abort(404);
    }

    return response()->download($path);
});