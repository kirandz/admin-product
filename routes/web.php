<?php

use App\Http\Controllers\InvoiceController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    // return view('welcome');
    return redirect()->route('filament.admin.pages.dashboard');
});

Route::get('/invoices/{sale}', [InvoiceController::class, 'printInvoice'])->name('print.invoice');
