@extends('layouts.master')
@section('styles')
@endsection

@section('breadcrumb')

@endsection

@section('content')
<div class="container mt-5">
    <h2 class="mb-4">Search Student</h2>
    <form action="{{ route('reports.students.index') }}" method="GET">
        <div class="row mb-3">
            <div class="col-md-6">
                <input type="text" id="student" name="name" class="form-control" placeholder="Student Name..." value="{{ request('name') }}">
            </div>
            <div class="col-md-4">
                <select id="courseFilter" name="course_id" class="form-control">
                    <option value="">Select Course and Department</option>
                    @foreach($departments as $department)
                        <optgroup label="{{ $department->name }}">
                            @foreach($department->courses as $c)
                                <option value="{{ $c->id }}" {{ request('course_id') == $c->id ? 'selected' : '' }}>{{ $c->slug }} - {{$c->name }}</option>
                            @endforeach
                        </optgroup>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">Search</button>
            </div>
        </div>
    </form>
    <br/>
    <div class="row">
        <div class="col-12">
            @if($students->isNotEmpty())
                <table class="table">
                    <thead>
                        <tr>
                            <th class="text-center">Student ID</th>
                            <th class="text-center">Name</th>
                            <th class="text-center">Course | Department</th>
                            <th class="text-center">View Report</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($students as $student)
                            <tr>
                                <td class="text-center">{{ $student->card_id }}</td>
                                <td class="text-center">{{ $student->fullName() }}</td>
                                <td class="text-center">{{ $student->course->name }} | {{ $student->course->department->name }}</td>
                                <td class="text-center">
                                    <a  href="{{route('reports.students.show', ['id' => $student->id]) }}" class="btn btn-info">View Report</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <p>No students found.</p>
            @endif
        </div>
    </div>
</div>
@endsection

@section('scripts')
@endsection