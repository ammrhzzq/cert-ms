@extends('layouts.app')


@section('styles')
<link rel="stylesheet" href="{{ asset('css/template-management.css') }}">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fancyapps/ui/dist/fancybox.css" />
@endsection

@section('content')
<h1>Template Management</h1>

@if (session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>  
@endif

@if (session('error'))
    <div class="alert alert-danger">
        {{ session('error') }}
    </div>
@endif

<div class="status-tabs">
    <a href="{{ route('templates.index', array_merge(request()->except(['cert_type', 'page']), ['cert_type' => null])) }}"
        class="status-tab {{ is_null(request('cert_type')) ? 'active' : '' }}">
        All
    </a>
    @foreach (['ISMS', 'PIMS', 'BCMS', 'MyCC'] as $type)
        <a href="{{ route('templates.index', array_merge(request()->except(['cert_type', 'page']), ['cert_type' => $type])) }}"
            class="status-tab {{ request('cert_type') == $type ? 'active' : '' }}">
            {{ $type }}
        </a>
    @endforeach
</div>

<h5>Current Certificate Template</h5>
<div class="table-responsive mb-4">
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Name</th>
                <th>Certificate Type</th>
                <th>Version</th>
                <th>Status</th>
                <th>Uploaded By</th>
                <th>Upload Date</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($activeTemplates as $certType => $template)
                <tr class="clickable-row" data-fancybox="pdfs" data-type="pdf"
                    data-src="{{ route('templates.preview', $template->id) }}" style="cursor: pointer;">
                    <td>{{ $template->name }}</td>
                    <td>{{ $certType }}</td>
                    <td>{{ $template->version }}</td>
                    <td><span class="badge badge-success">Active</span></td>
                    <td>{{  $template->user->name ?? 'Unknown' }}</td>
                    <td>{{  $template->created_at->format('d/m/Y') }}</td>
                    <td>
                        <div class="action-btn-group">
                            <a href="{{  route('templates.download', $activeTemplates[$certType]->id) }}" class="btn-icon btn-download">
                                <i class="fa fa-download"></i>
                                <span class="btn-text">Download</span>
                            </a>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center">No template found</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<h5>Template History</h5>
<div class="table-responsive mb-4">
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Name</th>
                <th>Certificate Type</th>
                <th>Version</th>
                <th>Status</th>
                <th>Uploaded By</th>
                <th>Upload Date</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($inactiveTemplates as $template)
            <tr class="clickable-row" data-fancybox="pdfs" data-type="pdf"
                data-src="{{ route('templates.preview', $template->id) }}" style="cursor: pointer;">
                    <td>{{  $template->name }}</td>
                    <td>{{  $template->cert_type }}</td>
                    <td>{{  $template->version }}</td>
                    <td>
                        @if ($template->is_active)
                            <span class="badge badge-success">Active</span>
                        @else
                            <span class="badge bg-secondary">Inactive</span>
                        @endif
                    </td>
                    <td>{{  $template->user->name ?? 'Unknown' }}</td>
                    <td>{{  $template->created_at->format('d/m/Y') }}</td>
                    <td>
                        <div class="action-btn-group">
                            <a href="{{  route('templates.download', $template->id) }}" class="btn-icon btn-download">
                                <i class="fa fa-download"></i>
                                <span class="btn-text">Download</span>
                            </a>

                            @if (!$template->is_active)
                                <form action="{{ route('templates.set-active', $template->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn-icon btn-active">
                                        <i class="fa fa-check-circle"></i>
                                        <span class="btn-text">Set Active</span>
                                    </button>
                                </form>
                            @endif

                            <form action="{{ route('templates.destroy', $template->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this template?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn-icon btn-delete">
                                    <i class="fa fa-trash"></i>
                                    <span class="btn-text">Delete</span>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center">No template found</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="action-button">
    <a href="{{  route('templates.create') }}" class="btn btn-primary">Upload New Template</a>
</div>

@push('scripts')
@section('scripts')
<!-- Include PDF.js for better PDF viewing experience -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.16.105/pdf.min.js"></script>
<script>
    // Set PDF.js worker path
    pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.16.105/pdf.worker.min.js';
    
    $(document).ready(function(){
        // Prevent nested button clicks from triggering row clickable-row
        $('.clickable-row .btn-icon, .clickable-row button').on('click', function(e){
            e.stopPropagation();
        });

        // Handle row clicks for PDF preview
        $('.clickable-row').on('click', function() {
            const pdfUrl = $(this).data('src');
            
            Fancybox.show([{ src: pdfUrl, type: "iframe" }], {
                groupAll: true,
                animated: false,
                dragToClose: false,
                showClass: false,
                hideClass: false,
                Toolbar: {
                    display: ["zoom", "fullscreen", "download", "close"],
                },
                iframe: {
                    preload: false,
                    css: {
                        width: '90%',
                        height: '90%'
                    }
                }
            });
        });
    });
    
</script>
@endsection
@endpush
@endsection

