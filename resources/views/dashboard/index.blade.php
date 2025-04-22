@extends('layouts.app', ['activeItem' => 'dashboard'])

@section('title', 'Dashboard - Certificate Management System')

@section('content')
<h1>Overview</h1>

<div class="dashboard-cards">
    <div class="card blue-border">
        <h3>Pending Review</h3>
        <p class="count">{{ $pendingReview }}</p>
    </div>
    <div class="card yellow-border">
        <h3>Client Verified</h3>
        <p class="count">{{ $clientVerified }}</p>
    </div>
    <div class="card red-border">
        <h3>Need Revision</h3>
        <p class="count">{{ $needRevision }}</p>
    </div>
    <div class="card green-border">
        <h3>Pending HoD Approval</h3>
        <p class="count">{{ $pendingHodApproval }}</p>
    </div>
    <div class="card orange-border">
        <h3>Certificate Issued</h3>
        <p class="count">{{ $certificateIssued }}</p>
    </div>
    <div class="card gray-border">
        <h3>Number of Client</h3>
        <p class="count">{{ $clientCount }}</p>
    </div>
</div>
@endsection