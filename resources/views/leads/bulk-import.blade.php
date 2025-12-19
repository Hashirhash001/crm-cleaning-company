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

    .progress-details {
        background: #f1f5f9;
        padding: 1rem;
        border-radius: 8px;
        margin-top: 1rem;
        font-family: 'Courier New', monospace;
    }

    .recent-import-item {
        position: relative;
    }

    .download-failed-btn {
        position: absolute;
        top: 10px;
        right: 10px;
        padding: 4px 8px;
        font-size: 12px;
    }

    /* Better table styling in SweetAlert */
    .swal2-popup table {
        width: 100%;
        margin-bottom: 0;
    }

    .swal2-popup table td {
        padding: 0.5rem;
    }

    .swal2-popup .table-info td {
        background-color: #e0f2fe;
        font-weight: 600;
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
                    <p class="text-muted mb-0">Import multiple leads at once using Excel file (Max: 3000 rows)</p>
                </div>
                <div>
                    <button onclick="location.reload()" class="btn btn-light me-2" title="Refresh to see latest imports">
                        <i class="las la-sync-alt"></i> Refresh
                    </button>
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
                            <li>Remove the example row before uploading</li>
                            <li>Maximum 3000 rows per import</li>
                            <li>Wait for the import to complete (may take 3-5 minutes for large files)</li>
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
                            <p class="text-muted mt-3 mb-0">Supported: Excel (.xlsx, .xls) or CSV (.csv) | Max: 10MB | Max Rows: 3000</p>
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

                        <!-- Real-time Progress Details -->
                        <div class="progress-details" id="progressDetails">
                            <div class="d-flex justify-content-between mb-2">
                                <span><i class="las la-tasks me-2"></i>Processing:</span>
                                <strong id="rowProgress">0 / 0</strong>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span><i class="las la-check-circle me-2"></i>Successful:</span>
                                <strong class="text-success" id="successProgress">0</strong>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span><i class="las la-times-circle me-2"></i>Failed:</span>
                                <strong class="text-danger" id="failedProgress">0</strong>
                            </div>
                            <div class="d-flex justify-content-between" id="currentSheetContainer" style="display: none;">
                                <span><i class="las la-file-alt me-2"></i>Current Sheet:</span>
                                <strong id="currentSheet">-</strong>
                            </div>
                        </div>

                        <div class="progress mt-3">
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
                <div class="card-body" style="max-height: 600px; overflow-y: auto;">
                    @forelse($recentImports as $import)
                        <div class="mb-3 pb-3 border-bottom position-relative">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div class="flex-grow-1 pe-3">
                                    <h6 class="mb-1">
                                        <i class="las la-file-excel me-1"></i>
                                        {{ basename($import->filename) }}
                                    </h6>
                                    <small class="text-muted">
                                        <i class="las la-clock me-1"></i>
                                        {{ $import->created_at->diffForHumans() }}
                                    </small>
                                </div>
                                <span class="badge bg-{{ $import->status === 'completed' ? 'success' : ($import->status === 'failed' ? 'danger' : 'warning') }}">
                                    {{ ucfirst($import->status) }}
                                </span>
                            </div>

                            <div class="row g-2 small mb-2">
                                <div class="col-4 text-center">
                                    <div class="text-primary fw-bold">{{ $import->total_rows ?? 0 }}</div>
                                    <div class="text-muted">Total</div>
                                </div>
                                <div class="col-4 text-center">
                                    <div class="text-success fw-bold">{{ $import->successful_rows ?? 0 }}</div>
                                    <div class="text-muted">Success</div>
                                </div>
                                <div class="col-4 text-center">
                                    <div class="text-danger fw-bold">{{ $import->failed_rows ?? 0 }}</div>
                                    <div class="text-muted">Failed</div>
                                </div>
                            </div>

                            @php
                                // Check if failed_rows_data exists and is not empty
                                $hasFailedData = false;
                                if ($import->failed_rows > 0) {
                                    if (is_array($import->failed_rows_data)) {
                                        $hasFailedData = !empty($import->failed_rows_data);
                                    } elseif (is_string($import->failed_rows_data)) {
                                        $decoded = json_decode($import->failed_rows_data, true);
                                        $hasFailedData = !empty($decoded);
                                    }
                                }
                            @endphp

                            @if($hasFailedData)
                                <div class="d-grid gap-1 mt-2">
                                    <a href="{{ route('leads.download-failed-rows', $import->id) }}"
                                    class="btn btn-sm btn-danger"
                                    title="Download Failed Rows CSV">
                                        <i class="las la-download me-1"></i>
                                        Download Failed Rows ({{ $import->failed_rows }})
                                    </a>
                                </div>
                            @endif
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
let progressInterval = null;

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

        if (file.size > 10 * 1024 * 1024) {
            Swal.fire('Error', 'File size exceeds 10MB limit', 'error');
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
    stopProgressPolling();
}

// Start polling for progress updates
function startProgressPolling(importId) {
    currentImportId = importId;

    progressInterval = setInterval(async () => {
        try {
            const response = await fetch(`{{ url('/leads/bulk-import/progress') }}/${importId}`);
            const progress = await response.json();

            if (progress.error) {
                stopProgressPolling();
                return;
            }

            // Update progress details
            document.getElementById('rowProgress').textContent = `${progress.processed} / ${progress.total}`;
            document.getElementById('successProgress').textContent = progress.successful;
            document.getElementById('failedProgress').textContent = progress.failed;

            if (progress.current_sheet) {
                document.getElementById('currentSheetContainer').style.display = 'flex';
                document.getElementById('currentSheet').textContent = progress.current_sheet;
            }

            // Update progress bar
            updateProgress(progress.percentage,
                `Processing row ${progress.processed} of ${progress.total}...`);

            // Stop polling if completed
            if (progress.status === 'completed' || progress.percentage >= 100) {
                stopProgressPolling();
                updateProgress(100, 'Import completed!');
            }

        } catch (error) {
            console.error('Progress polling error:', error);
        }
    }, 1000); // Poll every 1 second
}

function stopProgressPolling() {
    if (progressInterval) {
        clearInterval(progressInterval);
        progressInterval = null;
    }
}

// Form submission with pre-validation
document.getElementById('uploadForm').addEventListener('submit', async function(e) {
    e.preventDefault();

    const formData = new FormData(this);
    const uploadBtn = document.getElementById('uploadBtn');

    uploadBtn.disabled = true;
    uploadBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Validating...';

    try {
        // Step 1: Pre-validate the file
        updateProgress(10, 'Analyzing file for duplicates...');
        document.getElementById('progressContainer').style.display = 'block';

        const preValidateResponse = await fetch('{{ route("leads.pre-validate-import") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            },
            body: formData
        });

        if (!preValidateResponse.ok) {
            throw new Error('Pre-validation failed');
        }

        const validation = await preValidateResponse.json();

        if (!validation.success) {
            throw new Error(validation.message);
        }

        updateProgress(20, 'Validation complete...');

        // Step 2: Show warning if duplicates found
        if (validation.needs_warning) {
            const confirmResult = await Swal.fire({
                icon: 'warning',
                title: 'Duplicate Data Detected',
                html: `
                    <div class="text-start">
                        <p>${validation.warning_message}</p>
                        <hr>
                        <h6 class="mb-3">Import Summary:</h6>
                        <table class="table table-sm">
                            <tr>
                                <td><strong>Total Rows:</strong></td>
                                <td class="text-end">${validation.analysis.total_rows}</td>
                            </tr>
                            <tr>
                                <td><strong>Existing Phones:</strong></td>
                                <td class="text-end text-warning">${validation.analysis.existing_count}</td>
                            </tr>
                            <tr>
                                <td><strong>New Phones:</strong></td>
                                <td class="text-end text-success">${validation.analysis.new_count}</td>
                            </tr>
                            <tr class="table-info">
                                <td><strong>Will Create:</strong></td>
                                <td></td>
                            </tr>
                            <tr>
                                <td class="ps-4">New Leads:</td>
                                <td class="text-end">${validation.analysis.will_create_leads}</td>
                            </tr>
                            <tr>
                                <td class="ps-4">New Customers:</td>
                                <td class="text-end">${validation.analysis.will_create_customers}</td>
                            </tr>
                            ${validation.analysis.leads_to_convert > 0 ? `
                            <tr>
                                <td class="ps-4 text-info">Lead Conversions:</td>
                                <td class="text-end text-info">${validation.analysis.leads_to_convert}</td>
                            </tr>
                            ` : ''}
                            <tr>
                                <td class="ps-4">New Jobs:</td>
                                <td class="text-end">${validation.analysis.will_create_jobs}</td>
                            </tr>
                            ${validation.analysis.new_jobs_for_existing > 0 ? `
                            <tr>
                                <td class="ps-4 text-warning">Jobs for Existing Customers:</td>
                                <td class="text-end text-warning">${validation.analysis.new_jobs_for_existing}</td>
                            </tr>
                            ` : ''}
                        </table>

                        <div class="alert alert-info mt-3 mb-0">
                            <small><i class="las la-info-circle me-1"></i>
                            ${validation.analysis.existing_count === validation.analysis.total_rows
                                ? 'This appears to be a duplicate upload. Only new jobs will be created for existing customers with payment received.'
                                : 'Duplicate phone numbers will be processed according to payment status.'}
                            </small>
                        </div>
                    </div>
                `,
                showCancelButton: true,
                confirmButtonText: 'Yes, Continue Import',
                cancelButtonText: 'Cancel',
                confirmButtonColor: '#10b981',
                cancelButtonColor: '#ef4444',
                width: '600px',
                customClass: {
                    popup: 'text-start'
                }
            });

            if (!confirmResult.isConfirmed) {
                uploadBtn.disabled = false;
                uploadBtn.innerHTML = '<i class="las la-upload me-2"></i>Start Import';
                document.getElementById('progressContainer').style.display = 'none';
                return;
            }
        }

        // Step 3: Proceed with actual import
        uploadBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Importing...';
        updateProgress(30, 'Starting import process...');

        const response = await fetch('{{ route("leads.process-bulk-import") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            },
            body: formData
        });

        if (!response.ok) {
            const errorText = await response.text();
            console.error('Server error:', errorText);
            throw new Error(`Server error: ${response.status}`);
        }

        const result = await response.json();

        // Start progress polling
        if (result.import_id) {
            startProgressPolling(result.import_id);
        }

        // Wait for completion
        await waitForCompletion(result);

    } catch (error) {
        console.error('Import error:', error);

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
        stopProgressPolling();
    }
});

