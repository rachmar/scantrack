


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


<div class="col-sm-12 text-left mb-3">

    <div class="alert alert-info my-4" role="alert">
        <strong>Key Features:</strong>
        <ul>
            <li><span class="text-danger fw-bold">Red section:</span> Indicates absences that are <b>not cleared</b> and may require action.</li>
            <li><span class="text-success fw-bold">Green section:</span> Indicates absences that are <b>cleared</b> and resolved.</li>
            <li><b>Clear Absences Button:</b> Allows users to mark absences as cleared.</li>
            <li>Each record displays the <b>student's name, absence dates, and clearance status</b>.</li>
        </ul>
    </div>

    <form method="GET" action="{{ route('absences.index') }}" class="">
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
<div class="row">
    <div class="col-12">

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
                        <form action="{{ route('absences.destroy', $student_id) }}" method="POST" onsubmit="return confirm('Are you sure you want to clear absences for this student?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-light text-dark">Clear Absences</button>
                        </form>
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
<script>
    document.addEventListener("DOMContentLoaded", function () {
        const departmentDropdown = document.getElementById("department");
        const courseDropdown = document.getElementById("course");
        const semesterDropdown = document.getElementById("semester"); // Add semester dropdown
        const selectedCourse = '{{ request('course') }}'; // Get selected course from GET request
        const selectedSemester = '{{ request('semester') }}'; // Get selected semester from GET request

        // Retain selected course value on page load if available
        if (selectedCourse) {
            courseDropdown.value = selectedCourse;
        }

        // Fetch courses when department changes
        departmentDropdown.addEventListener("change", function () {
            let departmentId = this.value;
            courseDropdown.innerHTML = '<option value="">Loading...</option>';
            semesterDropdown.innerHTML = '<option value="">-- Select Semester --</option>'; // Reset semester dropdown

            if (departmentId) {
                fetch("{{ route('reports.get.courses') }}", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ department_id: departmentId })
                })
                .then(response => response.json())
                .then(data => {
                    courseDropdown.innerHTML = '<option value="">-- Select Course --</option>';
                    data.forEach(course => {
                        let option = document.createElement("option");
                        option.value = course.id;
                        option.textContent = course.name;

                        if (course.id == selectedCourse) {
                            option.selected = true;
                        }

                        courseDropdown.appendChild(option);
                    });

                    // Trigger the change event if a course is pre-selected
                    if (selectedCourse) {
                        courseDropdown.dispatchEvent(new Event('change'));
                    }
                })
                .catch(error => {
                    console.error("Error fetching courses:", error);
                    courseDropdown.innerHTML = '<option value="">Error loading courses</option>';
                });
            } else {
                courseDropdown.innerHTML = '<option value="">-- Select Course --</option>';
                semesterDropdown.innerHTML = '<option value="">-- Select Semester --</option>';
            }
        });

        // Fetch semesters when course changes
        courseDropdown.addEventListener("change", function () {
            let courseId = this.value;
            semesterDropdown.innerHTML = '<option value="">Loading...</option>';

            if (courseId) {
                fetch("{{ route('reports.get.semesters') }}", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ course_id: courseId })
                })
                .then(response => response.json())
                .then(data => {
                    semesterDropdown.innerHTML = '<option value="">-- Select Semester --</option>';
                    data.forEach(semester => {
                        let option = document.createElement("option");
                        option.value = semester.id;
                        option.textContent = semester.name;

                        if (semester.id == selectedSemester) {
                            option.selected = true;
                        }

                        semesterDropdown.appendChild(option);
                    });
                })
                .catch(error => {
                    console.error("Error fetching semesters:", error);
                    semesterDropdown.innerHTML = '<option value="">Error loading semesters</option>';
                });
            } else {
                semesterDropdown.innerHTML = '<option value="">-- Select Semester --</option>';
            }
        });

        // Trigger the change event on page load if a department is selected
        if (departmentDropdown.value) {
            departmentDropdown.dispatchEvent(new Event('change'));
        }
    });
</script>
@endsection
