<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\ReceivingController;
use App\Http\Controllers\UnitUnderTestController;
// use App\Http\Controllers\JobOrderController;   // ❌ not needed anymore
use App\Http\Controllers\UserController;
use App\Http\Controllers\QuotationController;
use App\Http\Controllers\CustomerController;
use App\Models\WorkOrder;
use App\Models\User; 

// Landing → dashboard
Route::get('/', function () {
    return redirect()->route('dashboard');
});

// Dashboard counts now use WorkOrder
Route::get('/dashboard', function () {
    $receivingCount = WorkOrder::where('status', 'receiving')->count();
    $technicalCount = WorkOrder::where('status', 'technical')->count();
    $releasingCount = WorkOrder::where('status', 'releasing')->count();
    $billingCount   = WorkOrder::where('status', 'billing')->count();
    $arCount        = WorkOrder::where('status', 'ar')->count();

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

    // Customers
    Route::resource('customers', CustomerController::class);

    // Receiving (WorkOrder header)
    Route::get('/receiving', [ReceivingController::class, 'index'])->name('receiving.index');
    Route::get('/receiving/create', [ReceivingController::class, 'create'])->name('receiving.create');
    Route::post('/receiving', [ReceivingController::class, 'store'])->name('receiving.store');
    Route::get('/receiving/{workOrder}/edit', [ReceivingController::class, 'edit'])->name('receiving.edit');
    Route::put('/receiving/{workOrder}', [ReceivingController::class, 'update'])->name('receiving.update');
    Route::post('/receiving/{workOrder}/post', [ReceivingController::class, 'post'])->name('receiving.post');
    Route::post('/receiving/{workOrder}/submit-approval', [ReceivingController::class, 'submitForApproval'])->name('receiving.submitApproval');
    Route::post('/receiving/{workOrder}/assign-approver-batch', [ReceivingController::class, 'assignApproverBatch'])->name('receiving.assignApproverBatch');
    Route::post('/receiving/{workOrder}/assign-personnel-batch', [ReceivingController::class, 'assignPersonnelBatch'])->name('receiving.assignPersonnelBatch');

    // ⬇️ Keep these ONLY if your ReceivingController already has edit/update/destroy for WorkOrder
    // and make sure the method signatures use WorkOrder $workOrder.
    /*
    Route::get('/receiving/{workOrder}/edit', [ReceivingController::class, 'edit'])->name('receiving.edit');
    Route::put('/receiving/{workOrder}', [ReceivingController::class, 'update'])->name('receiving.update');
    Route::delete('/receiving/{workOrder}', [ReceivingController::class, 'destroy'])->name('receiving.destroy');
    */

    // Technical (Unit Under Test lines)
    Route::resource('technical', UnitUnderTestController::class);
    Route::get('technical/{unit}/download/{file}', [UnitUnderTestController::class, 'download'])
        ->name('technical.download')
        ->whereIn('file', ['certificate', 'datasheet', 'tsrf']);

    // ❌ old jobs routes removed – WorkOrder replaces them
    // Route::resource('jobs', JobOrderController::class);

    // Quotations (Marketing → PO)
    Route::resource('quotations', QuotationController::class);
    Route::post('quotations/{quotation}/approve', [QuotationController::class, 'approve'])
        ->name('quotations.approve');
    Route::post('quotations/{quotation}/proceed', [QuotationController::class, 'proceedToReceiving'])
        ->name('quotations.proceed');

    // User Accounts
    Route::resource('users', UserController::class)->except(['show']);
});

require __DIR__.'/auth.php';
