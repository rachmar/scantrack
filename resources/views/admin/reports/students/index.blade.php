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
            <div class="col-md-3">
                <input type="text" id="student" name="name" class="form-control" placeholder="Student Name..." value="{{ request('name') }}">
            </div>
            <div class="col-md-3">
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
                <select id="course" class="form-control" name="course">
                    <option value="">-- Select Course --</option>
                </select>
            </div>
            <div class="col-md-3">
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