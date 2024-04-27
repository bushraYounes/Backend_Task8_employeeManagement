<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\NoteController;
use App\Http\Controllers\ProjectController;

Route::controller(AuthController::class)->group(function () {
    Route::post('login', 'login');
    Route::post('register', 'register');
    Route::post('logout', 'logout')->middleware('auth:api');
    Route::post('refresh', 'refresh')->middleware('auth:api');
});



Route::group(['middleware' => 'auth:api'], function () {
    Route::apiResource('departments', DepartmentController::class);
    Route::delete('/departments/{id}/force-delete', [DepartmentController::class, 'forceDelete']);

    Route::apiResource('employees', EmployeeController::class);
    Route::delete('/employees/{id}/force-delete', [EmployeeController::class, 'forceDelete']);

    Route::apiResource('projects', ProjectController::class);
    Route::apiResource('notes', NoteController::class);
});
