<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\MedicalDocumentController;


// unauthenticated routes
Route::post('/login', [AuthController::class, "login"]);
Route::post('/register', [AuthController::class, "register"]);
Route::get('/error', [AuthController::class, "displayError"])->name("login");

// authenticated routes
Route::group(["prefix" => "v1", "middleware" => "auth:api"], function (){

    Route::post('/upload-documents', [MedicalDocumentController::class, 'addDocument']);
    Route::get('/get-documents', [MedicalDocumentController::class, 'getUserDocuments']);
});