@extends('layouts.app')

@section('title', 'Edit Client')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/data-entry.css') }}">
@endsection

@section('content')
<h1>Edit Client</h1>

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

        <form action="{{ route('clients.update', $client) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="form-group">
                <label>Company Name</label>
                <input type="text" name="comp_name" placeholder="Company Name" value="{{ $client->comp_name }}" required>
            </div>

            <div class="form-group">
                <label>Address Line 1</label>
                <input type="text" name="comp_address1" placeholder="Address" value="{{ $client->comp_address1 }}" required>
            </div>

            <div class="form-group">
                <label>Address Line 2</label>
                <input type="text" name="comp_address2" placeholder="Address" value="{{ $client->comp_address2 }}">
            </div>

            <div class="form-group">
                <label>Address Line 3</label>
                <input type="text" name="comp_address3" placeholder="Address" value="{{ $client->comp_address3 }}">
            </div>

            <div class="form-row">
                <div class="form-column">
                    <div class="form-group">
                        <label>Contact Number 1</label>
                        <input type="text" name="comp_phone1" placeholder="Contact Number 1" value="{{ $client->comp_phone1 }}">
                    </div>
                    <div class="form-group">
                        <label>Contact Person Name</label>
                        <input type="text" name="phone1_name" placeholder="Contact Person Name" value="{{ $client->phone1_name }}">
                    </div>
                </div>
                <div class="form-column">
                    <div class="form-group">
                        <label>Contact Number 2</label>
                        <input type="text" name="comp_phone2" placeholder="Contact Number 2" value="{{ $client->comp_phone2 }}">
                    </div>
                    <div class="form-group">
                        <label>Contact Person Name</label>
                        <input type="text" name="phone2_name" placeholder="Contact Person Name" value="{{ $client->phone2_name }}">
                    </div>
                </div>
            </div>

            <div class="button-group">
                <a href="{{ route('clients.index') }}" class="btn-back">Cancel</a>
                <input type="submit" value="Update" />
            </div>
        </form>
    </div>
</div>
@endsection