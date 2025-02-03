@extends('layouts.master')

@section('styles')
@endsection

@section('breadcrumb')
<div class="col-sm-6 text-left" >
     <h4 class="page-title mb-2">Overall School Report</h4>
     <form method="GET" action="{{ route('school.reports') }}" class="mb-4">
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
    <!-- Department Attendance Overview -->
    <div class="col-12">
        <h3 class="text-uppercase">Department Attendance Overview</h3>
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

    <!-- Course Attendance Overview -->
    <div class="col-12">
        <h3 class="text-uppercase">Course Attendance Overview</h3>
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

<!-- Charts Section -->
<div class="row">
    <!-- Department Attendance Chart -->
    <div class="col-6">
        <div class="card bg-light">
            <div class="card-body">
                <h4 class="mb-2">Department Attendance</h4>
                <p class="text-muted mb-4">This chart provides a detailed distribution of attendance by department, showcasing the number of presents across each department for a selected time period. This allows for better analysis of departmental participation rates.</p>
                <canvas id="departmentAttendanceChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Course Attendance Chart -->
    <div class="col-6">
        <div class="card bg-light">
            <div class="card-body">
                <h4 class="mb-2">Course Attendance</h4>
                <p class="text-muted mb-4">This chart visualizes the attendance distribution by course, giving insights into student presence across various courses. It helps assess the participation levels for each course during the current period.</p>
                <canvas id="courseAttendanceChart"></canvas>
            </div>
        </div>
    </div>
</div>

<div class="row">
     <!-- Absenteeism Rate per Department Chart -->
     <div class="col-6">
        <div class="card bg-light">
            <div class="card-body">
                <h4 class="mb-2">Absenteeism Rate per Department</h4>
                <p class="text-muted mb-4">This chart depicts the absenteeism rate per department, offering a clear view of absence trends across different departments. It helps to identify areas where attendance issues may need to be addressed.</p>
                <canvas id="absenteeismRateChart"></canvas>
            </div>
        </div>
    </div>

    <div class="col-6">
        <div class="card bg-light">
            <div class="card-body">
                <h4 class="mb-2">Absenteeism Rate per Course</h4>
                <p class="text-muted mb-4">This chart depicts the absenteeism rate per department, offering a clear view of absence trends across different departments. It helps to identify areas where attendance issues may need to be addressed.</p>
                <canvas id="absenteeismRateCourseChart"></canvas>
            </div>
        </div>
    </div>

    <!-- School Days Utilization per Department Chart -->
    <div class="col-6">
        <div class="card bg-light">
            <div class="card-body">
                <h4 class="mb-2">School Days Utilization per Department</h4>
                <p class="text-muted mb-4">This chart compares the scheduled school days with actual attendance, illustrating how effectively school days are utilized across various departments. It helps monitor attendance efficiency and identify potential areas for improvement.</p>
                <canvas id="schoolDaysUtilizationChart"></canvas>
            </div>
        </div>
    </div>
</div>
@endsection


@section('scripts')
<script src="{{ URL::asset('assets/js/chart.min.js') }}"></script>
<script>
    // Function to generate a random color
    function getRandomColor() {
        const letters = '0123456789ABCDEF';
        let color = '#';
        for (let i = 0; i < 6; i++) {
            color += letters[Math.floor(Math.random() * 16)];
        }
        return color;
    }

    // Course Attendance Chart
    var ctx1 = document.getElementById('courseAttendanceChart').getContext('2d');
    var courseAttendanceChart = new Chart(ctx1, {
        type: 'bar',
        data: {
            labels: {!! json_encode($chartData['courseLabels']) !!},
            datasets: [{
                label: 'Distinct Attendance Days per Course',
                data: {!! json_encode($chartData['courseAttendance']) !!},
                backgroundColor: function() {
                    const color = getRandomColor(); // Generate a random color
                    return color;
                }               // Border width set to 0 to remove border
            }]
        },
        options: {
            responsive: true,
            hover: {
                mode: null // Disables hover effects (no change in color on hover)
            },
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    // Department Attendance Chart
    var ctx2 = document.getElementById('departmentAttendanceChart').getContext('2d');
    var departmentAttendanceChart = new Chart(ctx2, {
        type: 'bar',
        data: {
            labels: {!! json_encode($chartData['departmentLabels']) !!},
            datasets: [{
                label: 'Distinct Attendance Days per Department',
                data: {!! json_encode($chartData['departmentAttendance']) !!},
                backgroundColor: function() {
                    const color = getRandomColor(); // Generate a random color
                    return color;
                }                  // Border width set to 0 to remove border
            }]
        },
        options: {
            responsive: true,
            hover: {
                mode: null // Disables hover effects (no change in color on hover)
            },
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    // Absenteeism Rate per Department Chart
    var ctx3 = document.getElementById('absenteeismRateChart').getContext('2d');
    var absenteeismRateChart = new Chart(ctx3, {
        type: 'pie',
        data: {
            labels: {!! json_encode($absenteeismReport->pluck('department')->toArray()) !!},  // Department Names
            datasets: [{
                label: 'Absenteeism Rate per Department (%)',
                data: {!! json_encode($absenteeismReport->pluck('absenteeism_rate')->toArray()) !!},  // Absenteeism Rate per Department
                backgroundColor: function() {
                    const color = getRandomColor(); // Generate a random color
                    return color;
                }                // Border width set to 0 to remove border
            }]
        },
        options: {
            responsive: true,
            hover: {
                mode: null // Disables hover effects (no change in color on hover)
            },
            scales: {
                y: {
                    beginAtZero: true  // Ensures the Y-axis starts from 0
                }
            }
        }
    });


    var ctx5 = document.getElementById('absenteeismRateCourseChart').getContext('2d');
    var absenteeismRateCourseChart = new Chart(ctx5, {
        type: 'pie',
        data: {
            labels: {!! json_encode($absenteeismRateCourse->pluck('course')->toArray()) !!},  // Department Names
            datasets: [{
                label: 'Absenteeism Rate per Course (%)',
                data: {!! json_encode($absenteeismRateCourse->pluck('absenteeism_rate')->toArray()) !!},  // Absenteeism Rate per Department
                backgroundColor: function() {
                    const color = getRandomColor(); // Generate a random color
                    return color;
                }                // Border width set to 0 to remove border
            }]
        },
        options: {
            responsive: true,
            hover: {
                mode: null // Disables hover effects (no change in color on hover)
            },
            scales: {
                y: {
                    beginAtZero: true  // Ensures the Y-axis starts from 0
                }
            }
        }
    });



    


    // School Days Utilization per Department Chart
    var ctx4 = document.getElementById('schoolDaysUtilizationChart').getContext('2d');
    var schoolDaysUtilizationChart = new Chart(ctx4, {
        type: 'pie',
        data: {
            labels: {!! json_encode($schoolDaysUtilization->pluck('department')->toArray()) !!},  // Department Names
            datasets: [{
                label: 'School Days Utilization per Department (%)',
                data: {!! json_encode($schoolDaysUtilization->pluck('utilization_rate')->toArray()) !!},  // Utilization Rate per Department
                backgroundColor: function() {
                    const color = getRandomColor(); // Generate a random color
                    return color;
                }                       // Border width set to 0 to remove border
            }]
        },
        options: {
            responsive: true,
            hover: {
                mode: null // Disables hover effects (no change in color on hover)
            },
            scales: {
                y: {
                    beginAtZero: true  // Ensures the Y-axis starts from 0
                }
            }
        }
    });
</script>
@endsection


