@extends('layouts.app', ['activeItem' => 'templates'])

@section('title', 'Certificate Templates')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/template.css') }}">
@endsection

@section('content')
<h1>Certificate Templates</h1>

<!-- Success alert -->
@if(session()->has('success'))
<div class="alert alert-success">
    {{ session()->get('success') }}
</div>
@endif

<!-- Error alert -->
@if(session()->has('error'))
<div class="alert alert-danger">
    {{ session()->get('error') }}
</div>
@endif

<!-- Filter tabs -->
<div class="tabs">
    <a href="{{ route('templates.index') }}" class="tab {{ $currentType == 'all' ? 'active' : '' }}">
        All
    </a>
    @foreach($certTypes as $type)
    <a href="{{ route('templates.index', ['type' => $type]) }}" class="tab {{ $currentType == $type ? 'active' : '' }}">
        {{ $type }}
    </a>
    @endforeach
</div>

<div class="template-container">
    <!-- Upload Card -->
    <div class="upload-container" id="uploadTrigger">
        <div class="upload-icon">
            <i class="fas fa-cloud-upload-alt"></i>
        </div>
        <div class="upload-text">
            <p>Upload new template</p>
            <p><small>PDF or DOCX</small></p>
        </div>
    </div>
    
    <!-- Template Cards -->
    @foreach($templates as $template)
    <div class="template-card">
        <div class="template-preview">
            @if($template->file_type == 'pdf')
                <div class="pdf-icon">
                    <i class="far fa-file-pdf"></i>
                </div>
            @elseif($template->file_type == 'docx')
                <div class="docx-icon">
                    <i class="far fa-file-word"></i>
                </div>
            @endif
        </div>
        <div class="template-info">
            <div class="template-name">{{ $template->cert_type }} - {{ $template->name }}</div>

            <div class="template-actions">
                <div>
                    <a href="{{ route('templates.preview', $template) }}" class="preview-icon" title="Preview" target="_blank">
                        <i class="fa-regular fa-eye"></i>
                    </a>
                    <a href="{{ Storage::url($template->file_path) }}" class="download-icon" title="Download" download>
                        <i class="fas fa-download"></i>
                    </a>
                </div>
                <div>
                    <form action="{{ route('templates.destroy', $template) }}" method="POST" class="delete-form">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="delete-icon" title="Delete">
                            <i class="fas fa-trash"></i>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @endforeach
</div>

<!-- Upload Modal -->
<div id="uploadModal" class="modal">
    <div class="modal-content">
        <span class="close-modal">&times;</span>
        <h2 class="modal-title">Upload Certificate Template</h2>
        
        <form action="{{ route('templates.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            
            <div class="form-group">
                <label>Certificate Type</label>
                <div class="select-container">
                    <select name="cert_type" required>
                        <option value="" selected disabled>Select Certificate Type</option>
                        <option value="ISMS">ISMS</option>
                        <option value="BCMS">BCMS</option>
                        <option value="PIMS">PIMS</option>
                    </select>
                </div>
            </div>
            
            <div class="form-group">
                <label>Template Version</label>
                <input type="text" name="template_name" placeholder="Enter template name" required>
            </div>
            
            <div class="form-group">
                <label>Template File</label>
                <div class="file-input-wrapper">
                    <div class="file-input-button">
                        Choose File
                    </div>
                    <input type="file" name="template_file" id="template_file" accept=".pdf,.docx" required>
                </div>
                <div class="file-name-display" id="fileName">No file chosen</div>
                <small>Supported formats: PDF, DOCX. Max size: 5MB</small>
            </div>
            
            <div class="button-group">
                <button type="button" class="btn-back" id="cancelUpload">Cancel</button>
                <input type="submit" value="Upload" />
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const uploadTrigger = document.getElementById('uploadTrigger');
        const uploadModal = document.getElementById('uploadModal');
        const closeModal = document.querySelector('.close-modal');
        const cancelUpload = document.getElementById('cancelUpload');
        const fileInput = document.getElementById('template_file');
        const fileNameDisplay = document.getElementById('fileName');
        
        // Open modal
        uploadTrigger.addEventListener('click', function() {
            uploadModal.style.display = 'block';
        });
        
        // Close modal
        closeModal.addEventListener('click', function() {
            uploadModal.style.display = 'none';
        });
        
        cancelUpload.addEventListener('click', function() {
            uploadModal.style.display = 'none';
        });
        
        // Close modal when clicking outside
        window.addEventListener('click', function(event) {
            if (event.target === uploadModal) {
                uploadModal.style.display = 'none';
            }
        });
        
        // Display selected filename
        fileInput.addEventListener('change', function() {
            if (this.files.length > 0) {
                fileNameDisplay.textContent = this.files[0].name;
            } else {
                fileNameDisplay.textContent = 'No file chosen';
            }
        });
        
        // Confirm delete
        const deleteForms = document.querySelectorAll('.delete-form');
        deleteForms.forEach(form => {
            form.addEventListener('submit', function(event) {
                if (!confirm('Are you sure you want to delete this template?')) {
                    event.preventDefault();
                }
            });
        });
    });
</script>
@endsection