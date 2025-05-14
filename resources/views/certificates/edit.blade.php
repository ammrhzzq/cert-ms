<!-- resources/views/certificates/edit.blade.php -->
@extends('layouts.app', ['activeItem' => 'certificates'])

@section('title', 'Edit Certificate')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/data-entry.css') }}">
@endsection

@section('content')
<div class="container">
    <h1>Edit Certificate</h1>

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

        <form action="{{ route('certificates.update', ['cert' => $cert]) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="form-group">
                <label>Certificate Type</label>
                <div class="select-container">
                    <select name="cert_type" required>
                        <option value="" disabled>Select Certificate Type</option>
                        <option value="ISMS" {{ $cert->cert_type == 'ISMS' ? 'selected' : '' }}>ISMS</option>
                        <option value="BCMS" {{ $cert->cert_type == 'BCMS' ? 'selected' : '' }}>BCMS</option>
                        <option value="PIMS" {{ $cert->cert_type == 'PIMS' ? 'selected' : '' }}>PIMS</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label>ISO Number</label>
                <div class="select-container">
                    <select name="iso_num" required>
                        <option value="" disabled>Select ISO Number</option>
                        <option value="ISO/IEC 27001:2022" {{ $cert->iso_num == 'ISO/IEC 27001:2022' ? 'selected' : '' }}>ISO/IEC 27001:2022</option>
                        <option value="ISO 22301:2019" {{ $cert->iso_num == 'ISO 22301:2019' ? 'selected' : '' }}>ISO 22301:2019</option>
                        <option value="ISO/IEC 27701:2019" {{ $cert->iso_num == 'ISO/IEC 27701:2019' ? 'selected' : '' }}>ISO/IEC 27701:2019</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label>Company Name</label>
                <input type="text" name="comp_name" placeholder="Company Name" value="{{ $cert->comp_name }}" required>
            </div>

            <div class="form-group">
                <label>Address</label>
                <input type="text" name="comp_address1" placeholder="Address Line 1" value="{{ $cert->comp_address1 }}" required>
            </div>

            <div class="form-group">
                <input type="text" name="comp_address2" placeholder="Address Line 2" value="{{ $cert->comp_address2 }}">
            </div>

            <div class="form-group">
                <input type="text" name="comp_address3" placeholder="Address Line 3" value="{{ $cert->comp_address3 }}">
            </div>

            <div class="form-row">
                <div class="form-column">
                    <div class="form-group">
                        <label>Contact Number 1</label>
                        <input type="text" name="comp_phone1" placeholder="Contact Number 1" value="{{ $cert->comp_phone1 }}">
                    </div>
                    <div class="form-group">
                        <label>Contact Name 1</label>
                        <input type="text" name="phone1_name" placeholder="Contact Name" value="{{ $cert->phone1_name }}">
                    </div>
                </div>
                <div class="form-column">
                    <div class="form-group">
                        <label>Contact Number 2</label>
                        <input type="text" name="comp_phone2" placeholder="Contact Number 2" value="{{ $cert->comp_phone2 }}">
                    </div>
                    <div class="form-group">
                        <label>Contact Name 2</label>
                        <input type="text" name="phone2_name" placeholder="Contact Name" value="{{ $cert->phone2_name }}">
                    </div>
                </div>
            </div>

            <div class="form-row">
                <div class="form-column">
                    <div class="form-group">
                        <label>Registration Date</label>
                        <input type="date" name="reg_date" value="{{ $cert->reg_date }}" required>
                    </div>
                </div>

                <div class="form-column">
                    <div class="form-group">
                        <label>Issue Date</label>
                        <input type="date" name="issue_date" value="{{ $cert->issue_date }}" required>
                    </div>
                </div>

                <div class="form-column">
                    <div class="form-group">
                        <label>Expired Date</label>
                        <input type="date" name="exp_date" value="{{ $cert->exp_date }}" required>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label>Status</label>
                <div class="select-container">
                    <select name="status" required>
                        <option value="pending_review" {{ $cert->status == 'pending_review' ? 'selected' : '' }}>Pending Review</option>
                        <option value="client_verified" {{ $cert->status == 'client_verified' ? 'selected' : '' }}>Client Verified</option>
                        <option value="need_revision" {{ $cert->status == 'need_revision' ? 'selected' : '' }}>Need Revision</option>
                        <option value="pending_hod_approval" {{ $cert->status == 'pending_hod_approval' ? 'selected' : '' }}>Pending HoD Approval</option>
                        <option value="certificate_issued" {{ $cert->status == 'certificate_issued' ? 'selected' : '' }}>Certificate Issued</option>
                    </select>
                </div>
            </div>

            <div class="button-group">
                <a href="{{ route('certificates.index') }}" class="btn-back">Cancel</a>
                <input type="submit" value="Update" />
            </div>
        </form>
    </div>
</div>
@endsection