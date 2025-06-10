@extends('layouts.app', ['activeItem' => 'templates'])

@section('title', 'Certificate Templates')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/template.css') }}">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fancyapps/ui/dist/fancybox.css" />
<script src="https://cdn.jsdelivr.net/npm/@fancyapps/ui/dist/fancybox.umd.js"></script>
@endsection

@section('content')
<h1>Template List</h1>

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
    @php
    $fileExtension = pathinfo($template->file_path, PATHINFO_EXTENSION);
    @endphp
    <div class="template-card" data-id="{{ $template->id }}" data-file-type="{{ strtolower($fileExtension) }}" data-file-path="{{ Storage::url($template->file_path) }}" data-name="{{ $template->name }}" data-cert-type="{{ $template->cert_type }}">

        <div class="template-preview">
            @if(strtolower($fileExtension) == 'pdf')
            <iframe src="{{ Storage::url($template->file_path) }}#page=1" title="{{ $template->name }}" loading="lazy"></iframe>
            @elseif(strtolower($fileExtension) == 'docx')
            <div class="docx-preview">
                <i class="far fa-file-word"></i>
                <div class="file-name">{{ $template->name }}.{{ $fileExtension }}</div>
            </div>
            @else
            <div class="preview-not-available">
                <i class="fas fa-file-alt" style="font-size: 48px; margin-bottom: 8px;"></i>
                <span>Preview not available</span>
            </div>
            @endif
        </div>
        <div class="template-info">
            @if($template->is_active)
            <div class="badge">
                <div class="green-dot" title="Active"></div>
            </div>
            @else
            <div class="badge">
                <div class="red-dot" title="Inactive"></div>
            </div>
            @endif
            <div class="template-header">
                <div class="template-meta">
                    <span class="cert-type">{{ $template->cert_type }}</span>
                    @if($template->version)
                    <span class="version">v{{ $template->version }}</span>
                    @endif
                </div>
                @if($template->description)
                <div class="template-description" title="{{ $template->description }}">
                    {{ Str::limit($template->description, 50) }}
                </div>
                @endif
            </div>
            <div class="template-actions">
                <div class="action-group">
                    <a href="#" class="preview-btn"
                        data-template-id="{{ $template->id }}"
                        data-template-name="{{ $template->cert_type }} - {{ $template->name }}"
                        data-file-type="{{ strtolower($fileExtension) }}"
                        data-preview-url="{{ Storage::url($template->file_path) }}"
                        title="Preview">
                        <i class="far fa-eye"></i>
                    </a>
                    <form action="{{ route('templates.toggle', $template) }}" method="POST" class="toggle-form" style="display: inline;">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="toggle-active-btn" title="{{ $template->is_active ? 'Deactivate' : 'Activate' }}">
                            <i class="fas {{ $template->is_active ? 'fa-toggle-on' : 'fa-toggle-off' }}"></i>
                        </button>
                    </form>
                </div>
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
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label for="name">Template Name</label>
                <input type="text" name="name" id="name" placeholder="Enter template name" value="{{ old('name') }}" required>
                @error('name')
                <div class="error-message">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="description">Description (Optional)</label>
                <textarea name="description" id="description" placeholder="Enter template description" rows="3">{{ old('description') }}</textarea>
                @error('description')
                <div class="error-message">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="version">Version (Optional)</label>
                <input type="text" name="version" id="version" placeholder="e.g., 1.0, 2.1" value="{{ old('version', '1.0') }}">
                @error('version')
                <div class="error-message">{{ $message }}</div>
                @enderror
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
                @error('template_file')
                <div class="error-message">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label class="checkbox-label">
                    <input type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active') ? 'checked' : '' }}>
                    <span class="checkmark"></span>
                    Set as active template for this certificate type
                </label>
                <small>Note: Setting this as active will deactivate other templates of the same type.</small>
            </div>

            <div class="button-group">
                <button type="button" class="btn-back" id="cancelUpload">Cancel</button>
                <input type="submit" value="Upload" />
            </div>
        </form>
    </div>
</div>

<!-- Template Preview Modal-->
<div id="templatePreviewModal" class="modal">
    <div class="modal-content">
        <span class="close-modal" id="closeTemplateModal">&times;</span>
        <div class="certificate-preview-content">
            <div class="preview-header">
                <h3 id="templatePreviewTitle">Template Preview</h3>
                <div class="modal-actions">
                    <a href="#" id="openTemplateNewTab" class="btn-newtab" target="_blank">
                        <i class="fas fa-external-link-alt"></i> Open in New Tab
                    </a>
                </div>
            </div>
            <div class="preview-container">
                <iframe id="templatePreviewFrame" src=""></iframe>
            </div>
        </div>
    </div>
</div>

