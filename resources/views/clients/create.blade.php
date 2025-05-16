@extends('layouts.app')

@section('title', 'Create Client')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/data-entry.css') }}">
@endsection

@section('content')
<h1>Create Client</h1>
<div class="container">
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

        <form action="{{ route('clients.store') }}" method="POST">
            @csrf
            @method('POST')

            <div class="form-group">
                <label>Company Name</label>
                <input type="text" name="comp_name" placeholder="Company Name" required>
            </div>

            <div class="form-group">
                <label>Address</label>
                <input type="text" name="comp_address1" placeholder="Address Line 1" required>
            </div>

            <div class="form-group">
                <input type="text" name="comp_address2" placeholder="Address Line 2">
            </div>

            <div class="form-group">
                <input type="text" name="comp_address3" placeholder="Address Line 3">
            </div>

            <div class="form-row">
                <div class="form-column">
                    <div class="form-group">
                        <label>Contact Number 1</label>
                        <input type="text" name="comp_phone1" placeholder="Contact Number 1">
                    </div>
                    <div class="form-group">
                        <label>Contact Person Name</label>
                        <input type="text" name="phone1_name" placeholder="Contact Person Name">
                    </div>
                </div>
                <div class="form-column">
                    <div class="form-group">
                        <label>Contact Number 2</label>
                        <input type="text" name="comp_phone2" placeholder="Contact Number 2">
                    </div>
                    <div class="form-group">
                        <label>Contact Person Name</label>
                        <input type="text" name="phone2_name" placeholder="Contact Person Name">
                    </div>
                </div>
            </div>

            <div class="button-group">
                <a href="{{ route('clients.index') }}" class="btn-back">Cancel</a>
                <input type="submit" value="Create Client" />
            </div>
        </form>
    </div>
</div>
@endsection