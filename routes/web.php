<?php

use App\Http\Controllers\Admin\AvailabilityController;
use App\Http\Controllers\Admin\DoctorBreakController;
use App\Http\Controllers\Admin\DoctorController as AdminDoctorController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\DoctorController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return request()->user()
        ? redirect()->route('dashboard')
        : view('welcome');
});

Route::get('/dashboard', function () {
    $user = request()->user();

    return $user->isAdmin()
        ? redirect()->route('admin.doctors.index')
        : redirect()->route('doctors.index');
})->middleware('auth')->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth', 'role:patient'])->group(function () {
    Route::get('/doctors', [DoctorController::class, 'index'])->name('doctors.index');
    Route::get('/doctors/{doctor}/slots', [DoctorController::class, 'slots'])->name('doctors.slots');

    Route::get('/appointments', [AppointmentController::class, 'index'])->name('appointments.index');
    Route::post('/appointments', [AppointmentController::class, 'store'])->name('appointments.store');
    Route::delete('/appointments/{appointment}', [AppointmentController::class, 'destroy'])->name('appointments.destroy');
});

Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/doctors', [AdminDoctorController::class, 'index'])->name('doctors.index');
    Route::post('/doctors', [AdminDoctorController::class, 'store'])->name('doctors.store');
    Route::delete('/doctors/{doctor}', [AdminDoctorController::class, 'destroy'])->name('doctors.destroy');

    Route::get('/availability', [AvailabilityController::class, 'index'])->name('availability.index');
    Route::post('/doctors/{doctor}/availability', [AvailabilityController::class, 'store'])->name('availability.store');
    Route::delete('/availability/{availability}', [AvailabilityController::class, 'destroy'])->name('availability.destroy');

    Route::post('/doctors/{doctor}/breaks', [DoctorBreakController::class, 'store'])->name('breaks.store');
    Route::delete('/breaks/{doctorBreak}', [DoctorBreakController::class, 'destroy'])->name('breaks.destroy');
});

require __DIR__.'/auth.php';