<!-- DOCX Preview Modal -->
<div id="docxPreviewModal" class="modal">
    <div class="modal-content">
        <span class="close-modal" id="closeDocxModal">&times;</span>
        <div class="certificate-preview-content">
            <div class="preview-header">
                <h3 id="docxPreviewTitle">Template Preview</h3>
                <div class="modal-actions">
                    <a href="#" id="downloadDocxLink" class="btn-download">
                        <i class="fas fa-download"></i> Download File
                    </a>
                </div>
            </div>
            <div class="preview-container" style="text-align: center; padding: 2rem; color: #666;">
                <i class="far fa-file-word" style="font-size: 48px; margin-bottom: 1rem;"></i>
                <h4>Microsoft Word Document</h4>
                <p>Preview is not available for Word documents.<br>Click "Download File" to view this template.</p>
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

        // Template Preview Modal elements
        const templatePreviewModal = document.getElementById('templatePreviewModal');
        const closeTemplateModal = document.getElementById('closeTemplateModal');
        const templatePreviewFrame = document.getElementById('templatePreviewFrame');
        const templatePreviewTitle = document.getElementById('templatePreviewTitle');
        const openTemplateNewTab = document.getElementById('openTemplateNewTab');

        // DOCX Preview Modal elements
        const docxPreviewModal = document.getElementById('docxPreviewModal');
        const closeDocxModal = document.getElementById('closeDocxModal');
        const downloadDocxLink = document.getElementById('downloadDocxLink');
        const docxPreviewTitle = document.getElementById('docxPreviewTitle');

        const previewBtns = document.querySelectorAll('.preview-btn');

        // Open upload modal
        uploadTrigger.addEventListener('click', function() {
            uploadModal.style.display = 'block';
        });

        // Handle preview button clicks
        previewBtns.forEach(btn => {
            btn.addEventListener('click', function(event) {
                event.preventDefault();
                const templateId = this.getAttribute('data-template-id');
                const templateName = this.getAttribute('data-template-name');
                const fileType = this.getAttribute('data-file-type');
                const previewUrl = this.getAttribute('data-preview-url');

                if (fileType === 'pdf') {
                    // Show PDF preview modal (same as certificate)
                    templatePreviewTitle.textContent = `${templateName}`;
                    templatePreviewFrame.src = previewUrl;
                    openTemplateNewTab.href = previewUrl;
                    templatePreviewModal.style.display = 'block';
                } else if (fileType === 'docx') {
                    // Show DOCX preview modal
                    docxPreviewTitle.textContent = `${templateName}`;
                    downloadDocxLink.href = previewUrl;
                    docxPreviewModal.style.display = 'block';
                }
            });
        });

        // Close template preview modal
        closeTemplateModal.addEventListener('click', function() {
            templatePreviewModal.style.display = 'none';
            templatePreviewFrame.src = ''; // Clear iframe to stop loading
        });

        // Close DOCX preview modal
        closeDocxModal.addEventListener('click', function() {
            docxPreviewModal.style.display = 'none';
        });

        // Close upload modal
        closeModal.addEventListener('click', function() {
            uploadModal.style.display = 'none';
        });

        cancelUpload.addEventListener('click', function() {
            uploadModal.style.display = 'none';
        });

        // Close modals when clicking outside
        window.addEventListener('click', function(event) {
            if (event.target === uploadModal) {
                uploadModal.style.display = 'none';
            }
            if (event.target === templatePreviewModal) {
                templatePreviewModal.style.display = 'none';
                templatePreviewFrame.src = ''; // Clear iframe to stop loading
            }
            if (event.target === docxPreviewModal) {
                docxPreviewModal.style.display = 'none';
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

        // Confirm toggle active status
        const toggleForms = document.querySelectorAll('.toggle-form');
        toggleForms.forEach(form => {
            form.addEventListener('submit', function(event) {
                const button = this.querySelector('.toggle-active-btn');
                const isActive = button.title.includes('Deactivate');
                const action = isActive ? 'deactivate' : 'activate';

                if (!confirm(`Are you sure you want to ${action} this template?`)) {
                    event.preventDefault();
                }
            });
        });

        // Handle iframe load errors for preview thumbnails
        const previewIframes = document.querySelectorAll('.template-preview iframe');
        previewIframes.forEach(iframe => {
            iframe.addEventListener('error', function() {
                console.error('Failed to load template preview thumbnail');
                this.style.display = 'none';
                this.parentNode.innerHTML = '<div class="preview-not-available"><i class="fas fa-file-alt" style="font-size: 48px; margin-bottom: 8px;"></i><span>Preview not available</span></div>';
            });
        });

        // Handle iframe load errors for modal
        templatePreviewFrame.addEventListener('error', function() {
            console.error('Failed to load template preview');
            this.style.display = 'none';
            this.parentNode.innerHTML = '<div style="text-align: center; padding: 2rem; color: #666;"><i class="fas fa-exclamation-triangle" style="font-size: 48px; margin-bottom: 1rem;"></i><br>Preview not available<br><small>Click "Open in New Tab" to view the template</small></div>';
        });
    });
</script>
@endsection