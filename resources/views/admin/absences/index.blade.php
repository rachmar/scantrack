


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
        <div class="card">
            <div class="card-body">
            <table class="table table-bordered table-striped">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Student Name</th>
                    <th>Semester</th>
                    <th>Date</th>
                    <th>Cleared</th>
                    <th>Created At</th>
                    <th>Updated At</th>
                </tr>
            </thead>
            <tbody>
                @foreach($records as $student_id => $semesters)
                    {{-- Student Row --}}
                    <tr class="table-primary fw-bold">
                        <td colspan="7">Student: {{ $semesters->first()->first()->student->fullName() ?? 'Unknown' }}</td>
                    </tr>
                    @foreach($semesters as $semester_id => $entries)
                        {{-- Semester Row --}}
                        <tr class="table-info fw-bold">
                            <td colspan="7">Semester: {{ $entries->first()->semester->name ?? 'Unknown' }}</td>
                        </tr>
                        @foreach($entries as $entry)
                            <tr>
                                <td>{{ $entry->id }}</td>
                                <td>
                                    <a href="{{route('reports.students.show', $entry->student->id)}}"> {{ $entry->student->fullName() ?? 'N/A' }} </a>
                                </td>
                                <td>{{ $entry->semester->name ?? 'N/A' }}</td>
                                <td>{{ $entry->date }}</td>
                                <td>
                                    <span class="badge {{ $entry->clear ? 'bg-success' : 'bg-danger' }}">
                                        {{ $entry->clear ? 'Yes' : 'No' }}
                                    </span>
                                </td>
                                <td>{{ $entry->created_at }}</td>
                                <td>{{ $entry->updated_at }}</td>
                            </tr>
                        @endforeach
                    @endforeach
                @endforeach
            </tbody>
        </table>
            </div>
        </div>
    </div>
</div> <!-- end col -->
</div> <!-- end row -->

@endsection

@section('scripts')
@endsection
