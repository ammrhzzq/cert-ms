@extends('layouts.app')

@section('content')
<div class="container-fluid template-management">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4>Template Management</h4>
                        <a href="{{  route('templates.create') }}" class="btn btn-primary">Upload New Template</a>
                    </div>

                    <div class="card-body">
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
                                        <tr class="clickable-row" data-fancybox data-type="iframe"
                                            data-src="{{ route('templates.preview', $template->id) }}" style="cursor: pointer;">
                                            <td>{{ $template->name }}</td>
                                            <td>{{ $certType }}</td>
                                            <td>{{ $template->version }}</td>
                                            <td><span class="badge badge-success">Active</span></td>
                                            <td>{{  $template->user->name ?? 'Unknown' }}</td>
                                            <td>{{  $template->created_at->format('d/m/Y') }}</td>
                                            <td>
                                                <div class="d-flex flex-wrap gap-2">
                                                    <a href="{{  route('templates.download', $activeTemplates[$certType]->id) }}" class="btn btn-sm btn-outline-info">
                                                        <i class="fa fa-download"></i>Download</a>
                                                    <a href="{{  route('templates.edit', $activeTemplates[$certType]->id) }}" class="btn btn-sm btn-outline-primary">
                                                        <i class="fa fa-edit"></i>Edit</a>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center">No certificate types found</td>
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
                                    <tr class="clickable-row" data-fancybox data-type="iframe"
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
                                                <div class="d-flex flex-wrap gap-2">
                                                    <a href="{{  route('templates.download', $template->id) }}" class="btn btn-sm btn-outline-info">
                                                        <i class="fa fa-download"></i>Download
                                                    </a>

                                                    @if (!$template->is_active)
                                                        <form action="{{ route('templates.set-active', $template->id) }}" method="POST" class="d-inline">
                                                            @csrf
                                                            <button type="submit" class="btn btn-sm btn-outline-success">
                                                                <i class="fa fa-check-circle"></i>Set Active
                                                            </button>
                                                        </form>
                                                    @endif

                                                    <a href="{{  route('templates.edit', $template->id) }}" class="btn btn-sm btn-outline-primary">
                                                        <i class="fa fa-edit"></i>Edit
                                                    </a>
                                                    <form action="{{ route('templates.destroy', $template->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this template?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                                            <i class="fa fa-trash"></i>Delete
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center">No templates found</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Initialize FancyBox for template preview popup
    $(document).ready(function(){
        // Prevent nested button clicks from tiggering row clickable-row
        $('.clickable-row a, .clickable-row button').on('click', function(e){
            e.stopPropagation();
        });

        // Fancybox already handles iframe links with data-fancybox and data-src
        $("[data-fancybox]").fancybox({
            iframe: {
                preload:false
            },
            buttons: [
                "zoom",
                "download",
                "close"
            ]
        });
    });
</script>
@endpush
@endsection

