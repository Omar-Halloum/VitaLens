<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\BodyMetricController;
use App\Http\Controllers\MedicalDocumentController;
use App\Http\Controllers\DocumentTextController;
use App\Http\Controllers\MedicalMetricController;
use App\Http\Controllers\HabitLogController;
use App\Http\Controllers\EngineeredFeatureController;
use App\Http\Controllers\RiskPredictionController;


// unauthenticated routes
Route::post('/login', [AuthController::class, "login"]);
Route::post('/register', [AuthController::class, "register"]);
Route::get('/error', [AuthController::class, "displayError"])->name("login");

Route::post('/add-document-text', [DocumentTextController::class, 'addText']);
Route::get('/get-document-text/{documentId}', [DocumentTextController::class, 'getText']);

Route::post('/extract-metrics', [MedicalMetricController::class, 'extractMetrics']);

// Endpoint for Python service to store predictions
Route::post('/store-predictions', [RiskPredictionController::class, 'storePredictions']);

// authenticated routes
Route::group(["prefix" => "v1", "middleware" => "auth:api"], function (){
    Route::post("/logout", [AuthController::class, "logout"]);
    Route::get("/profile", [UserController::class, "getProfile"]);

    Route::post('/update-body-metrics', [BodyMetricController::class, 'updateMetrics']);
    Route::get('/body-metrics', [BodyMetricController::class, 'getUserMetrics']);

    Route::post('/upload-documents', [MedicalDocumentController::class, 'addDocument']);
    Route::get('/get-documents', [MedicalDocumentController::class, 'getUserDocuments']);

    Route::get('/medical-metrics', [MedicalMetricController::class, 'getUserMetrics']);
    Route::get('/medical-metrics/document/{documentId}', [MedicalMetricController::class, 'getDocumentMetrics']);

    Route::post('/log-habit', [HabitLogController::class, 'storeHabit']);
    Route::get('/habit-logs', [HabitLogController::class, 'getUserLogs']);
    Route::get('/habit-metrics', [HabitLogController::class, 'getUserHabitMetrics']);
    Route::get('/habit-log-metrics/{logId}', [HabitLogController::class, 'getLogMetrics']);

    // Engineered Features
    Route::post('/engineer-features', [EngineeredFeatureController::class, 'engineerFeatures']);
    Route::get('/get-engineered-features', [EngineeredFeatureController::class, 'getUserFeatures']);
    Route::get('/feature-history', [EngineeredFeatureController::class, 'getFeatureHistory']);
    Route::get('/feature-history/{featureName}', [EngineeredFeatureController::class, 'getFeatureHistory']);

    // Risk Predictions
    Route::post('/predict-risks', [RiskPredictionController::class, 'predictRisks']);
    Route::get('/risk-predictions', [RiskPredictionController::class, 'getUserPredictions']);
    Route::get('/risk-predictions/{riskKey}', [RiskPredictionController::class, 'getRiskPrediction']);
    Route::get('/risk-factors/{riskKey}', [RiskPredictionController::class, 'getRiskFactors']);
    Route::get('/check-data-sufficiency', [RiskPredictionController::class, 'checkDataSufficiency']);
    Route::get('/risk-history', [RiskPredictionController::class, 'getRiskHistory']);
    Route::get('/risk-history/{riskKey}', [RiskPredictionController::class, 'getRiskHistory']);
});