<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        
        .tile {
            background-color: #ffffff;
            padding: 20px;
            text-align: center;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease-in-out;
        }
        .tile:hover {
            transform: translateY(-10px);
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.15);
        }
        .tile h3 {
            font-size: 1.4em;
            margin-bottom: 10px;
            color: #495057;
        }
        .tile .count {
            font-size: 2.5em;
            font-weight: bold;
            color: #007bff;
        }
        .tile .icon {
            font-size: 3em;
            color: #007bff;
        }
        
    </style>
</head>
<body>
    <div class="container-fluid">
        <h1 class="mt-4">Reports Dashboard</h1>
        <h3 class="mt-4"> <span class="text-muted"> Start Date: {{ $startDate }} End Date: {{ $endDate }}</span></h3>

        <!-- Date Filter Form -->
        <form method="GET" action="{{ route('reports') }}" class="my-4">
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

        <!-- Tiles for Reports -->
        <div class="row mb-4">
            <!-- Number of Students Present Today (Overall) -->
            <div class="col-md-3 mb-3">
                <div class="tile">
                    <div class="icon"><i class="bi bi-person-check"></i></div>
                    <h3>Total Students Present</h3>
                    <div class="count">{{ $studentsPresentToday }}</div>
                </div>
            </div>

            <!-- Number of Students Present Today by Course -->
            @foreach($studentsPresentTodayByCourse as $course)
                <div class="col-md-3 mb-3">
                    <div class="tile">
                        <div class="icon"><i class="bi bi-journal-text"></i></div>
                        <h3>{{ $course->course_name }}</h3>
                        <div class="count">{{ $course->attendance_count }}</div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Combined Course Enrollment and Attendance Chart -->
        <div>
            <h2>Attendance Breakdown per Course</h2>
            <canvas id="courseAttendanceChart"></canvas>
        </div>

        <!-- Daily Attendance Chart -->
        <div class="mt-4">
            <h2>Daily Attendance Trends</h2>
            <canvas id="dailyAttendanceChart"></canvas>
        </div>

        <!-- Courses with High Absentee Rates -->
        <div class="mt-4">
            <h2>Courses with High Absentee Rates</h2>
            <canvas id="coursesWithHighAbsenteeRateCTX"></canvas>
        </div>

    </div>

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
</body>
</html>
