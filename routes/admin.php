<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\FacultyController;
use App\Http\Controllers\Admin\GroupController;
use App\Http\Controllers\Admin\QuestionController;
use App\Http\Controllers\Admin\StudentController;
use App\Http\Controllers\Admin\SubjectController;
use App\Http\Controllers\Admin\TestAssignmentController;
use App\Http\Controllers\Admin\TopicController;
use App\Http\Controllers\AjaxController;
use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'admin'])->group(function () {
    Route::prefix('admin')->as('admin.')->group(function () {

        Route::get('/home', function () {
            return view('pages.admin.home');
        })->name('home');

        Route::controller(StudentController::class)->prefix('students')->as('students.')->group(function () {
            Route::get('/index', 'index')->name('index');
            Route::get('/faculties/{faculty}/index', 'index')->name('faculties.index');
            Route::get('/create', 'create')->name('create');
            Route::post('/store', 'store')->name('store');
            Route::get('/{student}', 'show')->name('show');
            Route::get('/{student}/edit', 'edit')->name('edit');
            Route::put('/{student}', 'update')->name('update');
            Route::delete('/{student}', 'destroy')->name('destroy');
            Route::get('students/upload', 'uploadForm')->name('uploadForm');
            Route::post('students/upload-excel', 'uploadExcel')->name('uploadExcel');
            Route::get('students/upload-progress/{id}', 'uploadProgress')->name('uploadProgress');
            Route::get('students/download-template', 'downloadTemplate')->name('downloadTemplate');
        });

        Route::controller(QuestionController::class)->prefix('questions')->as('questions.')->group(function () {
            Route::get('/index', 'index')->name('index');
            Route::get('/create', 'create')->name('create');
            Route::post('/store', 'store')->name('store');
            Route::get('/{question}/edit', 'edit')->name('edit');
            Route::put('/{question}', 'update')->name('update');
            Route::delete('/{question}', 'destroy')->name('destroy');
            Route::get('/{question}/show', 'show')->name('show');
            Route::post('/import', 'import')->name('import');
            Route::get('/questions/subjects-by-language',  'getSubjectsByLanguage')->name('subjects-by-language');
            Route::get('/questions/delete', 'deleteForm')->name('delete');
            Route::get('/questions/count', 'getQuestionsCount')->name('count');
            Route::post('/questions/mass-delete', 'massDelete')->name('mass-delete');
            Route::post('questions/update-via-file', 'updateViaFile')->name('updateViaFile');
        });

        Route::controller(FacultyController::class)->prefix('faculties')->as('faculties.')->group(function () {
            Route::get('/index', 'index')->name('index');
            Route::get('/create', 'create')->name('create');
            Route::post('/store', 'store')->name('store');
            Route::get('/{faculty}/edit', 'edit')->name('edit');
            Route::put('/{faculty}', 'update')->name('update');
            Route::delete('/{faculty}', 'destroy')->name('destroy');
        });

        Route::controller(GroupController::class)->prefix('groups')->as('groups.')->group(function () {
            Route::get('/index', 'index')->name('index');
            Route::get('/faculties/{faculty}/index', 'index')->name('faculties.index');
            Route::get('/create', 'create')->name('create');
            Route::post('/store', 'store')->name('store');
            Route::get('/{group}/edit', 'edit')->name('edit');
            Route::put('/{group}', 'update')->name('update');
            Route::delete('/{group}', 'destroy')->name('destroy');
        });

        Route::controller(SubjectController::class)->prefix('subjects')->as('subjects.')->group(function () {
            Route::get('/index', 'index')->name('index');
            Route::get('/create', 'create')->name('create');
            Route::post('/store', 'store')->name('store');
            Route::get('/{subject}/edit', 'edit')->name('edit');
            Route::put('/{subject}', 'update')->name('update');
            Route::delete('/{subject}', 'destroy')->name('destroy');
        });

        Route::controller(TopicController::class)->prefix('topics')->as('topics.')->group(function () {
            Route::get('/index', 'index')->name('index');
            Route::get('/create', 'create')->name('create');
            Route::post('/store', 'store')->name('store');
            Route::get('/{topic}/edit', 'edit')->name('edit');
            Route::put('/{topic}', 'update')->name('update');
            Route::delete('/{topic}', 'destroy')->name('destroy');
        });

        Route::controller(AjaxController::class)->prefix('ajax')->as('ajax.')->group(function () {
            Route::get('faculties/by-language/{language}', 'getFacultiesByLanguage')->name('faculties.byLanguage');
            Route::get('groups/by-faculty/{faculty}', 'getGroupsByFaculty')->name('groups.byFaculty');
            Route::get('subjects/by-language/{language}', 'getSubjectsByLanguage')->name('subjects.byLanguage');
            Route::get('topics/by-subject-and-language', 'getTopicsBySubjectAndLanguage')->name('topics.bySubjectAndLanguage');
            Route::get('questions/available', 'getAvailableQuestions')->name('questions.available');
        });

        Route::controller(TestAssignmentController::class)->prefix('test-assignments')->as('test-assignments.')->group(function () {
            Route::get('/index', 'index')->name('index');
            Route::get('/all-results', 'allResults')->name('all-results');
            Route::get('/all-results/export-excel', 'exportAllResultsExcel')->name('all-results.export-excel');
            Route::get('/create', 'create')->name('create');
            Route::post('/store', 'store')->name('store');
            Route::get('/{testAssignment}/show', 'show')->name('show');
            Route::get('/{testAssignment}/results', 'results')->name('results');
            Route::get('/edit/{testAssignment}', 'edit')->name('edit');
            Route::put('/{testAssignment}', 'update')->name('update');
            Route::delete('/{id}', 'destroy')->name('destroy');
            Route::post('/{id}/toggle-status', 'toggleStatus')->name('toggle-status');
            Route::get('groups/by-faculty', 'getGroupsByFaculty')->name('groups.by-faculty');
            Route::get('questions/available', 'getAvailableQuestions')->name('questions.available');
            Route::get('/{testAssignment}/student/{testResult}', 'studentDetail')->name('student-detail');
            Route::get('test-assignments/{testAssignment}/student/{testResult}/pdf', 'downloadPdf')->name('student-pdf');
            Route::get('test-assignments/{testAssignment}/export-excel', 'exportExcel')->name('export-excel');
            Route::get('test-assignments/{id}/retake/create', 'retakeCreate')->name('retake.create');
            Route::post('test-assignments/{id}/retake', 'retakeStore')->name('retake.store');
            Route::patch('{testResult}/update-score', 'updateScore')->name('update-score');
        });

        Route::controller(DashboardController::class)->prefix('dashboard')->as('dashboard.')->group(function () {
            Route::get('/index', 'index')->name('index');
        });
    });
});
