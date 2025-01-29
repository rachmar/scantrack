@extends('layouts.master')

@section('styles')
<!-- DataTables CSS from the official CDN (version 2.2.1) -->
<link href="https://cdn.datatables.net/2.2.1/css/dataTables.dataTables.min.css" rel="stylesheet" type="text/css">
@endsection

@section('breadcrumb')
<div class="col-sm-6">
    <h4 class="page-title text-left">Students</h4>
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="javascript:void(0);">Home</a></li>
        <li class="breadcrumb-item"><a href="javascript:void(0);">Students</a></li>
    </ol>
</div>
@endsection

@section('content')

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <table id="datatable" class="table table-striped table-bordered dt-responsive nowrap" style="border-collapse: collapse; border-spacing: 0; width: 100%;">
                    <thead>
                        <tr>
                            <th>Student ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Course</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($students as $student)
                        <tr>
                            <td>{{$student->card_id}}</td>
                            <td>{{$student->fullName()}}</td>
                            <td>{{$student->phone}}</td>
                            <td>{{$student->email}}</td>
                            <td>{{$student->course->name ?? 'No Course'}}</td>
                        </tr>
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
<!-- Include DataTables JS from the official CDN (version 2.2.1) -->
<script src="https://cdn.datatables.net/2.2.1/js/dataTables.min.js"></script>

<script>
    console.log(123);
    $(document).ready(function() {
        // Initialize the DataTable
        let table = new DataTable('#datatable');  
        
        console.log(table);
        });
</script>
@endsection
