@extends('layouts.master')

@section('styles')
<!-- DataTables CSS from the official CDN (version 2.2.1) -->
<link href="https://cdn.datatables.net/2.2.1/css/dataTables.dataTables.min.css" rel="stylesheet" type="text/css">
@endsection


@section('breadcrumb')
<div class="col-sm-12 text-left">
    <h4 class="page-title mb-3">Visitor Report </h4>
    <form method="GET" action="{{ route('reports.visitor.index') }}" class="mb-4">
        <div class="row g-2 align-items-end d-flex flex-wrap">
            <div class="col-md-2">
                <label for="start_date" class="form-label">Start Date:</label>
                <input type="date" id="start_date" name="start_date" class="form-control" 
                    value="{{ request('start_date', $startDate) }}">
            </div>
            <div class="col-md-2">
                <label for="end_date" class="form-label">End Date:</label>
                <input type="date" id="end_date" name="end_date" class="form-control" 
                    value="{{ request('end_date', $endDate) }}">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">Filter</button>
            </div>
        </div>
    </form>
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
                            <th>Name</th>
                            <th>Phone</th>
                            <th>Directory</th>
                            <th>Purpose</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($visitors as $visitor)
                        <tr>
                            <td>{{$visitor->name}}</td>
                            <td>{{$visitor->phone}}</td>
                            <td>{{$visitor->directory->name}}</td>
                            <td>{{$visitor->purpose}}</td>
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