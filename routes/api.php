<?php

use App\Http\Controllers\Api\StudentController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\SectionController;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('sections', SectionController::class)->name('sections.index');
Route::post('/students/{id}/toggle-status', [StudentController::class, 'toggleStatus'])->name('students.toggle-status');

