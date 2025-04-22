<!-- resources/views/certificates/create.blade.php -->
@extends('layouts.app', ['activeItem' => 'create'])

@section('title', 'Create Certificate')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/data-entry.css') }}">
@endsection

@section('content')
<h1>Create Certificate</h1>
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

        <form action="{{ route('certificates.store') }}" method="POST">
            @csrf
            @method('POST')

            <div class="form-group">
                <label>Certificate Type</label>
                <div class="select-container">
                    <select name="cert_type" required>
                        <option value="" selected disabled>Select Certificate Type</option>
                        <option value="isms">ISMS</option>
                        <option value="bcms">BCMS</option>
                        <option value="pims">PIMS</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label>ISO Number</label>
                <div class="select-container">
                    <select name="iso_num" required>
                        <option value="" selected disabled>Select ISO Number</option>
                        <option value="iso27001">ISO 27001</option>
                        <option value="iso22301">ISO 22301</option>
                        <option value="iso27701">ISO 27701</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label>Company Name</label>
                <input type="text" name="comp_name" placeholder="Company Name" required>
            </div>

            <div class="form-group">
                <label>Address</label>
                <input type="text" name="comp_address1" placeholder="Address Line 1" required>
            </div>

            <div class="form-group">
                <input type="text" name="comp_address2" placeholder="Address Line 2" required>
            </div>

            <div class="form-group">
                <input type="text" name="comp_address3" placeholder="Address Line 3" required>
            </div>

            <div class="form-row">
                <div class="form-column">
                    <div class="form-group">
                        <label>Contact Number 1</label>
                        <input type="text" name="comp_phone1" placeholder="Contact Number 1" required>
                    </div>
                </div>
                <div class="form-column">
                    <div class="form-group">
                        <label>Contact Number 2</label>
                        <input type="text" name="comp_phone2" placeholder="Contact Number 2" required>
                    </div>
                </div>
            </div>

            <div class="form-row">
                <div class="form-column">
                    <div class="form-group">
                        <label>Registration Date</label>
                        <input type="date" name="reg_date" required>
                    </div>
                </div>

                <div class="form-column">
                    <div class="form-group">
                        <label>Issue Date</label>
                        <input type="date" name="issue_date" required>
                    </div>
                </div>

                <div class="form-column">
                    <div class="form-group">
                        <label>Expired Date</label>
                        <input type="date" name="exp_date" required>
                    </div>
                </div>
            </div>

            <div class="button-group">
                <button type="button" class="btn-cancel">Cancel</button>
                <input type="submit" value="Save" />
            </div>
        </form>
    </div>
</div>
@endsection