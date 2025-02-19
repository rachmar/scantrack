@extends('layouts.master')

@section('styles')
@endsection

@section('breadcrumb')
<div class="col-sm-12 text-left">
    <h5 class="page-title mb-3">Filters</h5>
    <form method="GET" action="{{ route('reports.courses.index') }}" class="">
        <div class="row g-2 align-items-end d-flex flex-wrap">
            <div class="col-md-3">
                <label for="department" class="form-label">Select Department:</label>
                <select id="department" class="form-control" name="department">
                    <option value="">-- Select Department --</option>
                    @foreach($departments as $department)
                        <option value="{{ $department->id }}" {{ request('department') == $department->id ? 'selected' : '' }}>
                            {{ $department->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label for="course" class="form-label">Select Course:</label>
                <select id="course" class="form-control" name="course">
                    <option value="">-- Select Course --</option>
                </select>
            </div>
            <div class="col-md-3">
                <label for="semester" class="form-label">Select Semester:</label>
                <select id="semester" class="form-control" name="semester">
                    <option value="">-- Select Semester --</option>
                    @foreach($semesters as $sems)
                        <option value="{{ $sems->id }}" {{ request('semester') == $sems->id ? 'selected' : '' }}>
                            {{ $sems->name }} | {{ $sems->start_date }} - {{ $sems->end_date }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-primary w-100">Submit</button>
            </div>
        </div>
    </form>
</div>
@endsection

@section('content')
    @if( $semester)
        <h2 class="my-3">
            {{ $semester->name }} | {{ $semester->start_date }} - {{ $semester->end_date }}
        </h2>

        <div class="row">
            <!-- Department Attendance Overview -->
            <div class="col-12">
                <h5 class="text-uppercase">Department Attendance Overview</h5>
                <p class="text-muted">This section provides a comprehensive overview of attendance by department. It highlights the total number of presents in each department, helping to evaluate departmental engagement and track attendance trends.</p>
            </div>
            @foreach($attendanceSummedByDepartment as $departmentKey => $departmentValue)
                <div class="col-xl-2 col-md-3 d-flex ">
                    <div class="card mini-stat bg-primary text-white flex-fill">
                        <div class="card-body">
                            <div class="">
                                <div class="float-left mini-stat-img mr-4">
                                    <i class="fas fa-chalkboard-teacher fa-2x"></i>
                                </div>
                                <h5 class="font-16 text-uppercase mt-0 text-white">{{ $departmentKey }}</h5>
                                <h5 class="font-500">{{ $departmentValue }} present</h5>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach

            <!-- Course Attendance Overview -->
            <div class="col-12">
                <h5 class="text-uppercase">Course Attendance Overview</h5>
                <p class="text-muted">This section provides an overview of student attendance by course. It highlights the total number of presents in each course, offering insights into course participation and student engagement levels.</p>
            </div>
            @foreach($attendanceSummedByCourse as $courseKey => $courseValue)
                <div class="col-xl-2 col-md-3 d-flex ">
                    <div class="card mini-stat bg-primary text-white flex-fill">
                        <div class="card-body">
                            <div class="">
                                <div class="float-left mini-stat-img mr-4">
                                    <i class="fas fa-chalkboard-teacher fa-2x"></i>
                                </div>
                                <h5 class="font-16 text-uppercase mt-0 text-white">{{ $courseKey }}</h5>
                                <h5 class="font-500">{{ $courseValue }} present</h5>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
        <div class="row">
            <!-- Department Attendance Chart -->
            <div class="col-6">
                <div class="card bg-light">
                    <div class="card-body">
                        <h5 class="mb-2">Department Attendance</h5>
                        <p class="text-muted ">This chart provides a detailed distribution of attendance by department, showcasing the number of presents across each department for a selected time period. This allows for better analysis of departmental participation rates.</p>
                        <canvas id="departmentAttendanceChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Course Attendance Chart -->
            <div class="col-6">
                <div class="card bg-light">
                    <div class="card-body">
                        <h5 class="mb-2">Course Attendance</h5>
                        <p class="text-muted ">This chart visualizes the attendance distribution by course, giving insights into student presence across various courses. It helps assess the participation levels for each course during the current period.</p>
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
                        <h5 class="mb-2">Absenteeism Rate per Department</h5>
                        <p class="text-muted ">This chart depicts the absenteeism rate per department, offering a clear view of absence trends across different departments. It helps to identify areas where attendance issues may need to be addressed.</p>
                        <canvas id="absenteeismRateChart"></canvas>
                    </div>
                </div>
            </div>

            <div class="col-6">
                <div class="card bg-light">
                    <div class="card-body">
                        <h5 class="mb-2">Absenteeism Rate per Course</h5>
                        <p class="text-muted ">This chart depicts the absenteeism rate per department, offering a clear view of absence trends across different departments. It helps to identify areas where attendance issues may need to be addressed.</p>
                        <canvas id="absenteeismRateCourseChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    @endif
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
        type: 'bar',
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
        type: 'bar',
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

</script>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        const departmentDropdown = document.getElementById("department");
        const courseDropdown = document.getElementById("course");
        const selectedCourse = '{{ request('course') }}'; // Get the selected course from the GET request
        
        // Retain selected course value on page load if available
        if (selectedCourse) {
            courseDropdown.value = selectedCourse;
        }

        departmentDropdown.addEventListener("change", function () {
            let departmentId = this.value;
            courseDropdown.innerHTML = '<option value="">Loading...</option>';
            
            if (departmentId) {
                fetch("{{ route('reports.get.courses') }}", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ department_id: departmentId })
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error("Network response was not ok");
                    }
                    return response.json();
                })
                .then(data => {
                    courseDropdown.innerHTML = '<option value="">-- Select Course --</option>';
                    data.forEach(course => {
                        let option = document.createElement("option");
                        option.value = course.id;
                        option.textContent = course.name;

                        // Pre-select the course if it's the same as the one in the GET request
                        if (course.id == selectedCourse) {
                            option.selected = true;
                        }

                        courseDropdown.appendChild(option);
                    });
                })
                .catch(error => {
                    console.error("Error fetching courses:", error);
                    courseDropdown.innerHTML = '<option value="">Error loading courses</option>';
                });
            } else {
                courseDropdown.innerHTML = '<option value="">-- Select Course --</option>';
            }
        });

        // Trigger the change event on page load if a department is selected
        if (departmentDropdown.value) {
            departmentDropdown.dispatchEvent(new Event('change'));
        }
    });
</script>

@endsection


