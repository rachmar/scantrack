


@extends('layouts.master')

@section('styles')
@endsection

@section('breadcrumb')
<div class="col-sm-6">
    <h4 class="page-title text-left">Absence Records</h4>
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="javascript:void(0);">Home</a></li>
        <li class="breadcrumb-item"><a href="javascript:void(0);">Absence Records</a></li>
    </ol>
</div>
@endsection

@section('content')


<div class="row">
    <div class="col-12">

        <div class="alert alert-info" role="alert">
            <strong>Key Features:</strong>
            <ul>
                <li><span class="text-danger fw-bold">Red section:</span> Indicates absences that are <b>not cleared</b> and may require action.</li>
                <li><span class="text-success fw-bold">Green section:</span> Indicates absences that are <b>cleared</b> and resolved.</li>
                <li><b>Clear Absences Button:</b> Allows users to mark absences as cleared.</li>
                <li>Each record displays the <b>student's name, absence dates, and clearance status</b>.</li>
            </ul>
        </div>

        <div class="accordion" id="studentAccordion">
            @foreach($records as $student_id => $semesters)
                @php
                    // Check if the student has any uncleared absences
                    $hasUnclearedAbsences = false;
                    foreach ($semesters as $entries) {
                        foreach ($entries as $entry) {
                            if (!$entry->clear) {
                                $hasUnclearedAbsences = true;
                                break 2; // Exit both loops
                            }
                        }
                    }
                @endphp
                <div class="card m-0">
                    <div class="card-header {{ $hasUnclearedAbsences ? 'bg-danger' : 'bg-success' }} text-white p-2 d-flex justify-content-between align-items-center" id="heading{{ $student_id }}">
                        <h6 style="cursor: pointer;" class="m-0 p-0 cursor:pointer" data-toggle="collapse" data-target="#collapse{{ $student_id }}" aria-expanded="true" aria-controls="collapse{{ $student_id }}">
                            {{ $semesters->first()->first()->student->card_id }} - 
                            {{ $semesters->first()->first()->student->fullName() }} - 
                            {{ $semesters->first()->first()->student->course->name ?? 'Unknown' }}
                        </h6>
                        <button class="btn btn-sm btn-light text-dark">Clear Absences</button>
                    </div>
                    <div id="collapse{{ $student_id }}" class="collapse" aria-labelledby="heading{{ $student_id }}">
                        <div class="card-body">
                            <table class="table table-bordered table-striped">
                                <thead class="thead-dark">
                                    <tr>
                                        <th>ID</th>
                                        <th>Student Name</th>
                                        <th>Date</th>
                                        <th>Cleared</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($semesters as $semester_id => $entries)
                                        @foreach($entries as $entry)
                                            <tr class="{{ $entry->clear ? 'table-success' : 'table-danger' }}">
                                                <td>{{ $loop->iteration }}</td>
                                                <td>
                                                    <a class="text-dark" href="{{ route('reports.students.show', $entry->student->id) }}">{{ $entry->student->fullName() ?? 'N/A' }}</a>
                                                </td>
                                                <td>{{ $entry->date }}</td>
                                                <td>
                                                    <span class="badge {{ $entry->clear ? 'badge-success' : 'badge-danger' }} text-white">
                                                        {{ $entry->clear ? 'Yes' : 'No' }}
                                                    </span>
                                                </td>
                                            </tr>
                                        @endforeach
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>

@endsection

@section('scripts')
@endsection
