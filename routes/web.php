<?php

use Illuminate\Support\Facades\Route;

include "auth.php";

Route::get('/', function () {
    return view('welcome');
})->name('welcome');

Route::post('/process', '\App\Http\Controllers\ScanController@show')->name('process');

Route::group(['middleware' => ['auth', 'auth.roles'], 'roles' => ['admin']], function () {
    Route::get('/admin', '\App\Http\Controllers\DashboardController@index')->name('admin');
});