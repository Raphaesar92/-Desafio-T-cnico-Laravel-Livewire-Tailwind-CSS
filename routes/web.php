<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DocumentController;

Route::view('/', 'upload');
Route::post('/upload', [DocumentController::class, 'upload'])->name('upload');