async function waitForCompletion(result) {
    const uploadBtn = document.getElementById('uploadBtn');

    // Wait for progress polling to complete
    await new Promise((resolve) => {
        const checkComplete = setInterval(() => {
            const progressBar = document.getElementById('progressBar');
            const width = parseFloat(progressBar.style.width);

            if (width >= 100) {
                clearInterval(checkComplete);
                setTimeout(resolve, 500);
            }
        }, 500);
    });

    // Show final results
    if (result.success || (result.stats && result.stats.successful > 0)) {
        currentImportId = result.import_id;

        document.getElementById('importStats').style.display = 'grid';
        document.getElementById('statTotal').textContent = result.stats.total;
        document.getElementById('statSuccess').textContent = result.stats.successful;
        document.getElementById('statFailed').textContent = result.stats.failed;

        if (result.skipped_sheets && result.skipped_sheets.length > 0) {
            let skippedHtml = '<div class="alert alert-warning mt-3">';
            skippedHtml += '<h6 class="alert-heading"><i class="las la-exclamation-triangle me-2"></i>Skipped Sheets</h6>';
            skippedHtml += '<ul class="mb-0">';
            result.skipped_sheets.forEach(sheet => {
                skippedHtml += `<li><strong>${sheet.name}</strong>: ${sheet.reason}</li>`;
            });
            skippedHtml += '</ul></div>';
            document.getElementById('progressContainer').insertAdjacentHTML('beforeend', skippedHtml);
        }

        // Show errors if they exist
        if (result.errors && result.errors.length > 0) {
            console.log('Showing errors:', result.errors.length);
            showErrors(result.errors, result.import_id, result.has_failed_rows);
        } else {
            document.getElementById('errorsContainer').style.display = 'none';
        }

        // Show completion dialog WITHOUT "Go to Leads" button
        Swal.fire({
            icon: result.stats.successful > 0 ? 'success' : 'warning',
            title: result.stats.successful > 0 ? 'Import Completed!' : 'Import Completed with Errors',
            html: `
                <div class="text-start">
                    <p><strong>✓ Successfully processed:</strong> ${result.stats.successful} rows</p>
                    ${result.stats.customers_created > 0 ? `<p><strong>✓ Customers created:</strong> ${result.stats.customers_created}</p>` : ''}
                    ${result.stats.jobs_created > 0 ? `<p><strong>✓ Jobs created:</strong> ${result.stats.jobs_created}</p>` : ''}
                    ${result.stats.failed > 0 ? `<p class="text-danger"><strong>✗ Failed:</strong> ${result.stats.failed} rows</p>` : ''}
                    ${result.has_failed_rows ? '<p class="text-info mt-2"><small><i class="las la-info-circle me-1"></i>Scroll down to see error details and download failed rows.</small></p>' : ''}
                </div>
            `,
            confirmButtonText: 'OK',
            confirmButtonColor: '#10b981',
            width: '600px',
            allowOutsideClick: false
        }).then(() => {
            uploadBtn.disabled = false;
            uploadBtn.innerHTML = '<i class="las la-upload me-2"></i>Start Import';

            // Scroll to errors if any
            if (result.errors && result.errors.length > 0) {
                setTimeout(() => {
                    document.getElementById('errorsContainer').scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }, 300);
            }

            // NO AUTO-REFRESH - Let user view errors
            // User can manually refresh to see in Recent Imports
        });

    } else {
        // Failed import
        document.getElementById('importStats').style.display = 'grid';
        document.getElementById('statTotal').textContent = result.stats?.total || 0;
        document.getElementById('statSuccess').textContent = result.stats?.successful || 0;
        document.getElementById('statFailed').textContent = result.stats?.failed || 0;

        updateProgress(0, 'Import failed.');
        document.getElementById('progressBar').classList.remove('bg-success');
        document.getElementById('progressBar').classList.add('bg-danger');

        if (result.errors && result.errors.length > 0) {
            showErrors(result.errors, result.import_id, result.has_failed_rows);
        }

        Swal.fire({
            icon: 'error',
            title: 'Import Failed',
            html: `<p>${result.message}</p>`,
            confirmButtonText: 'OK'
        });

        uploadBtn.disabled = false;
        uploadBtn.innerHTML = '<i class="las la-upload me-2"></i>Start Import';
    }
}

