@extends('layouts.app', ['activeItem' => 'clients'])

@section('title', 'Client List')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/client-index.css') }}">
@endsection

@section('content')
<h1>Client List</h1>


<!-- Success alert -->
@if(session()->has('success'))
<div class="alert alert-success">
    {{ session()->get('success') }}
</div>
@endif

<!-- Create button -->
<div class="action-button">
    <a href="{{ route('clients.create') }}" class="create-btn">
        <i class="fas fa-plus"></i> Create Client
    </a>
</div>

<!-- Client table -->
<table class="table">
    <thead>
        <tr>
            <th>Name</th>
            <th>Address</th>
            <th>Registration Date</th>
            <th>Contact Number</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        @foreach($clients as $client)
        <tr>
            <td>{{ $client->comp_name }}</td>
            <td>
                {{ $client->comp_address1 }}<br>
                {{ $client->comp_address2 }}<br>
                {{ $client->comp_address3 }}
            </td>
            <td>{{ \Carbon\Carbon::parse($client->reg_date)->format('d/m/Y') }}</td>
            <td>
                {{ $client->comp_phone1 }}<br>
                {{ $client->comp_phone2 }}
            </td>
            <td>
                <div class="action-icons">
                    <form action="{{ route('clients.destroy', ['client' => $client]) }}" method="POST" class="delete-form">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="delete-icon" title="Delete">
                            <i class="fas fa-trash"></i>
                        </button>
                    </form>

                    <a href="{{ route('clients.edit', ['client' => $client]) }}" class="edit-icon" title="Edit">
                        <i class="fas fa-pencil-alt"></i>
                    </a>
                </div>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>

<!-- Pagination - assuming you'll implement pagination later -->
<div class="pagination">
    <div class="pagination-controls">
        <span class="page-item"><a href="#" class="page-link">Previous</a></span>
        <span class="page-item active"><a href="#" class="page-link">1</a></span>
        <span class="page-item"><a href="#" class="page-link">2</a></span>
        <span class="page-item"><a href="#" class="page-link">3</a></span>
        <span class="page-item"><a href="#" class="page-link">4</a></span>
        <span class="page-item"><a href="#" class="page-link">5</a></span>
        <span class="page-item"><a href="#" class="page-link">Next</a></span>
    </div>
    <div class="results-info">Results 1 to 3 from 35</div>
</div>
</div>
@endsection