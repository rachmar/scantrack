@extends('layouts.master')

@section('styles')
@endsection

@section('breadcrumb')
<div class="col-sm-6 text-left" >
     <h4 class="page-title mb-2">Overall School Report</h4>
     <form method="GET" action="{{ route('school.reports') }}" class="mb-4">
        @php
            $startDate = request()->get('start_date', \Carbon\Carbon::now()->toDateString());
            $endDate = request()->get('end_date', \Carbon\Carbon::now()->toDateString());
        @endphp
        <div class="row">
            <div class="col-md-4">
                <label for="start_date" class="form-label">Start Date:</label>
                <input type="date" id="start_date" name="start_date" class="form-control" value="{{ $startDate }}">
            </div>

            <div class="col-md-4">
                <label for="end_date" class="form-label">End Date:</label>
                <input type="date" id="end_date" name="end_date" class="form-control" value="{{ $endDate }}">
            </div>
            <div class="col-md-4 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100">Filter</button>
            </div>
        </div>
    </form>
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

<div class="row">
    <div class="col-xl-12">
        <div class="card">
            <div class="card-body">
                <h4 class="mt-0 header-title">Total Student Attendance by Department</h4>
                <div class="row">
                    <div class="col-lg-8 offset-lg-2">
                        <canvas id="courseAttendanceChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-xl-12">
        <div class="card">
            <div class="card-body">
                <h4 class="mt-0 header-title">Total Student Daily Attendance</h4>
                <div class="row">
                    <div class="col-lg-8 offset-lg-2">
                        <canvas id="dailyAttendanceChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-xl-12">
        <div class="card">
            <div class="card-body">
                <h4 class="mt-0 header-title">Department with High Absentee Rates</h4>
                <div class="row">
                    <div class="col-lg-8 offset-lg-2">
                        <canvas id="coursesWithHighAbsenteeRateCTX"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="{{ URL::asset('assets/js/chart.min.js') }}"></script>

<script>
    const labels = @json($coursesWithHighAbsenteeRateLabels);

    // Generate unique colors for each course
    const backgroundColors = labels.map(() => {
        return `rgba(${Math.floor(Math.random() * 255)}, ${Math.floor(Math.random() * 255)}, ${Math.floor(Math.random() * 255)}, 0.2)`;
    });
    const borderColors = labels.map((_, index) => {
        return backgroundColors[index].replace('0.2', '1');
    });

    // Combined Course Enrollment and Attendance Chart
    var courseAttendanceCtx = document.getElementById('courseAttendanceChart').getContext('2d');
    var courseAttendanceChart = new Chart(courseAttendanceCtx, {
        type: 'bar',
        data: {
            labels: @json($attendanceByCourseLabels),
            datasets: [{
                label: 'Attendance Count',
                data: @json($attendanceByCourseValues),
                backgroundColor: backgroundColors,
                borderColor: borderColors,
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: { beginAtZero: true }
            }
        }
    });

    // Daily Attendance Chart
    var dailyAttendanceCtx = document.getElementById('dailyAttendanceChart').getContext('2d');
    var dailyAttendanceChart = new Chart(dailyAttendanceCtx, {
        type: 'bar',
        data: {
            labels: @json($attendanceLabels),
            datasets: [{
                label: 'Attendance Count',
                data: @json($attendanceValues),
                backgroundColor: backgroundColors,
                borderColor: borderColors,
                fill: true,
                tension: 0.1
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: { beginAtZero: true }
            }
        }
    });

    // Courses with High Absentee Rates
    var coursesWithHighAbsenteeRateCTX = document.getElementById('coursesWithHighAbsenteeRateCTX').getContext('2d');
    var coursesWithHighAbsenteeRateChart = new Chart(coursesWithHighAbsenteeRateCTX, {
        type: 'line',
        data: {
            labels: @json($coursesWithHighAbsenteeRateLabels),
            datasets: [
                {
                    label: 'Total Students',
                    data: @json($coursesWithHighAbsenteeRateTotalStudents),
                    backgroundColor: 'rgba(54, 162, 235, 0.2)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                },
                {
                    label: 'Attendance Count',
                    data: @json($coursesWithHighAbsenteeRateAttendanceCounts),
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    borderColor: 'rgba(75, 192, 192, 1)',
                    borderWidth: 1
                },
                {
                    label: 'Absent Count',
                    data: @json($coursesWithHighAbsenteeRateAbsentCounts),
                    backgroundColor: 'rgba(255, 159, 64, 0.2)',
                    borderColor: 'rgba(255, 159, 64, 1)',
                    borderWidth: 1,
                    type: 'line'
                },
                {
                    label: 'Absentee Rate (%)',
                    data: @json($coursesWithHighAbsenteeRateValues),
                    backgroundColor: 'rgba(0, 0, 0, 0.2)',
                    borderColor: 'rgb(0, 0, 0)',
                    borderWidth: 1,
                    type: 'line'
                }
            ]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true,
                    title: { display: true, text: 'Absentee Rate (%)' }
                },
                x: {
                    title: { display: true, text: 'Courses' }
                }
            }
        }
    });
</script>
@endsection
