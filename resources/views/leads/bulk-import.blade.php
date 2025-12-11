@extends('layouts.app')

@section('title', 'Bulk Import Leads')

@section('extra-css')
<style>
    .upload-area {
        border: 2px dashed #cbd5e0;
        border-radius: 12px;
        padding: 3rem 2rem;
        text-align: center;
        transition: all 0.3s ease;
        background: #f8fafc;
    }

    .upload-area:hover {
        border-color: #2563eb;
        background: #eff6ff;
    }

    .upload-area.dragover {
        border-color: #10b981;
        background: #d1fae5;
    }

    .progress-container {
        display: none;
        margin-top: 2rem;
    }

    .progress {
        height: 30px;
        border-radius: 8px;
        overflow: hidden;
    }

    .progress-bar {
        transition: width 0.3s ease;
        font-weight: 600;
        font-size: 14px;
    }

    .import-stats {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1rem;
        margin-top: 1.5rem;
    }

    .stat-box {
        padding: 1rem;
        border-radius: 8px;
        text-align: center;
    }

    .stat-box.total {
        background: #dbeafe;
        color: #1e40af;
    }

    .stat-box.success {
        background: #d1fae5;
        color: #065f46;
    }

    .stat-box.failed {
        background: #fee2e2;
        color: #991b1b;
    }

    .stat-number {
        font-size: 2rem;
        font-weight: 700;
        margin-bottom: 0.25rem;
    }

    .stat-label {
        font-size: 0.875rem;
        font-weight: 600;
        text-transform: uppercase;
    }

    .error-list {
        max-height: 400px;
        overflow-y: auto;
        margin-top: 1rem;
    }

    .error-item {
        padding: 0.75rem;
        background: #fef2f2;
        border-left: 3px solid #ef4444;
        margin-bottom: 0.5rem;
        border-radius: 4px;
    }
</style>
@endsection

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="page-title-box d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="page-title mb-1">Bulk Import Leads</h4>
                    <p class="text-muted mb-0">Import multiple leads at once using Excel file</p>
                </div>
                <div>
                    <a href="{{ route('leads.index') }}" class="btn btn-secondary">
                        <i class="las la-arrow-left me-1"></i> Back to Leads
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Upload Section -->
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="las la-cloud-upload-alt me-2"></i>Upload Excel/CSV File
                    </h5>
                </div>
                <div class="card-body">
                    <!-- Instructions -->
                    <div class="alert alert-info mb-4">
                        <h6 class="alert-heading"><i class="las la-info-circle me-2"></i>Instructions</h6>
                        <ol class="mb-0 ps-3">
                            <li>Download the Excel template file below</li>
                            <li>Fill in your lead data following the format</li>
                            <li>Remove the example row (John Doe) before uploading</li>
                            <li>Save the file and upload it here</li>
                            <li>Wait for the import to complete</li>
                        </ol>
                    </div>

                    <!-- Download Template Button -->
                    <div class="text-center mb-4">
                        <a href="{{ route('leads.download-template') }}" class="btn btn-success btn-lg">
                            <i class="las la-download me-2"></i>Download Excel Template
                        </a>
                    </div>

                    <!-- Upload Area -->
                    <form id="uploadForm" enctype="multipart/form-data">
                        @csrf
                        <div class="upload-area" id="uploadArea">
                            <i class="las la-cloud-upload-alt" style="font-size: 4rem; color: #cbd5e0;"></i>
                            <h5 class="mt-3 mb-2">Drag & Drop File Here</h5>
                            <p class="text-muted mb-3">or</p>
                            <input type="file" id="csvFile" name="csv_file" accept=".csv,.xlsx,.xls" class="d-none">
                            <button type="button" class="btn btn-primary" onclick="document.getElementById('csvFile').click()">
                                <i class="las la-folder-open me-2"></i>Browse Files
                            </button>
                            <p class="text-muted mt-3 mb-0">Supported: Excel (.xlsx, .xls) or CSV (.csv) | Max: 10MB</p>
                        </div>

                        <div id="selectedFile" class="mt-3" style="display: none;">
                            <div class="alert alert-success d-flex justify-content-between align-items-center">
                                <div>
                                    <i class="las la-file-excel me-2"></i>
                                    <span id="fileName"></span>
                                    <small class="text-muted ms-2" id="fileSize"></small>
                                </div>
                                <button type="button" class="btn btn-sm btn-danger" onclick="clearFile()">
                                    <i class="las la-times"></i>
                                </button>
                            </div>
                            <button type="submit" class="btn btn-success btn-lg w-100" id="uploadBtn">
                                <i class="las la-upload me-2"></i>Start Import
                            </button>
                        </div>
                    </form>

                    <!-- Progress Container -->
                    <div class="progress-container" id="progressContainer">
                        <h5 class="mb-3">Import Progress</h5>
                        <div class="progress">
                            <div class="progress-bar progress-bar-striped progress-bar-animated bg-success"
                                 id="progressBar"
                                 role="progressbar"
                                 style="width: 0%">
                                0%
                            </div>
                        </div>
                        <p class="text-center mt-2 text-muted" id="progressText">Preparing import...</p>

                        <!-- Import Stats -->
                        <div class="import-stats" id="importStats" style="display: none;">
                            <div class="stat-box total">
                                <div class="stat-number" id="statTotal">0</div>
                                <div class="stat-label">Total Rows</div>
                            </div>
                            <div class="stat-box success">
                                <div class="stat-number" id="statSuccess">0</div>
                                <div class="stat-label">Successful</div>
                            </div>
                            <div class="stat-box failed">
                                <div class="stat-number" id="statFailed">0</div>
                                <div class="stat-label">Failed</div>
                            </div>
                        </div>
                    </div>

                    <!-- Errors Container -->
                    <div id="errorsContainer" style="display: none; margin-top: 2rem;">
                        <h5 class="text-danger mb-3">
                            <i class="las la-exclamation-triangle me-2"></i>Import Errors
                        </h5>
                        <div class="error-list" id="errorList"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Imports Sidebar -->
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="las la-history me-2"></i>Recent Imports
                    </h5>
                </div>
                <div class="card-body">
                    @forelse($recentImports as $import)
                        <div class="mb-3 pb-3 border-bottom">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div>
                                    <h6 class="mb-1">{{ basename($import->filename) }}</h6>
                                    <small class="text-muted">{{ $import->created_at->diffForHumans() }}</small>
                                </div>
                                <span class="badge bg-{{ $import->status === 'completed' ? 'success' : ($import->status === 'failed' ? 'danger' : 'warning') }}">
                                    {{ ucfirst($import->status) }}
                                </span>
                            </div>
                            <div class="row g-2 small">
                                <div class="col-4 text-center">
                                    <div class="text-primary fw-bold">{{ $import->total_rows }}</div>
                                    <div class="text-muted">Total</div>
                                </div>
                                <div class="col-4 text-center">
                                    <div class="text-success fw-bold">{{ $import->successful_rows }}</div>
                                    <div class="text-muted">Success</div>
                                </div>
                                <div class="col-4 text-center">
                                    <div class="text-danger fw-bold">{{ $import->failed_rows }}</div>
                                    <div class="text-muted">Failed</div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center text-muted py-5">
                            <i class="las la-inbox" style="font-size: 3rem; opacity: 0.3;"></i>
                            <p class="mt-2 mb-0">No recent imports</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('extra-scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
let currentImportId = null;

// Drag & Drop functionality
const uploadArea = document.getElementById('uploadArea');
const fileInput = document.getElementById('csvFile');

['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
    uploadArea.addEventListener(eventName, preventDefaults, false);
});

