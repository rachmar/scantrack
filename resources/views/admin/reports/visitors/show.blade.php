@extends('layouts.master')

@section('styles')
<!-- DataTables CSS from the official CDN (version 2.2.1) -->
<link href="https://cdn.datatables.net/2.2.1/css/dataTables.dataTables.min.css" rel="stylesheet" type="text/css">
@endsection


@section('breadcrumb')
<div class="col-sm-12 text-left">
    <a href="{{ route('reports.visitor.index') }}" class="btn btn-secondary">Go Back</a>
</div>
@endsection

@section('content')
<div class="row">
    <div class="col-12">
    <h4 class="page-title mb-3">{{ucfirst($directory->name )}} VISITOR REPORT  | {{ $startDate }} - {{ $endDate }}</h4>
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