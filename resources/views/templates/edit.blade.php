@extends('layouts.app')

@section('content')
<div class="container py-5 template-management">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h4>Edit Certificate Template</h4>
                </div>
                <div class="card-body">
                    @if (session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                    @endif

                    <form action="{{ route('templates.update', $template->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="form-group row mb-3">
                            <label for="name" class="col-md-4 col-form-label text-md-right">Template Name</label>
                            <div class="col-md-6">
                                <input type="text" id="name" name="name" class="form-control" value="{{ old('name',$template->name) }}" required>
                            </div>
                        </div>

                        <div class="form-group row mb-3">
                            <label for="cert_type" class="col-md-4 col-form-label text-md-right">Certificate Type</label>
                            <div class="col-md-6">
                                <select id="cert_type" name="cert_type" class="form-control" required>
                                    <option value="">Select Certificate Type</option>
                                    @foreach($certTypes as $type)
                                        <option value="{{ $type }}" {{ old('cert_type', $template->cert_type) == $type ? 'selected' : '' }}>
                                            {{ $type }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="form-group row mb-3">
                            <label for="description" class="col-md-4 col-form-label text-md-right">Description</label>
                            <div class="col-md-6">
                                <textarea id="description" name="description" class="form-control" rows="3">{{ old('description', $template->description) }}</textarea>
                        </div>

                        <div class="form-group row mb-3">
                            <label class="col-md-4 col-form-label text-md-right">Current Template</label>
                            <div class="col-md-6">
                                <div class="current-template-preview border rounded p-2 mb-3">
                                    <a href="{{ route('templates.preview', $template->id) }}"
                                        alt="Current Template"
                                        class="img-fluid">
                                    </a>
                                    <div class="text-center mt-2">
                                        <small>Version: {{ $template->version}}</small> 
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-group row mb-3">
                            <label for="template_file" class="col-md-4 col-form-label text-md-right">Upload New Template (PDF)</label>
                            <div class="col-md-6">
                                <div class="custom-file-upload">
                                    <input type="file" id="template_file" name="template_file" class="form-control" accept="application/pdf">
                                    <small class="form-text text-muted">
                                        Leave blank if you don't want to replace the existing file.
                                    </small>
                                </div>

                                <div class="preview-container mt-3" id="previewContainer" style="display: none;">
                                    <h6>New Template Preview:</h6>
                                    <div class="pdf-review border rounded p-2">
                                        <img id="previewImage" src="" alt="New Template Preview" class="img-fluid">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-group row mb-3">
                            <div class="col-md-6 offset-md-4">
                                <div class="checkbox-container">
                                    <input class="form-check-input" type="checkbox" id="set_active" name="set_active" {{ $template->is_active ? 'checked' : '' }}>
                                    <label class="form-check-label" for="set_active">
                                        Set as Active Template
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="form-group row mb-0">
                            <div class="col-md-6 offset-md-4">
                                <a href="{{ route('templates.index') }}" class="btn btn-cancel me-2">Cancel</a>
                                <button type="submit" class="btn btn-primary">
                                    Update Template
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Initialize FancyBox for template preview popup
    $(doocument).ready(function(){
        $("[data-fancybox]").fancybox({
            buttons: [
                "zoom",
                "download",
                "close"
            ]
        });
    });

    // Preview PDF file before upload using PDF.js
    document.getElementById('template_file').addEventListener('change', function(e){
        const file = e.target.files[0];
        if(!file) return;

        // Show preview container
        document.getElementById('previewContainer').style.display = 'block';

        // For a real implementation, you could use PDF.js to generate preview
        // Here's a simple placeholder using FileReader for image preview
        if (file.type === 'application/pdf'){
            // In a real implementation, you'd use PDF.js to convert PDF to image
            // For this demo, we'll just show a placeholder
            document.getElementById('previewImage').src = '/images/pdf-preview-placeholder.png';
        }
    });
</script>
@endpush
@endsection
