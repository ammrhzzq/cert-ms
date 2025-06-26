@extends('layouts.app', ['activeItem' => 'clients'])

@section('title', 'Client List')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/index.css') }}">
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
            <th class="sortable-header">
                <a href="{{ route('clients.index', ['sort' => $currentSort == 'asc' ? 'desc' : 'asc']) }}" >
                    Name
                    @if($currentSort == 'asc')
                        <i class="fas fa-sort-up sort-icon"></i>
                    @else
                        <i class="fas fa-sort-down sort-icon"></i>
                    @endif
                </a>
            </th>
            <th>Address</th>
            <th>Contact Person</th>
            <th>Contact Number</th>
            <th>Email</th>
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
            <td>
                {{ $client->phone1_name }}<br>
                {{ $client->phone2_name }}
            </td>
            <td>
                {{ $client->comp_phone1 }}<br>
                {{ $client->comp_phone2 }}
            </td>
            <td>
                {{ $client->comp_email1 }}<br>
                {{ $client->comp_email2 }}
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
@endsection