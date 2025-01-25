<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Employee;
use App\Models\Latetime;
use App\Models\Attendance;


class DashboardController extends Controller
{

 
    public function index()
    {
        return view('admin.index')->with(['data' => 123]);
    }

}
