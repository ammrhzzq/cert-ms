@extends('layouts.app')

@section('content')
<div class="container py-5 template-management">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4>Template Details</h4>
                    <a href="{{ route('templates.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Templates
                    </a>
                </div>

                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h5>{{ $template->name }}</h5>
                            <p class="text-muted mb-2">Certificate Type: {{ $template->cert_type }}</p>
                            <p class="text-muted mb-2">Version: {{ $template->version }}</p>
                            <p class="text-muted mb-2">
                                Status:
                                @if($template->is_active)
                                    <span class="badge badge-success">Active</span>
                                @else
                                    <span class="badge badge-secondary">Inactive</span>
                                @endif
                            </p>
                            <p class="text-muted mb-2">Uploaded by: {{ $template->user->name ?? 'Unknown' }}</p>
                            <p class="text-muted mb-4">Upload Date: {{ $template->created_at->format('d/m/Y H:i') }}</p>

                            @if($template->description)
                                <h6>Description:</h6>
                                <p>{{ $template->description }}</p>
                            @endif

                            <div class="template-actions mt-4 d-flex flex-wrap gap-2">
                                <a href="{{ route('templates.download', $template->id) }}" class="btn btn-info flex-fill">
                                    <i class="fas fa-download"></i> Download Template
                                </a>
                                <a href="{{ route('templates.edit', $template->id) }}" class="btn btn-primary flex-fill">
                                    <i class="fas fa-edit"></i> Edit Template
                                </a>

                                @if(!$template->is_active)
                                    <form action="{{ route('templates.set-active', $template->id) }}" method="POST" class="flex-fill">
                                        @csrf
                                        <button type="submit" class="btn btn-success w-100">
                                            <i class="fas fa-trash"></i> Set as Active
                                        </button>
                                    </form>

                                    <form action="{{ route('templates.destroy', $template->id) }}" method="POST" class="flex-fill"
                                        onsubmit="return confirm('Are you sure you want to delete this template?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger w-100">
                                            <i class="fas fa-trash"></i> Delete Template
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="template-full-preview border rounded p-3">
                                <h6 class="text-center mb-3">Template Preview</h6>
                                <a href="{{ route('templates.preview', $template->id) }}"
                                    data-fancybox
                                    data-caption="{{ $template->name }} (Version {{ $template->version }})">
                                    <img src="{{ route('templates.preview', $template->id) }}"
                                        alt="Template Preview"
                                        class="img-fluid rounded">
                                </a>
                            </div>
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
    $(document).ready(function() {
        $("[data-fancybox]").fancybox({
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