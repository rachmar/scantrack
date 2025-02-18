@extends('layouts.master')

@section('styles')
<!-- DataTables CSS from the official CDN (version 2.2.1) -->
<link href="https://cdn.datatables.net/2.2.1/css/dataTables.dataTables.min.css" rel="stylesheet" type="text/css">
@endsection

@section('breadcrumb')
<div class="col-sm-6">
    <h4 class="page-title text-left">Semesters</h4>
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="javascript:void(0);">Home</a></li>
        <li class="breadcrumb-item"><a href="javascript:void(0);">Semesters</a></li>
    </ol>
</div>
@endsection

@section('content')

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <a href="{{ route('semesters.create') }}" class="btn btn-primary float-right mb-3">Add Semester</a>
                <table id="datatable" class="table table-striped table-bordered dt-responsive nowrap" style="border-collapse: collapse; border-spacing: 0; width: 100%;">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Start Date</th>
                            <th>End Date</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($semesters as $semester)
                        <tr>
                            <td>{{$semester->name}}</td>
                            <td>{{$semester->start_date}}</td>
                            <td>{{$semester->end_date}}</td>
                            <td>
                                <form action="{{ route('semesters.destroy', $semester->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this semester?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                                </form>
                            </td>
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
<script src="https://cdn.datatables.net/2.2.1/js/dataTables.min.js"></script>

<script>
    $(document).ready(function() {
        let table = new DataTable('#datatable');  
    });
</script>
@endsection
