<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PDFController;

Route::get('/', function () {
    return view('welcome');
});
Route::get('/generate-pdf', [PDFController::class, 'generatePdf'])->name('pdf.generate');
