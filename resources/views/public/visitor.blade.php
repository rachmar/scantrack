@extends('layouts.welcome')

@section('styles')
<style>
    .logo-container {
        text-align: center;
    }
    .logo-container img {
        max-width: 150px;
    }
</style>
@endsection

@section('content')
<div class="container d-flex justify-content-center align-items-center" style="height: 100vh;">
    <div class="col-md-6">
        
        <!-- Logo -->
        <div class="logo-container mb-3">
            <img src="{{ URL::asset('assets/images/logo.png ') }}" alt="School Logo">
        </div>


        @if(session('success'))
            <div class="alert alert-success" role="alert">
                {{ session('success') }}
            </div>
        @endif
        <h3 class="text-center my-4">Visitor Registration Form</h3>

        @if($errors->any())
            <div class="alert alert-danger" role="alert">
            <ul class="my-2">
                @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
            </div>
        @endif

        <form action="{{ route('public.visitor.store') }}" method="POST">
            @csrf
            <div class="form-group">
            <label for="name">Name</label>
            <input type="text" class="form-control" id="name" name="name" value="{{ old('name') }}" >
            </div>
            <div class="form-group">
            <label for="phone">Phone</label>
            <input type="text" maxlength="11" class="form-control" id="phone" name="phone" value="{{ old('phone') }}" >
            </div>
            <div class="form-group">
            <label for="directory_id">Directory</label>
            <select class="form-control" id="directory_id" name="directory_id">
                <option value="">-- Select Directory --</option>
                @foreach ($directories as $directory)
                    <option value="{{ $directory->id }}" {{ old('directory_id') == $directory->id ? 'selected' : '' }}>
                        {{ $directory->name }}
                    </option>
                @endforeach
            </select>
            </div>
            <div class="form-group">
            <label for="purpose">Purpose</label>
            <textarea class="form-control" id="purpose" name="purpose" rows="10">{{ old('purpose') }}</textarea>
            </div>
            <button type="submit" class="btn btn-primary btn-block">Submit</button>
        </form>

    </div>
</div>
@endsection

@section('scripts')
@endsection
