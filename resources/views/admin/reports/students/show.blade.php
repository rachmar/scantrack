@extends('layouts.master')
@section('styles')
@endsection

@section('breadcrumb')
@if (isset($student->id))
<div class="col-sm-6 text-left">
    <h5 class="page-title mb-3">Filters</h5>
    <form method="GET" action="{{ route('reports.students.show', ['id' => $student->id]) }}" class="mb-4">
        <div class="row">
            <div class="col-md-8">
                <label for="semester" class="form-label">Select Semester:</label>
                <select id="semester" class="form-control" name="semester">
                    <option value="">-- Select Semester --</option>
                    @foreach($semesters as $sems)
                        <option value="{{ $sems->id }}" {{ (request('semester') ?? $activeSemester->id) == $sems->id ? 'selected' : '' }}>
                            {{ $sems->name }} | {{ $sems->start_date }} - {{ $sems->end_date }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100">Submit</button>
            </div>
        </div>
    </form>
</div>
@endif
@endsection

@section('content')
@if ($student && $activeSemester)
    <h2 class="mb-4">
        {{ $activeSemester->name }} | {{ $activeSemester->start_date }} - {{ $activeSemester->end_date }}
    </h2>
    <div class="row">
    <div class="col-lg-3 d-flex">
        <div class="card mini-stat bg-success text-white flex-fill">
            <div class="card-body">
                <div class="mb-4">
                <div class="float-left mini-stat-img mr-4">
                    <span class="ti-user"></span> <!-- Changed Icon -->
                </div>
                <h5 class="font-16 text-uppercase mt-0 text-white">Student Name</h5>
                <h4 class="font-500">{{$student->fullName()}}</h4>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-3 d-flex">
        <div class="card mini-stat bg-success text-white flex-fill">
            <div class="card-body">
                <div class="mb-4">
                <div class="float-left mini-stat-img mr-4">
                    <span class="ti-book"></span> <!-- Changed Icon -->
                </div>
                <h5 class="font-16 text-uppercase mt-0 text-white">Student Course</h5>
                <h4 class="font-500">{{$student->course->slug }} |  {{$student->course->department->name}}</h4>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-3 d-flex">
        <div class="card mini-stat bg-success text-white flex-fill">
            <div class="card-body">
                <div class="mb-4">
                <div class="float-left mini-stat-img mr-4">
                    <span class="ti-calendar"></span> <!-- Changed Icon -->
                </div>
                <h5 class="font-16 text-uppercase mt-0 text-white">Student Schedule</h5>
                <h4 class="font-500">{{$student->schedule()}}</h4>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-3 d-flex">
        <div class="card mini-stat text-white flex-fill {{ $isPresentToday  ? 'bg-success' : 'bg-danger' }}">
            <div class="card-body">
                <div class="mb-4">
                <div class="float-left mini-stat-img mr-4">
                    <span class="ti-check"></span> <!-- Changed Icon -->
                </div>
                <h5 class="font-16 text-uppercase mt-0 text-white">Present Today?</h5>
                <h4 class="font-500"> {{ $isPresentToday == 'Yes' ? 'YES' : 'NO' }}</h4>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-3 d-flex">
        <div class="card mini-stat bg-primary text-white flex-fill">
            <div class="card-body">
                <div class="mb-4">
                <div class="float-left mini-stat-img mr-4">
                    <span class="ti-calendar"></span> <!-- Changed Icon -->
                </div>
                <h5 class="font-16 text-uppercase mt-0 text-white">Total Student School Days</h5>
                <h4 class="font-500">{{ $numOfSchoolDays }} days</h4>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-3 d-flex">
        <div class="card mini-stat bg-primary text-white flex-fill">
            <div class="card-body">
                <div class="mb-4">
                <div class="float-left mini-stat-img mr-4">
                    <span class="ti-check-box"></span> <!-- Changed Icon -->
                </div>
                <h5 class="font-16 text-uppercase mt-0 text-white">Total Student Attendance</h5>
                <h4 class="font-500">{{ $numOfStudentAttendance }} days</h4>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-3 d-flex">
        <div class="card mini-stat bg-primary text-white flex-fill">
            <div class="card-body">
                <div class="mb-4">
                <div class="float-left mini-stat-img mr-4">
                    <span class="ti-na"></span> <!-- Changed Icon -->
                </div>
                <h5 class="font-16 text-uppercase mt-0 text-white">Total Student Absent</h5>
                <h4 class="font-500">{{ $numOfAbsences }} days</h4>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-3 d-flex">
        <div class="card mini-stat bg-success text-white flex-fill">
            <div class="card-body">
                <div class="mb-4">
                <div class="float-left mini-stat-img mr-4">
                    <span class="ti-na"></span> <!-- Changed Icon -->
                </div>
                <h5 class="font-16 text-uppercase mt-0 text-white">Absenteeism Rate</h5>
                <h4 class="font-500">{{ $absenteeismRate }} %</h4>
                </div>
            </div>
        </div>
    </div>
    </div>
    <div class="row">
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
    <div class="col-lg-6">
        <div class="card">
            <div class="card-body">
                <div class="row">
                <div class="col-lg-12">
                    @foreach($studentAttendanceTable as $date => $items)
                    <table class="table table-bordered table-sm mb-4">
                        <thead class="bg-success text-white">
                            <tr>
                            <th colspan="4" class="text-center">{{ \Carbon\Carbon::parse($date)->format('F j, Y') ?? 'Unknown' }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($items->chunk(2) as $chunk)
                            <tr class="bg-light">
                            @foreach($chunk as $item)
                            <!-- Format the Date to be readable with time -->
                            <td>{{ \Carbon\Carbon::parse($item->created_at)->format('F j, Y g:i A') ?? 'Unknown' }}</td>
                            <!-- Badge the IN and OUT statuses -->
                            <td>
                                <span class="badge 
                                    @if($item->status == 'IN') badge-success
                                    @elseif($item->status == 'OUT') badge-warning
                                    @else badge-danger @endif">
                                {{ $item->status ?? 'Unknown' }}
                                </span>
                            </td>
                            @endforeach
                            @if(count($chunk) == 1)
                            <td colspan="2" class="bg-light"></td>
                            <!-- Empty cells to balance the row if there's only one item -->
                            @endif
                            </tr>
                            @endforeach
                            @if($items->isEmpty())
                            <tr class="bg-secondary text-white">
                            <td colspan="4" class="text-center">No items available for this date.</td>
                            </tr>
                            @endif
                        </tbody>
                    </table>
                    @endforeach
                </div>
                </div>
            </div>
        </div>
    </div>
    </div>
@endif
@endsection

@section('scripts')
<script src="{{ URL::asset('assets/js/chart.min.js') }}"></script>
<script src="{{ URL::asset('assets/js/fullcalendar.min.js') }}"></script>
<script>
   document.addEventListener('DOMContentLoaded', function () {
       var calendarEl = document.getElementById('attendanceCalendar');
       var selectedDate = @json($startDate) || new Date().toISOString(); 
        // Initialize FullCalendar
        var calendar = new FullCalendar.Calendar(calendarEl, {
           initialView: 'dayGridMonth',
           initialDate: selectedDate, // Set the calendar to show the selected date or default to today
           events: [
              ...@json($dailyAttendanceEvents),
              ...@json($absenceEvents),
              ...@json($holidayEvents)
           ],
        });
   
       calendar.render();
   });
</script>
@endsection