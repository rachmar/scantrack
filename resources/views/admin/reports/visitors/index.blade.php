@extends('layouts.master')

@section('styles')
@endsection

@section('breadcrumb')
<div class="col-sm-12 text-left">
    <h4 class="page-title mb-3">Overall Visitor Report</h4>
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
        <h3 class="text-uppercase">Visitor Directory Overview</h3>
        <p class="text-muted">This section provides a comprehensive overview of visitors by department. It highlights the total number of visitors in each department, helping to evaluate departmental engagement and track visitor trends.</p>
    </div>
    @foreach($visitorPerDirectories as $visitorPerDirectoryKey => $visitorPerDirectoryValue)
        <div class="col-xl-2 col-md-3 d-flex mb-4">
            <div class="card mini-stat bg-primary text-white flex-fill">
                <div class="card-body">
                    <div class="mb-4">
                        <div class="float-left mini-stat-img mr-4">
                            <i class="fas fa-chalkboard-teacher fa-2x"></i>
                        </div>
                        <h5 class="font-16 text-uppercase mt-0 text-white">{{ $visitorPerDirectoryKey }}</h5>
                        <h4 class="font-500">{{ $visitorPerDirectoryValue }} visits</h4>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</div>

<div class="row">

    <div class="col-6">
        <div class="card bg-light">
            <div class="card-body">
                <h4 class="mb-2">Visitor Directory Attendance</h4>
                <p class="text-muted mb-4">TThis chart visualizes the attendance distribution by visitor, giving insights into visitor presence across various categories. It helps assess the participation levels for each category during the current period.</p>
                <canvas id="visitorAttendanceChart"></canvas>
            </div>
        </div>
    </div>

</div>
@endsection

@section('scripts')
<script src="{{ URL::asset('assets/js/chart.min.js') }}"></script>
<script>
    // Function to generate a random color
    function getRandomColor() {
        const letters = '0123456789ABCDEF';
        let color = '#';
        for (let i = 0; i < 6; i++) {
            color += letters[Math.floor(Math.random() * 16)];
        }
        return color;
    }

    var ctx1 = document.getElementById('visitorAttendanceChart').getContext('2d');
    var courseAttendanceChart = new Chart(ctx1, {
        type: 'bar',
        data: {
            labels: {!! json_encode($chartData['visitorPerDirectoryLabels']) !!},
            datasets: [{
                label: 'Distinct Attendance Days per Course',
                data: {!! json_encode($chartData['visitorPerDirectoryValues']) !!},
                backgroundColor: function() {
                    const color = getRandomColor(); 
                    return color;
                }               
            }]
        },
        options: {
            responsive: true,
            hover: {
                mode: null
            },
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
</script>
@endsection