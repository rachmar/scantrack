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
            <input type="text" id="student_id" name="student_id" class="form-control">
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
   <div class="col-lg-6">
      <div class="card">
         <div class="card-body">
            @if (isset($student->id))
            <h4 class="mt-0 header-title">Name: {{$student->fullName()}} |  {{$student->course->name}} </h4>
            <h3 class="mt-0 header-title mb-4">Schedule: {{$student->mappedSchedule()}}</h3>
            @endif
            <hr/>
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
                           <h5 class="font-16 text-uppercase mt-0 text-white">Total Student School Days</h5>
                           <h4 class="font-500">{{ $numOfSchoolDays }}</h4>
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
                           <h4 class="font-500">{{ $numOfStudentAttendance }}</h4>
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
                           <h5 class="font-16 text-uppercase mt-0 text-white">Total Student Absent</h5>
                           <h4 class="font-500">{{ $numOfAbsences }}</h4>
                        </div>
                     </div>
                  </div>
               </div>
            </div>
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