function preventDefaults(e) {
    e.preventDefault();
    e.stopPropagation();
}

['dragenter', 'dragover'].forEach(eventName => {
    uploadArea.addEventListener(eventName, () => {
        uploadArea.classList.add('dragover');
    }, false);
});

['dragleave', 'drop'].forEach(eventName => {
    uploadArea.addEventListener(eventName, () => {
        uploadArea.classList.remove('dragover');
    }, false);
});

uploadArea.addEventListener('drop', handleDrop, false);

function handleDrop(e) {
    const dt = e.dataTransfer;
    const files = dt.files;
    fileInput.files = files;
    handleFileSelect();
}

fileInput.addEventListener('change', handleFileSelect);

function handleFileSelect() {
    const file = fileInput.files[0];
    if (file) {
        const validExtensions = ['csv', 'xlsx', 'xls'];
        const fileExtension = file.name.split('.').pop().toLowerCase();

        if (!validExtensions.includes(fileExtension)) {
            Swal.fire('Error', 'Please select a CSV or Excel file (.csv, .xlsx, .xls)', 'error');
            return;
        }

        document.getElementById('fileName').textContent = file.name;
        document.getElementById('fileSize').textContent = formatFileSize(file.size);
        document.getElementById('selectedFile').style.display = 'block';
    }
}

function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
}

function clearFile() {
    fileInput.value = '';
    document.getElementById('selectedFile').style.display = 'none';
    document.getElementById('progressContainer').style.display = 'none';
    document.getElementById('errorsContainer').style.display = 'none';
}