function updateProgress(percentage, text) {
    const progressBar = document.getElementById('progressBar');
    progressBar.style.width = percentage + '%';
    progressBar.textContent = Math.round(percentage) + '%';
    document.getElementById('progressText').textContent = text;
}

function showErrors(errors, importId, hasFailedRows) {
    const errorsContainer = document.getElementById('errorsContainer');
    const errorList = document.getElementById('errorList');

    errorList.innerHTML = '';

    if (hasFailedRows) {
        const downloadBtn = document.createElement('div');
        downloadBtn.className = 'mb-3';
        downloadBtn.innerHTML = `
            <a href="{{ url('/leads/bulk-import/download-failed') }}/${importId}"
               class="btn btn-danger">
                <i class="las la-download me-2"></i>Download Failed Rows (${errors.length})
            </a>
        `;
        errorList.appendChild(downloadBtn);
    }

    const errorsPerPage = 50;
    let currentPage = 1;
    const totalPages = Math.ceil(errors.length / errorsPerPage);

    function renderErrors(page) {
        const start = (page - 1) * errorsPerPage;
        const end = start + errorsPerPage;
        const pageErrors = errors.slice(start, end);

        let errorHtml = '';
        pageErrors.forEach(error => {
            errorHtml += `
                <div class="error-item">
                    <strong>${error.row}</strong>
                    <span class="badge bg-secondary">${error.sheet}</span><br>
                    <small class="text-muted">${error.data}</small><br>
                    <small class="text-danger fw-bold">${Array.isArray(error.errors) ? error.errors.join(', ') : error.errors}</small>
                </div>
            `;
        });

        if (totalPages > 1) {
            errorHtml += `
                <div class="d-flex justify-content-between align-items-center mt-3">
                    <button class="btn btn-sm btn-secondary" onclick="changePage(${page - 1})" ${page === 1 ? 'disabled' : ''}>
                        <i class="las la-angle-left"></i> Previous
                    </button>
                    <span>Page ${page} of ${totalPages} (${errors.length} total errors)</span>
                    <button class="btn btn-sm btn-secondary" onclick="changePage(${page + 1})" ${page === totalPages ? 'disabled' : ''}>
                        Next <i class="las la-angle-right"></i>
                    </button>
                </div>
            `;
        } else {
            errorHtml += `<p class="text-center text-muted mt-3">Showing all ${errors.length} errors</p>`;
        }

        const errorContainer = errorList.querySelector('.errors-list') || document.createElement('div');
        errorContainer.className = 'errors-list';
        errorContainer.innerHTML = errorHtml;

        if (!errorList.querySelector('.errors-list')) {
            errorList.appendChild(errorContainer);
        }
    }

    window.changePage = function(page) {
        if (page >= 1 && page <= totalPages) {
            currentPage = page;
            renderErrors(page);
            errorList.scrollIntoView({ behavior: 'smooth' });
        }
    };

    renderErrors(1);
    errorsContainer.style.display = 'block';
}

window.addEventListener('beforeunload', function(e) {
    const uploadBtn = document.getElementById('uploadBtn');
    if (uploadBtn && uploadBtn.disabled) {
        e.preventDefault();
        e.returnValue = 'Import is in progress. Are you sure you want to leave?';
    }
});

// Use pagehide instead of unload (modern browsers)
window.addEventListener('pagehide', stopProgressPolling);

</script>
@endsection
