@extends('layouts.master')

@section('styles')
@endsection

@section('breadcrumb')
<div class="col-sm-6 text-left" >
     <h4 class="page-title mb-3">Student Report</h4>
     <form method="GET" action="{{ route('student.reports') }}" class="mb-4">
        @php
            $startDate = request()->get('start_date', \Carbon\Carbon::now()->toDateString());
            $endDate = request()->get('end_date', \Carbon\Carbon::now()->toDateString());
        @endphp
        <div class="row">
            <div class="col-md-3">
                <label for="student_id" class="form-label">Student ID:</label>
                <input type="text" id="student_id" name="student_id" class="form-control" value="ST677485">
            </div>
            <div class="col-md-3">
                <label for="start_date" class="form-label">Start Date:</label>
                <input type="date" id="start_date" name="start_date" class="form-control" value="{{ $startDate }}">
            </div>
            <div class="col-md-3">
                <label for="end_date" class="form-label">End Date:</label>
                <input type="date" id="end_date" name="end_date" class="form-control" value="{{ $endDate }}">
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100">Filter</button>
            </div>
        </div>
    </form>
</div>
@endsection

@section('content')
<div class="row">
   <div class="col-xl-6">
      <div class="card">
         <div class="card-body">
            @if (isset($student->id))
                <h4 class="mt-0 header-title mb-4">Name: {{$student->fullName()}} |  {{$student->course->name}} </h4>
            @endif
            <div class="row">
               <div class="col-lg-6 d-flex">
                  <div class="card mini-stat  text-white flex-fill {{ $isPresentToday  ? 'bg-success' : 'bg-danger' }}">
                     <div class="card-body">
                        <div class="mb-4">
                           <div class="float-left mini-stat-img mr-4">
                              <span class="ti-check-box"></span>
                           </div>
                           <h5 class="font-16 text-uppercase mt-0 text-white">Present Today?</h5>
                           <p class="font-500">
                              {{ $isPresentToday == 'Yes' ? 'YES' : 'NO' }}
                           </p>
                        </div>
                     </div>
                  </div>
               </div>
               <div class="col-lg-6 d-flex">
                  <div class="card mini-stat bg-primary text-white flex-fill">
                     <div class="card-body">
                        <div class="mb-4">
                           <div class="float-left mini-stat-img mr-4">
                              <span class="ti-check-box"></span>
                           </div>
                           <h5 class="font-16 text-uppercase mt-0 text-white">Total Student Attendance</h5>
                           <h4 class="font-500">{{ $numofStudentAttendance }}</h4>
                        </div>
                     </div>
                  </div>
               </div>
            </div>
            <h4 class="mt-0 header-title">Student Attendance Logs</h4>
            <div class="row">
               <div class="col-lg-12">
                  <canvas id="dailyAttendanceChart"></canvas>
               </div>
            </div>
         </div>
      </div>
   </div>
   <div class="col-lg-6">
      <div class="card">
         <div class="card-body">
            <div class="row">
               <div class="col-lg-12">
                  <div id="attendanceCalendar"></div>
               </div>
            </div>
         </div>
      </div>
   </div>
</div>
@endsection

@section('scripts')
<script src="{{ URL::asset('assets/js/chart.min.js') }}"></script>
<script src="{{ URL::asset('assets/js/fullcalendar.min.js') }}"></script>
<script>
   

</script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var calendarEl = document.getElementById('attendanceCalendar');

        // Initialize FullCalendar
        var calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            events: @json($dailyAttendanceEvents), // Use the attendance events from the controller
        });

        calendar.render();

        // Function to generate random color in hex format
        function getRandomColor() {
            const letters = '0123456789ABCDEF';
            let color = '#';
            for (let i = 0; i < 6; i++) {
                color += letters[Math.floor(Math.random() * 16)];
            }
            return color;
        }

        // Generate an array of random border colors
        const backgroundColors = @json($monthlyLabels).map(() => getRandomColor());
        const borderColors = @json($monthlyLabels).map(() => getRandomColor());

        var dailyAttendanceCtx = document.getElementById('dailyAttendanceChart').getContext('2d');
        var dailyAttendanceChart = new Chart(dailyAttendanceCtx, {
        type: 'bar',
            data: {
                labels: @json($monthlyLabels),
                datasets: [{
                    label: 'Attendance Count',
                    data: @json($monthlyValues),
                    backgroundColor: backgroundColors,  // Assuming backgroundColors is defined somewhere
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
    });
</script>
@endsection
