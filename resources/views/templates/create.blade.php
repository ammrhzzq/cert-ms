@extends('layouts.app')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/template-management.css') }}">
@endsection

@section('content')
<div class="container py-5 template-management">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h4>Upload Certificate Template</h4>
                </div>
                <div class="card-body">
                    @if($errors->any())
                    <div class="alert alert-danger">
                        <ul>
                            @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                    @endif
                    <form action="{{ route('templates.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="form-group row mb-3">
                            <label for="cert_type" class="col-md-4 col-form-label text-md-right">Certificate Type</label>
                            <div class="col-md-6">
                                <select id="cert_type" name="cert_type" class="form-control" required>
                                    <option value="">Select Certificate Type</option>
                                    @foreach (['ISMS', 'PIMS', 'BCMS', 'MyCC'] as $type)
                                        <option value="{{ $type }}" {{ old('cert_type') == $type ? 'selected' : '' }}>
                                            {{ $type }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="form-group row mb-3">
                            <label for="template_file" class="col-md-4 col-form-label text-md-right">Upload Template (PDF)</label>
                            <div class="col-md-6">
                                <div class="custom-file-upload">
                                    <input type="file" id="template_file" name="template_file" accept="application/pdf" required>
                                    <small class="form-text text-muted">Maximum file size: 2MB</small>
                                </div>
                            </div>
                        </div>

                        <div class="form-group row mb-3">
                            <div class="col-md-6 offset-md-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="set_active" name="set_active" {{ old('set_active') ? 'checked' : '' }}>
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
                                    Upload Template
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
    // Preview PDF file before upload using PDF.js
    document.getElementById('template_file').addEventListener('change', function(e){
        const file = e.target.files[0];
        if (!file) return;

        // Show preview container
        document.getElementById('previewContainer').style.display = 'block';

        // For a real implementation, you could use PDF.js to generate preview
        // Here's a simple placeholder using FileReader for image preview
        if (file.type === 'application/pdf') {
            // In a real implementation, you'd use PDF.js to convert PDF to image
            // For this demo, we'll just show a placeholder
            document.getElementById('previewImage').src = '/images/pdf-preview-placeholder.png';
            
        }

        if (file.size > 2 * 1024 * 1024) {
            alert('File size exceeds 2MB limit.');
            e.target.value = ''; // Clear the file input
            return;
        }
    });
</script>
@endpush
@endsection