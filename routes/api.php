<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\AttendanceController;

// ESP32 endpoints protected by API key middleware
Route::middleware('rfid.auth')->group(function () {
    Route::post('/attendance/scan', [AttendanceController::class, 'scan']);
    Route::post('/hardware/heartbeat', [AttendanceController::class, 'heartbeat']);
});

// Telegram Webhook
Route::post('/telegram/webhook', [\App\Http\Controllers\TelegramWebhookController::class, 'handleWebhook']);

// Public device-status — ESP32 can query this too
Route::get('/device-status', [AttendanceController::class, 'deviceStatus']);

// Admin API token login
Route::post('/auth/login', [AuthController::class, 'apiLogin']);

// Protected API routes (Sanctum token)
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', fn(Request $r) => $r->user());
});
