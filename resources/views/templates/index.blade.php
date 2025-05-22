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
    <div class="template-card" data-id="{{ $template->id }}" data-file-type="{{ $template->file_type }}" data-file-path="{{ Storage::url($template->file_path) }}" data-name="{{ $template->name }}" data-cert-type="{{ $template->cert_type }}">
        <div class="template-preview">
            @if($template->file_type == 'pdf')
            <iframe src="{{ Storage::url($template->file_path) }}#page=1" title="{{ $template->name }}" loading="lazy"></iframe>
            @elseif($template->file_type == 'docx')
            <div class="docx-preview">
                <i class="far fa-file-word"></i>
                <div class="file-name">{{ $template->original_name }}</div>
            </div>
            @else
            <div class="preview-not-available">
                <i class="fas fa-file-alt" style="font-size: 48px; margin-bottom: 8px;"></i>
                <span>Preview not available</span>
            </div>
            @endif
        </div>
        <div class="template-info">
            <div class="template-name" title="{{ $template->name }}">{{ $template->name }} - {{ $template->cert_type }}</div>
            <div class="template-actions">
                <div>
                    <a href="#" class="preview-btn" data-file-type="{{ $template->file_type }}" data-template-id="{{ $template->id }}" title="Preview">
                        <i class="far fa-eye"></i>
                    </a>
                    <a href="{{ Storage::url($template->file_path) }}" title="Download" download>
                        <i class="fas fa-download"></i>
                    </a>
                </div>
                <div>
                    <form action="{{ route('templates.destroy', $template) }}" method="POST" class="delete-form">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="delete-icon" title="Delete" style="border: none; background: none; cursor: pointer;">
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
                <label for="cert_type">Certificate Type</label>
                <div class="select-container">
                    <select name="cert_type" id="cert_type" required>
                        <option value="" selected disabled>Select Certificate Type</option>
                        <option value="ISMS">ISMS</option>
                        <option value="BCMS">BCMS</option>
                        <option value="PIMS">PIMS</option>
                    </select>
                </div>
            </div>
            
            <div class="form-group">
                <label for="template_name">Template Name</label>
                <input type="text" name="template_name" id="template_name" placeholder="Enter template name" required>
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

<!-- DOCX Preview Modal -->
<div id="docxPreviewModal" class="modal">
    <div class="modal-content">
        <span class="close-modal" id="closeDocxModal">&times;</span>
        <div class="docx-preview-content">
            <i class="far fa-file-word"></i>
            <h3>Microsoft Word Document</h3>
            <p>Preview is not available for Word documents. Please use the download button below to view this file.</p>
            <div class="modal-actions">
                <a href="#" id="downloadDocxLink" class="btn-download">
                    <i class="fas fa-download"></i> Download File
                </a>
            </div>
        </div>
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
        
        // DOCX Preview Modal elements
        const docxPreviewModal = document.getElementById('docxPreviewModal');
        const closeDocxModal = document.getElementById('closeDocxModal');
        const downloadDocxLink = document.getElementById('downloadDocxLink');
        const previewBtns = document.querySelectorAll('.preview-btn');
        
        // Open modal
        uploadTrigger.addEventListener('click', function() {
            uploadModal.style.display = 'block';
        });
        
        // Handle preview button clicks
        previewBtns.forEach(btn => {
            btn.addEventListener('click', function(event) {
                event.preventDefault();
                const fileType = this.getAttribute('data-file-type');
                const templateCard = this.closest('.template-card');
                
                if (fileType === 'docx') {
                    // Show DOCX preview modal
                    const filePath = templateCard.getAttribute('data-file-path');
                    downloadDocxLink.href = filePath;
                    docxPreviewModal.style.display = 'block';
                } else if (fileType === 'pdf') {
                    // For PDF files, open in new tab
                    const templateId = this.getAttribute('data-template-id');
                    window.open('{{ route("templates.preview", ":id") }}'.replace(':id', templateId), '_blank');
                }
            });
        });
        
        // Close DOCX preview modal
        closeDocxModal.addEventListener('click', function() {
            docxPreviewModal.style.display = 'none';
        });
        
        // Close DOCX modal when clicking outside
        window.addEventListener('click', function(event) {
            if (event.target === docxPreviewModal) {
                docxPreviewModal.style.display = 'none';
            }
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