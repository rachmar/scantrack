<?php

use Illuminate\Support\Facades\Route;

include "auth.php";

Route::get('/', function () {
    return view('welcome');
})->name('welcome');

Route::get('/fullscreen', function () {
    return view('fullscreen');
})->name('fullscreen');

Route::post('/process', '\App\Http\Controllers\ScanController@show')->name('process.show');

Route::group(['middleware' => ['auth', 'auth.roles'], 'roles' => ['admin']], function () {
    Route::get('/admin', '\App\Http\Controllers\DashboardController@index')->name('admin');
    Route::get('/school-reports', '\App\Http\Controllers\ReportController@schoolReports')->name('school.reports');

    Route::get('/reports/courses', '\App\Http\Controllers\ReportController@courseReportIndex')->name('reports.courses.index');
    Route::post('/reports/courses/lists', '\App\Http\Controllers\ReportController@getCourses')->name('reports.courses.lists');

    Route::get('/reports/students', '\App\Http\Controllers\ReportController@studentReportIndex')->name('reports.students.index');
    Route::get('/reports/students/{id}', '\App\Http\Controllers\ReportController@studentReportShow')->name('reports.students.show');

    Route::resource('students', '\App\Http\Controllers\StudentController');
});


