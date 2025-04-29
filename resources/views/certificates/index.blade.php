@extends('layouts.app', ['activeItem' => 'certificates'])

@section('title', 'Status & Action - Certificate Management System')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/index.css') }}">
@endsection

@section('content')
<h1>Status & Action</h1>

<!-- Success alert -->
@if(session()->has('success'))
<div class="alert alert-success">
    {{ session()->get('success') }}
</div>
@endif

<div class="actions-bar">
    <div class="search-container">
        <form action="{{ route('certificates.index') }}" method="GET" class="search-form">
            <i class="fas fa-search search-icon"></i>
            <input type="text" name="search" class="search-input" placeholder="Search for certificate" value="{{ request('search') }}">
            <button type="submit" class="search-submit" style="display: none;">Search</button>
        </form>
        <button class="filter-btn" id="filterToggle">
            <i class="fas fa-filter"></i> Filter
        </button>
    </div>

    <div class="action-button">
        <a href="{{ route('certificates.create') }}" class="create-btn">
            <i class="fas fa-plus"></i> Create Certificate
        </a>
    </div>
</div>

<!-- Filter panel (hidden by default) -->
<div class="filter-panel" id="filterPanel" style="display: none;">
    <form action="{{ route('certificates.index') }}" method="GET">
        <div class="filter-row">
            <div class="filter-group">
                <label for="cert_type">Certificate Type:</label>
                <select name="cert_type" id="cert_type">
                    <option value="all">All Types</option>
                    @foreach($certTypes as $type)
                    <option value="{{ $type }}" {{ request('cert_type') == $type ? 'selected' : '' }}>{{ $type }}</option>
                    @endforeach
                </select>
            </div>

            <div class="filter-group">
                <label for="iso_num">ISO Number:</label>
                <select name="iso_num" id="iso_num">
                    <option value="all">All ISO Numbers</option>
                    @foreach($isoNumbers as $iso)
                    <option value="{{ $iso }}" {{ request('iso_num') == $iso ? 'selected' : '' }}>{{ $iso }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="filter-row">
            <div class="filter-group">
                <label for="comp_name">Company Name:</label>
                <select name="comp_name" id="comp_name">
                    <option value="all">All Companies</option>
                    @foreach($companyNames as $name)
                    <option value="{{ $name }}" {{ request('comp_name') == $name ? 'selected' : '' }}>{{ $name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="filter-group">
                <label for="date_type">Date Field:</label>
                <select name="date_type" id="date_type">
                    <option value="reg_date" {{ request('date_type') == 'reg_date' ? 'selected' : '' }}>Registration Date</option>
                    <option value="issue_date" {{ request('date_type') == 'issue_date' ? 'selected' : '' }}>Issue Date</option>
                    <option value="exp_date" {{ request('date_type') == 'exp_date' ? 'selected' : '' }}>Expiry Date</option>
                </select>

                <select name="date_value" id="date_value">
                    <option value="all">All Dates</option>
                    @if(request('date_type') == 'issue_date' || request('date_type') == '')
                    @foreach($issueDates as $date)
                    <option value="{{ $date }}" {{ request('date_value') == $date ? 'selected' : '' }}>
                        {{ \Carbon\Carbon::parse($date)->format('d/m/Y') }}
                    </option>
                    @endforeach
                    @elseif(request('date_type') == 'exp_date')
                    @foreach($expDates as $date)
                    <option value="{{ $date }}" {{ request('date_value') == $date ? 'selected' : '' }}>
                        {{ \Carbon\Carbon::parse($date)->format('d/m/Y') }}
                    </option>
                    @endforeach
                    @else
                    @foreach($regDates as $date)
                    <option value="{{ $date }}" {{ request('date_value') == $date ? 'selected' : '' }}>
                        {{ \Carbon\Carbon::parse($date)->format('d/m/Y') }}
                    </option>
                    @endforeach
                    @endif
                </select>
            </div>
        </div>

        <div class="filter-actions">
            <button type="submit" class="apply-filter-btn">Apply Filters</button>
            <a href="{{ route('certificates.index') }}" class="reset-filter-btn">Reset</a>
        </div>
    </form>
</div>

<!-- Certificate table -->
<table class="table">
    <thead>
        <tr>
            <th>Certificate</th>
            <th>Last Edited</th>
            <th>Status</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        @foreach($certs as $cert)
        <tr>
            <td>{{ $cert->cert_type }}-{{ $cert->comp_name }}</td>

            <td>
                @if($cert->last_edited_at)
                {{ \Carbon\Carbon::parse($cert->last_edited_at)->format('d/m/Y H:i') }}<br>
                @if($cert->lastEditor)
                by {{ $cert->lastEditor->name }}
                @endif
                @else
                Not edited
                @endif
            </td>

            <td>{{ ucfirst(str_replace('_', ' ', $cert->status)) }}</td>
            <td>
                <div class="action-icons">
                    <a href="{{ route('certificates.show', ['cert' => $cert]) }}" class="view-icon" title="View">
                        <i class="fa-regular fa-eye"></i>
                    </a>

                    <a href="{{ route('certificates.edit', ['cert' => $cert]) }}" class="edit-icon" title="Edit">
                        <i class="fas fa-pencil-alt"></i>
                    </a>

                    <form action="{{ route('certificates.destroy', ['cert' => $cert]) }}" method="POST" class="delete-form">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="delete-icon" title="Delete">
                            <i class="fas fa-trash"></i>
                        </button>
                    </form>

                </div>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const filterToggle = document.getElementById('filterToggle');
        const filterPanel = document.getElementById('filterPanel');

        filterToggle.addEventListener('click', function() {
            if (filterPanel.style.display === 'none') {
                filterPanel.style.display = 'block';
            } else {
                filterPanel.style.display = 'none';
            }
        });

        // Add event listener for date field change
        const dateTypeSelect = document.getElementById('date_type');
        const dateValueSelect = document.getElementById('date_value');

        dateTypeSelect.addEventListener('change', function() {
            // Submit the form to refresh the page with the new date type
            this.form.submit();
        });
    });
</script>
@endsection