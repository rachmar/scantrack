@extends('layouts.master')

@section('styles')
@endsection

@section('breadcrumb')
<div class="col-sm-6 text-left" >
     <h4 class="page-title">Dashboard</h4>
     <ol class="breadcrumb">
         <li class="breadcrumb-item active">Welcome to Attendance Management System</li>
         <li class="breadcrumb-item active">Welcome to Attendance Management System</li>
     </ol>
     <button>sad</button>
</div>
@endsection

@section('content')
<div class="row">
    <div class="col-xl-3 col-md-6 d-flex">
        <div class="card mini-stat bg-primary text-white flex-fill">
            <div class="card-body">
                <div class="mb-4">
                    <div class="float-left mini-stat-img mr-4">
                        <i class="fas fa-user-check fa-2x"></i>
                    </div>
                    <h5 class="font-16 text-uppercase mt-0 text-white">Total Students Present</h5>
                    <h4 class="font-500">{{ $studentsPresentToday }}</h4>
                </div>
            </div>
        </div>
    </div>
    @foreach($studentsPresentTodayByCourse as $course)
        <div class="col-xl-3 col-md-6 d-flex">
            <div class="card mini-stat bg-primary text-white flex-fill">
                <div class="card-body">
                    <div class="mb-4">
                        <div class="float-left mini-stat-img mr-4">
                            <i class="fas fa-chalkboard-teacher fa-2x"></i>
                        </div>
                        <h5 class="font-16 text-uppercase mt-0 text-white">{{ $course->course_name }}</h5>
                        <h4 class="font-500">{{ $course->attendance_count }}</h4>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</div>

@endsection

@section('scripts')
@endsection