@extends('layouts.app', ['activeItem' => 'certificates'])

@section('title', 'Assign Certificate Number')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/data-entry.css') }}">
@endsection

@section('content')
<div class="container">
    <h1>Assign Certificate Number</h1>

    <div class="form-container">
        @if($errors->any())
            <div class="alert alert-danger">
                <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
                </ul>
            </div>
        @endif

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <form action="{{ route('certificates.assign-number', $cert) }}" method="POST">
            @csrf

            {{-- Show readonly fields --}}
            <div class="form-group">
                <label>Company Name</label>
                <input type="text" value="{{ $cert->comp_name }}" readonly>
            </div>

            <div class="form-group">
                <label>Certificate Type</label>
                <input type="text" value="{{ $cert->cert_type }}" readonly>
            </div>

            <div class="form-group">
                <label>ISO Number</label>
                <input type="text" value="{{ $cert->iso_num }}" readonly>
            </div>

            <div class="form-group">
                <label>Registration Date</label>
                <input type="text" value="{{ \Carbon\Carbon::parse($cert->reg_date)->format('d M Y') }}" readonly>
            </div>

            <div class="form-group">
                <label>Issue Date</label>
                <input type="text" value="TBD" readonly>
            </div>

            <div class="form-group">
                <label>Expiry Date</label>
                <input type="text" value="TBD" readonly>
            </div>

            {{-- Editable field --}}
            <div class="form-group">
                <label>Certificate Number <span class="text-danger">*</span></label>
                <input type="text" name="cert_number" placeholder="Enter Certificate Number" value="{{ old('cert_number', $cert->cert_number) }}" required>
                @error('cert_number') <div class="text-danger">{{ $message }}</div> @enderror
            </div>

            <div class="button-group">
                <a href="{{ route('certificates.index') }}" class="btn-back">Cancel</a>
                <input type="submit" value="Assign Number" />
            </div>
        </form>
    </div>
</div>
@endsection