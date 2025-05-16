<!-- resources/views/certificates/show.blade.php -->
@extends('layouts.app', ['activeItem' => 'certificates'])

@section('title', 'Certificate Details')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/data-entry.css') }}">
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
            <a href="{{ route('certificates.index') }}" class="btn-back">Back</a>
            @if (in_array(auth()->user()->role, ['manager', 'hod']))
                <button class="confirm-btn" onclick="openConfirmModal()">Confirm Data</button>  
            @endif 
        </div>
    </div>
</div>

@php
    $canVerify = in_array(auth()->user()->role, ['manager', 'hod']);
@endphp

@if($canVerify && $cert->status !== 'client_verified')
    <div class="review-section">
        <button class="btn btn-warning" onclick="doucment.getElementById('confirmModal').style.display='block'">Review & Confirm</button>
    </div>

    <div id="confirmModal" class="modal" style="display:none;">
        <div class="modal-content">
            <h3>Review and Confirm</h3>
            <p>Are you sure you want to confirm the data? Please type <strong>CONFIRM</strong> to proceed.</p>
            <form id='confirmForm' method="POST" action="{{ route('certificates.confirm', $cert->id) }}">
                @csrf
                <input type="text" id="confirmationInput" name="confirmation_text" placeholder="Type CONFIRM to continue" required>
                <div class="modal-actions">
                    <button type="submit" class="confirm-btn">Confirm</button>
                    <button type="button" class="btn-back" onclick="closeConfirmModal()">Cancel</button>
                </div>
            </form>
        </div>
    </div>
@endif

<script>
    function openConfirmModal(){
        document.getElementById('confirmModal').style.display = 'flex';
    }

    function closeConfirmModal(){
        document.getElementById('confirmModal').style.display = 'none';
    }
</script>
@endsection
