<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ReceivingController;
use App\Http\Controllers\UnitUnderTestController;
use App\Http\Controllers\JobOrderController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\QuotationController;
use App\Http\Controllers\CustomerController;   // ✅ add this
use App\Models\Job;

// Landing → dashboard
Route::get('/', function () {
    return redirect()->route('dashboard');
});

// Main dashboard (protected)
Route::get('/dashboard', function () {
    // Live counts for the tiles
    $receivingCount = Job::where('status', 'receiving')->count();
    $technicalCount = Job::where('status', 'technical')->count();
    $releasingCount = Job::where('status', 'releasing')->count();
    $billingCount   = Job::where('status', 'billing')->count();
    $arCount        = Job::where('status', 'ar')->count();

    return view('dashboard', compact(
        'receivingCount',
        'technicalCount',
        'releasingCount',
        'billingCount',
        'arCount'
    ));
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {

    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Customers ✅ (from old system)
    Route::resource('customers', CustomerController::class);

    // Receiving
    Route::get('/receiving', [ReceivingController::class, 'index'])->name('receiving.index');
    Route::get('/receiving/create', [ReceivingController::class, 'create'])->name('receiving.create');
    Route::post('/receiving', [ReceivingController::class, 'store'])->name('receiving.store');

    // Units & Jobs
    Route::resource('units', UnitUnderTestController::class);
    Route::resource('jobs', JobOrderController::class);

    // Quotations (Marketing → PO)
    Route::resource('quotations', QuotationController::class);
    Route::post('quotations/{quotation}/approve', [QuotationController::class, 'approve'])
        ->name('quotations.approve');

    // User Accounts
    Route::resource('users', UserController::class)->except(['show']);
});

require __DIR__.'/auth.php';
