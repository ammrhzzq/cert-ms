<!-- resources/views/certificates/show.blade.php -->
@extends('layouts.app', ['activeItem' => 'certificates'])

@section('title', 'Certificate Details')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/data-entry.css') }}">
<style>
    .detail-container {
        max-width: 800px;
        margin: 0 auto;
        padding: 20px;
        background-color: #fff;
        border-radius: 5px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }
    
    .detail-group {
        margin-bottom: 15px;
    }
    
    .detail-label {
        font-weight: bold;
        margin-bottom: 5px;
    }
    
    .detail-value {
        padding: 8px;
        background-color: #f9f9f9;
        border-radius: 4px;
    }
    
    .detail-row {
        display: flex;
        gap: 20px;
    }
    
    .detail-column {
        flex: 1;
    }
    
    .status-badge {
        display: inline-block;
        padding: 5px 10px;
        border-radius: 4px;
        font-weight: bold;
        color: white;
    }
    
    .status-pending_review { background-color: #3498db; }
    .status-client_verified { background-color: #f1c40f; }
    .status-need_revision { background-color: #e74c3c; }
    .status-pending_hod_approval { background-color: #2ecc71; }
    .status-certificate_issued { background-color: #e67e22; }
    
    .button-group {
        display: flex;
        justify-content: space-between;
        margin-top: 30px;
    }
    
    .edit-button {
        background-color: #3498db;
        color: white;
        border: none;
        padding: 10px 20px;
        border-radius: 4px;
        cursor: pointer;
        text-decoration: none;
    }
    
    .back-button {
        background-color: #95a5a6;
        color: white;
        border: none;
        padding: 10px 20px;
        border-radius: 4px;
        cursor: pointer;
        text-decoration: none;
    }
    
    .last-edited-info {
        font-size: 12px;
        color: #7f8c8d;
        text-align: right;
        margin-top: 20px;
    }
</style>
@endsection

@section('content')
<div class="container">
    <h1>Certificate Details</h1>

    <div class="detail-container">
        <div class="detail-group">
            <div class="detail-label">Certificate Type</div>
            <div class="detail-value">{{ ucfirst($cert->cert_type) }}</div>
        </div>

        <div class="detail-group">
            <div class="detail-label">ISO Number</div>
            <div class="detail-value">{{ $cert->iso_num }}</div>
        </div>

        <div class="detail-group">
            <div class="detail-label">Company Name</div>
            <div class="detail-value">{{ $cert->comp_name }}</div>
        </div>

        <div class="detail-group">
            <div class="detail-label">Address</div>
            <div class="detail-value">
                {{ $cert->comp_address1 }}<br>
                {{ $cert->comp_address2 }}<br>
                {{ $cert->comp_address3 }}
            </div>
        </div>

        <div class="detail-row">
            <div class="detail-column">
                <div class="detail-group">
                    <div class="detail-label">Contact Number 1</div>
                    <div class="detail-value">{{ $cert->comp_phone1 }}</div>
                </div>
            </div>
            <div class="detail-column">
                <div class="detail-group">
                    <div class="detail-label">Contact Number 2</div>
                    <div class="detail-value">{{ $cert->comp_phone2 }}</div>
                </div>
            </div>
        </div>

        <div class="detail-row">
            <div class="detail-column">
                <div class="detail-group">
                    <div class="detail-label">Registration Date</div>
                    <div class="detail-value">{{ \Carbon\Carbon::parse($cert->reg_date)->format('d/m/Y') }}</div>
                </div>
            </div>
            <div class="detail-column">
                <div class="detail-group">
                    <div class="detail-label">Issue Date</div>
                    <div class="detail-value">{{ \Carbon\Carbon::parse($cert->issue_date)->format('d/m/Y') }}</div>
                </div>
            </div>
            <div class="detail-column">
                <div class="detail-group">
                    <div class="detail-label">Expired Date</div>
                    <div class="detail-value">{{ \Carbon\Carbon::parse($cert->exp_date)->format('d/m/Y') }}</div>
                </div>
            </div>
        </div>

        <div class="detail-group">
            <div class="detail-label">Status</div>
            <div class="detail-value">
                <span class="status-badge status-{{ $cert->status }}">
                    {{ ucfirst(str_replace('_', ' ', $cert->status)) }}
                </span>
            </div>
        </div>

        <div class="last-edited-info">
            @if($cert->last_edited_at)
                Last edited on {{ \Carbon\Carbon::parse($cert->last_edited_at)->format('d/m/Y H:i') }} (MYT)
                @if(isset($cert->lastEditor) && $cert->lastEditor)
                    by {{ $cert->lastEditor->name }}
                @endif
            @else
                No edit history available
            @endif
        </div>

        <div class="button-group">
            <a href="{{ route('certificates.index') }}" class="back-button">Back to List</a>
            <a href="{{ route('certificates.edit', ['cert' => $cert]) }}" class="edit-button">Edit Certificate</a>
        </div>
    </div>
</div>
@endsection