<!-- resources/views/certificates/edit.blade.php -->
@extends('layouts.app', ['activeItem' => 'certificates'])

@section('title', 'Edit Certificate')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/data-entry.css') }}">
@endsection

@section('content')

<form action="{{ route('certificates.update', ['cert' => $cert]) }}" method="POST">
    @csrf
    @method('PUT')
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



            <div class="form-row">
                <div class="form-group">
                    <label>Certificate Type</label>
                    <div class="select-container">
                        <select name="cert_type" required>
                            <option value="" disabled>Select Certificate Type</option>
                            <option value="ISMS" {{ $cert->cert_type == 'ISMS' ? 'selected' : '' }}>ISMS</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label>ISO Number</label>
                    <div class="select-container">
                        <select name="iso_num" required>
                            <option value="" disabled>Select ISO Number</option>
                            <option value="ISO/IEC 27001:2022" {{ $cert->iso_num == 'ISO/IEC 27001:2022' ? 'selected' : '' }}>ISO/IEC 27001:2022</option>
                        </select>
                    </div>
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

            <div class="form-group">
                <label>Scope</label>
                <textarea name="scope" id="scope" rows="3" placeholder="Enter scope of certification">{{ $cert->scope }}</textarea>
            </div>

             <div class="form-group">
                <label>SOA (Statement of Applicability)</label>
                <input type="text" id="soa" name="soa" placeholder="Statement of Applicability" value="{{ $cert->soa }}"></input>
            </div>

             <div class="form-group">
                <label>Registration Number</label>
                <input type="text" id="cert_number" name="cert_number" placeholder="Certification Number" value="{{ $cert->cert_number }}"></input>
            </div>

            <div class="form-column">
                <div class="form-row">
                    <div class="form-group">
                        <label>Registration Date</label>
                        <input type="date" name="reg_date" value="{{ $cert->reg_date }}">
                    </div>
                    <div class="form-group">
                        <label>Issue Date</label>
                        <input type="date" name="issue_date" value="{{ $cert->issue_date }}">
                    </div>
                    <div class="form-group">
                        <label>Expiry Date</label>
                        <input type="date" name="exp_date" value="{{ $cert->exp_date }}">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="client-container">
        <div class="form-container">
            <h1>Client Details</h1>
            <div class="form-row">
                <div class="form-column">
                    <div class="form-group">
                        <label>Contact Name 1</label>
                        <input type="text" name="phone1_name" placeholder="Contact Name" value="{{ $cert->phone1_name }}">
                    </div>
                    <div class="form-group">
                        <label>Contact Number 1</label>
                        <input type="number" name="comp_phone1" placeholder="Contact Number 1" value="{{ $cert->comp_phone1 }}">
                    </div>
                    <div class="form-group">
                        <label>Contact Email 1</label>
                        <input type="text" name="comp_email1" placeholder="Contact Email" value="{{ $cert->comp_email1 }}">
                    </div>
                </div>
                <div class="form-column">
                    <div class="form-group">
                        <label>Contact Name 2</label>
                        <input type="text" name="phone2_name" placeholder="Contact Name" value="{{ $cert->phone2_name }}">
                    </div>
                    <div class="form-group">
                        <label>Contact Number 2</label>
                        <input type="number" name="comp_phone2" placeholder="Contact Number 2" value="{{ $cert->comp_phone2 }}">
                    </div>
                    <div class="form-group">
                        <label>Contact Email 2</label>
                        <input type="text" name="comp_email2" placeholder="Contact Email" value="{{ $cert->comp_email2 }}">
                    </div>
                </div>
            </div>

            <div class="button-group">
                <a href="{{ route('certificates.index') }}" class="btn-back">Cancel</a>
                <input type="submit" value="Update" />
            </div>
        </div>
    </div>
</form>
@endsection