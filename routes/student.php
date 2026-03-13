<?php

use App\Http\Controllers\StudentTestController;
use App\Http\Controllers\TestController;
use App\Http\Controllers\User\QuestionController;
use App\Http\Controllers\User\TemplateController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'student'])->group(function () {
    Route::controller(StudentTestController::class)->prefix('student')->name('student.')->group(function () {
        Route::get('/home', 'home')->name('home');
        Route::post('/test/{id}/start', 'startTest')->name('test.start');
        Route::get('/test/{id}/take', 'takeTest')->name('test.take');
        Route::post('/test/{testAssignment}/submit-answer', 'submitAnswer')->name('test.submit-answer');
        Route::post('/test/{testAssignment}/submit', 'submitTest')->name('test.submit');
        Route::get('/test/{testAssignment}/result', 'result')->name('test.result');
    });
});
