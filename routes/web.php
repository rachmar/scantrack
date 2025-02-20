<?php

use Illuminate\Support\Facades\Route;

include "auth.php";

Route::get('/', function () {
    return redirect()->route('public.scan.index');
})->name('welcome');

Route::get('/scan', '\App\Http\Controllers\ScanController@index')->name('public.scan.index');
Route::post('/scan', '\App\Http\Controllers\ScanController@show')->name('public.scan.show');

Route::get('/visitor', '\App\Http\Controllers\VisitorController@index')->name('public.visitor.index');
Route::post('/visitor', '\App\Http\Controllers\VisitorController@store')->name('public.visitor.store');

Route::group(['middleware' => ['auth', 'auth.roles'], 'roles' => ['admin']], function () {
    Route::get('/admin', '\App\Http\Controllers\DashboardController@index')->name('admin');

    Route::get('/reports/courses', '\App\Http\Controllers\ReportController@courseReportIndex')->name('reports.courses.index');
    Route::post('/reports/get/courses', '\App\Http\Controllers\ReportController@getCourses')->name('reports.get.courses');
    Route::post('/reports/get/semesters', '\App\Http\Controllers\ReportController@getSemesters')->name('reports.get.semesters');

    Route::get('/reports/students', '\App\Http\Controllers\ReportController@studentReportIndex')->name('reports.students.index');
    Route::get('/reports/students/{id}', '\App\Http\Controllers\ReportController@studentReportShow')->name('reports.students.show');

    Route::get('/reports/visitors', '\App\Http\Controllers\ReportController@visitorReportIndex')->name('reports.visitor.index');
    Route::get('/reports/visitors/{slug}', '\App\Http\Controllers\ReportController@visitorReportShow')->name('reports.visitor.show');

    Route::resource('students', '\App\Http\Controllers\StudentController');
    Route::resource('semesters', '\App\Http\Controllers\SemesterController');
    Route::resource('holidays', '\App\Http\Controllers\HolidayController');
    Route::resource('absences', '\App\Http\Controllers\AbsenceRecordController');
});


