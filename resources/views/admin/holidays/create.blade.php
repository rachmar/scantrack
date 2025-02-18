@extends('layouts.master')

@section('styles')
@endsection

@section('breadcrumb')
<div class="col-sm-6">
    <h4 class="page-title text-left">School Events</h4>
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="javascript:void(0);">Home</a></li>
        <li class="breadcrumb-item"><a href="javascript:void(0);">School Events</a></li>
    </ol>
</div>
@endsection

@section('content')
<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-body">
                <form action="{{ route('holidays.store') }}" method="POST">
                    @csrf
                    <div class="form-group">
                        <label for="name">Event Name</label>
                        <input type="text" class="form-control" id="name" name="name" value="{{ old('name') }}">
                        @error('name')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label for="date">Event Date</label>
                        <input type="date" class="form-control" id="date" name="date" value="{{ old('date') }}">
                        @error('date')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="text-right">
                        <button type="submit" class="btn btn-primary">Submit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
@endsection