// Form submission
document.getElementById('uploadForm').addEventListener('submit', async function(e) {
    e.preventDefault();

    const formData = new FormData(this);
    const uploadBtn = document.getElementById('uploadBtn');

    // Disable upload button
    uploadBtn.disabled = true;
    uploadBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Uploading...';

    // Show progress container
    document.getElementById('progressContainer').style.display = 'block';
    document.getElementById('errorsContainer').style.display = 'none';
    document.getElementById('importStats').style.display = 'none';

    updateProgress(10, 'Uploading file...');

    try {
        const response = await fetch('{{ route("leads.process-bulk-import") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            },
            body: formData
        });

        // Check if response is OK
        if (!response.ok) {
            const errorText = await response.text();
            console.error('Server error:', errorText);
            throw new Error(`Server error: ${response.status} - ${response.statusText}`);
        }

        // Parse JSON
        const result = await response.json();

        if (result.success) {
            currentImportId = result.import_id;

            // Show stats
            document.getElementById('importStats').style.display = 'grid';
            document.getElementById('statTotal').textContent = result.stats.total;
            document.getElementById('statSuccess').textContent = result.stats.successful;
            document.getElementById('statFailed').textContent = result.stats.failed;

            // Update progress bar to 100%
            updateProgress(100, `Completed! ${result.stats.successful} leads imported successfully.`);

            // Show errors if any
            if (result.errors && result.errors.length > 0) {
                showErrors(result.errors);
            }

            Swal.fire({
                icon: result.stats.successful > 0 ? 'success' : 'warning',
                title: result.stats.successful > 0 ? 'Import Completed!' : 'Import Completed with Errors',
                html: `
                    <p><strong>Successfully imported:</strong> ${result.stats.successful} leads</p>
                    ${result.stats.failed > 0 ? `<p class="text-danger"><strong>Failed:</strong> ${result.stats.failed} rows (see details below)</p>` : ''}
                `,
                confirmButtonText: 'Go to Leads',
                showCancelButton: true,
                cancelButtonText: 'Stay Here'
            }).then((swalResult) => {
                if (swalResult.isConfirmed) {
                    window.location.href = '{{ route("leads.index") }}';
                } else {
                    uploadBtn.disabled = false;
                    uploadBtn.innerHTML = '<i class="las la-upload me-2"></i>Start Import';
                }
            });

        } else {
            // FAILURE CASE: still show stats + detailed row errors
            document.getElementById('importStats').style.display = 'grid';
            document.getElementById('statTotal').textContent = result.stats.total;
            document.getElementById('statSuccess').textContent = result.stats.successful;
            document.getElementById('statFailed').textContent = result.stats.failed;

            updateProgress(0, 'Import failed. See errors below.');
            const progressBar = document.getElementById('progressBar');
            progressBar.classList.remove('bg-success');
            progressBar.classList.add('bg-danger');

            if (result.errors && result.errors.length > 0) {
                showErrors(result.errors);
            }

            Swal.fire({
                icon: 'error',
                title: 'Import Failed',
                html: `
                    <p>${result.message}</p>
                    <p class="text-danger"><strong>Failed rows:</strong> ${result.stats.failed}</p>
                `,
                confirmButtonText: 'OK'
            });

            uploadBtn.disabled = false;
            uploadBtn.innerHTML = '<i class="las la-upload me-2"></i>Start Import';
        }

    } catch (error) {
        console.error('Import error:', error);

        // Update UI
        updateProgress(0, 'Import failed!');
        document.getElementById('progressBar').classList.remove('bg-success');
        document.getElementById('progressBar').classList.add('bg-danger');

        Swal.fire({
            icon: 'error',
            title: 'Import Failed',
            html: `<p>${error.message}</p>`,
            confirmButtonText: 'OK'
        });

        uploadBtn.disabled = false;
        uploadBtn.innerHTML = '<i class="las la-upload me-2"></i>Start Import';
    }
});

function updateProgress(percentage, text) {
    const progressBar = document.getElementById('progressBar');
    progressBar.style.width = percentage + '%';
    progressBar.textContent = Math.round(percentage) + '%';
    document.getElementById('progressText').textContent = text;
}

function showErrors(errors) {
    const errorsContainer = document.getElementById('errorsContainer');
    const errorList = document.getElementById('errorList');

    errorList.innerHTML = '';
    errors.forEach(error => {
        const errorItem = document.createElement('div');
        errorItem.className = 'error-item';
        errorItem.innerHTML = `
            <strong>Row ${error.row}:</strong> ${error.data}<br>
            <small class="text-danger">${Array.isArray(error.errors) ? error.errors.join(', ') : error.errors}</small>
        `;
        errorList.appendChild(errorItem);
    });

    errorsContainer.style.display = 'block';
}

// Handle page unload/refresh during import
window.addEventListener('beforeunload', function(e) {
    const uploadBtn = document.getElementById('uploadBtn');
    if (uploadBtn && uploadBtn.disabled) {
        e.preventDefault();
        e.returnValue = 'Import is in progress. Are you sure you want to leave?';
    }
});
</script>
@endsection
