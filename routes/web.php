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
    Route::get('/student-reports', '\App\Http\Controllers\ReportController@studentReports')->name('student.reports');
    Route::resource('students', '\App\Http\Controllers\StudentController');
});