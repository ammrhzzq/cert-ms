@extends('layouts.app', ['activeItem' => 'dashboard'])

@section('title', 'Dashboard - Certificate Management System')

@section('content')
<h1>Overview</h1>

<div class="dashboard-cards">
    <a href="{{ route('certificates.index', ['status' => 'pending_review']) }}" class="card blue-border">
        <h3>Pending Review</h3>
        <p class="count">{{ $pendingReview }}</p>
    </a>
    <a href="{{ route('certificates.index', ['status' => 'client_verified']) }}" class="card yellow-border">
        <h3>Client Verified</h3>
        <p class="count">{{ $clientVerified }}</p>
    </a>
    <a href="{{ route('certificates.index', ['status' => 'need_revision']) }}" class="card red-border">
        <h3>Need Revision</h3>
        <p class="count">{{ $needRevision }}</p>
    </a>
    <a href="{{ route('certificates.index', ['status' => 'pending_hod_approval']) }}" class="card green-border">
        <h3>Pending HoD Approval</h3>
        <p class="count">{{ $pendingHodApproval }}</p>
    </a>
    <a href="{{ route('certificates.view')}}" class="card orange-border">
        <h3>Certificate Issued</h3>
        <p class="count">{{ $certificateIssued }}</p>
    </a>
    <div class="card gray-border">
        <h3>Number of Client</h3>
        <p class="count">{{ $clientCount }}</p>
    </div>
</div>
@endsection