<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PDFController;
use App\Http\Controllers\GmailController;


Route::get('/', function () {
    return view('welcome');
});
Route::get('/generate-pdf', [PDFController::class, 'generatePdf'])->name('pdf.generate');

// Gmail API RealTime Auth ROUTES
Route::get('/google/auth', [GmailController::class, 'googleAuth'])->name('google.auth');
Route::get('/google/callback', [GmailController::class, 'googleCallback'])->name('google.callback');
