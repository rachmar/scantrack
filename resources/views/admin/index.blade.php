@extends('layouts.master')

@section('styles')
@endsection

@section('breadcrumb')
<div class="col-sm-6 text-left" >
     <h4 class="page-title">Dashboard</h4>
     <ol class="breadcrumb">
         <li class="breadcrumb-item active">Welcome to Attendance Management System</li>
     </ol>
</div>
@endsection

@section('content')
<div class="row">
    <!-- Department Attendance Overview -->
    <div class="col-12">
        <h3 class="text-uppercase">Weekly Department Attendance Overview</h3>
        <p class="text-muted">This section provides a comprehensive overview of attendance by department. It highlights the total number of presents in each department, helping to evaluate departmental engagement and track attendance trends.</p>
    </div>
    @foreach($attendanceSummedByDepartment as $departmentKey => $departmentValue)
        <div class="col-xl-2 col-md-3 d-flex mb-4">
            <div class="card mini-stat bg-primary text-white flex-fill">
                <div class="card-body">
                    <div class="mb-4">
                        <div class="float-left mini-stat-img mr-4">
                            <i class="fas fa-chalkboard-teacher fa-2x"></i>
                        </div>
                        <h5 class="font-16 text-uppercase mt-0 text-white">{{ $departmentKey }}</h5>
                        <h4 class="font-500">{{ $departmentValue }} presents</h4>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
    <div class="col-12">
        <h3 class="text-uppercase">Weekly Course Attendance Overview</h3>
        <p class="text-muted">This section provides an overview of student attendance by course. It highlights the total number of presents in each course, offering insights into course participation and student engagement levels.</p>
    </div>
    @foreach($attendanceSummedByCourse as $courseKey => $courseValue)
        <div class="col-xl-2 col-md-3 d-flex mb-4">
            <div class="card mini-stat bg-primary text-white flex-fill">
                <div class="card-body">
                    <div class="mb-4">
                        <div class="float-left mini-stat-img mr-4">
                            <i class="fas fa-chalkboard-teacher fa-2x"></i>
                        </div>
                        <h5 class="font-16 text-uppercase mt-0 text-white">{{ $courseKey }}</h5>
                        <h4 class="font-500">{{ $courseValue }} presents</h4>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</div>
@endsection

@section('scripts')
@endsection