@extends('layouts.welcome')

@section('styles')
@endsection

@section('content')
<div class="container d-flex justify-content-center align-items-center" style="height: 100vh;">
    <div class="col-md-6">
        
        @if ($qrCode && $visitor)

            <div class="d-flex justify-content-center">
                {!! $qrCode !!}
            </div>
            <div class="d-flex justify-content-center mt-4">
                <a href="data:image/svg+xml;base64,{{ base64_encode($qrCode) }}" download="{{$visitor->card_id}} {{$visitor->name}}.svg" class="btn btn-success">
                    Download QR Code
                </a>
            </div>
            <div class="d-flex justify-content-center mt-4">
                <a href="{{ route('public.visitor.index') }}" class="btn btn-primary">
                     Back To Menu
                </a>
            </div>
        
        @else

        <h3 class="text-center mb-4">Visitor Registration Form</h3>

        <div class="alert alert-info" role="alert">
            Please fill out the form below. After submission, a QR code will be generated for entry to the school premises.
        </div>

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

        @endif

    </div>
</div>
@endsection

@section('scripts')
@endsection
