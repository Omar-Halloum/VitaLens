<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\MedicalDocumentController;
use App\Http\Controllers\DocumentTextController;


// unauthenticated routes
Route::post('/login', [AuthController::class, "login"]);
Route::post('/register', [AuthController::class, "register"]);
Route::get('/error', [AuthController::class, "displayError"])->name("login");

Route::post('/add-document-text', [DocumentTextController::class, 'addText']);
Route::get('/get-document-text/{documentId}', [DocumentTextController::class, 'getText']);

// authenticated routes
Route::group(["prefix" => "v1", "middleware" => "auth:api"], function (){
    Route::post("/logout", [AuthController::class, "logout"]);
    
    Route::post('/upload-documents', [MedicalDocumentController::class, 'addDocument']);
    Route::get('/get-documents', [MedicalDocumentController::class, 'getUserDocuments']);
});