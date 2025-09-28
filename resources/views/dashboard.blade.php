@extends('layouts.app')

@section('content')
<div class="row">
    <div class="col-12">
        <h1>Bulk Import & Image Upload System</h1>
        <p class="text-muted">Efficiently manage product data and images with our intuitive bulk operations</p>
    </div>
</div>

<div class="row">
    <!-- CSV Import Section -->
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header d-flex align-items-center">
                <i class="fas fa-file-csv me-2"></i>
                <h5 class="card-title mb-0">CSV Bulk Import</h5>
            </div>
            <div class="card-body">
                <form id="csvImportForm" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-3">
                        <label for="csvFile" class="form-label">Select CSV File</label>
                        <div class="input-group">
                            <input type="file" class="form-control" id="csvFile" name="csv_file" accept=".csv" required>
                            <button class="btn btn-outline-secondary" type="button" id="csvInfoBtn" data-bs-toggle="tooltip" data-bs-placement="top" title="CSV should contain columns: SKU, Name, Description, Price, Stock">
                                <i class="fas fa-info-circle"></i>
                            </button>
                        </div>
                        <div class="form-text">Supports CSV files up to 100MB. Download <a href="{{ asset('storage/mock/products_10000.csv') }}">template</a>.</div>
                    </div>
                    <button type="submit" class="btn btn-primary w-100" id="importBtn">
                        <span class="spinner-border spinner-border-sm hidden" id="importSpinner"></span>
                        <i class="fas fa-upload me-2"></i>Import CSV
                    </button>
                </form>

                <div id="importResults" class="results-card hidden mt-4">
                    <h6 class="mb-3"><i class="fas fa-chart-bar me-2"></i>Import Results</h6>
                    <div id="resultsContent"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Image Upload Section -->
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header d-flex align-items-center">
                <i class="fas fa-images me-2"></i>
                <h5 class="card-title mb-0">Drag & Drop Image Upload</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label for="productSku" class="form-label">Product SKU</label>
                    <input type="text" class="form-control" id="productSku" placeholder="Enter product SKU" required>
                    <div class="form-text">Enter the SKU of the product to attach images to</div>
                </div>

                <div class="upload-area" id="uploadArea">
                    <i class="fas fa-cloud-upload-alt fa-3x mb-3"></i>
                    <h5>Drop your images here</h5>
                    <p class="text-muted mb-3">or</p>
                    <button type="button" class="btn btn-outline-primary" onclick="document.getElementById('imageFiles').click()">
                        <i class="fas fa-folder-open me-2"></i>Browse Files
                    </button>
                    <input type="file" id="imageFiles" multiple accept="image/*" class="hidden">
                    <p class="small text-muted mt-2">Supports JPG, PNG, GIF up to 10MB each</p>
                </div>

                <!-- Upload Progress Section - Always visible after selection -->
                <div id="uploadStatus" class="mt-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="mb-0"><i class="fas fa-tasks me-2"></i>Upload Progress</h6>
                        <div class="upload-stats">
                            <span class="badge bg-success me-2" id="completedCount">0</span>
                            <span class="badge bg-warning me-2" id="uploadingCount">0</span>
                            <span class="badge bg-secondary" id="pendingCount">0</span>
                        </div>
                    </div>
                    
                    <div id="uploadProgressContainer">
                        <div class="overall-progress mb-3">
                            <div class="d-flex justify-content-between mb-1">
                                <span>Overall Progress</span>
                                <span id="overallProgressPercent">0%</span>
                            </div>
                            <div class="progress" style="height: 10px;">
                                <div class="progress-bar" role="progressbar" id="overallProgressBar" style="width: 0%"></div>
                            </div>
                        </div>
                        
                        <div id="filesList" class="files-list"></div>
                    </div>
                    
                    <div id="noFilesMessage" class="text-center py-4">
                        <i class="fas fa-images fa-2x text-muted mb-2"></i>
                        <p class="text-muted">No images selected yet. Drag and drop or browse to add images.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Results Modal -->
<div class="modal fade" id="resultsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-chart-pie me-2"></i>Import Results</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="modalResultsContent">
                <div class="row text-center mb-4">
                    <div class="col-md-3">
                        <div class="stat-card">
                            <div class="stat-value" id="modalTotal">0</div>
                            <div class="stat-label">Total Rows</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card">
                            <div class="stat-value text-success" id="modalImported">0</div>
                            <div class="stat-label">Imported</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card">
                            <div class="stat-value text-warning" id="modalUpdated">0</div>
                            <div class="stat-label">Updated</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card">
                            <div class="stat-value text-danger" id="modalInvalid">0</div>
                            <div class="stat-label">Invalid</div>
                        </div>
                    </div>
                </div>
                <div id="modalErrors"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('styles')
<style>
.upload-area {
    border: 2px dashed #dee2e6;
    border-radius: 8px;
    padding: 2rem;
    text-align: center;
    transition: all 0.3s ease;
    position: relative;
    min-height: 200px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
}

.upload-area.drag-over {
    border-color: #0d6efd;
    background-color: rgba(13, 110, 253, 0.05);
}

.hidden {
    display: none !important;
}

#uploadStatus {
    border: 1px solid #e9ecef;
    border-radius: 8px;
    padding: 1rem;
    background-color: #f8f9fa;
}

.upload-stats .badge {
    font-size: 0.8rem;
    padding: 0.35em 0.65em;
}

.overall-progress {
    background: white;
    padding: 15px;
    border-radius: 8px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.files-list {
    max-height: 300px;
    overflow-y: auto;
}

.file-item {
    display: flex;
    align-items: center;
    padding: 10px;
    margin-bottom: 8px;
    border-radius: 6px;
    border-left: 4px solid #6c757d;
    background-color: white;
    transition: all 0.3s ease;
    box-shadow: 0 1px 2px rgba(0,0,0,0.05);
}

.file-item.uploading {
    border-left-color: #ffc107;
    background-color: #fffbf0;
}

.file-item.completed {
    border-left-color: #198754;
    background-color: #f0f9f4;
}

.file-item.failed {
    border-left-color: #dc3545;
    background-color: #fdf2f2;
}

.file-icon {
    width: 36px;
    height: 36px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: white;
    border-radius: 6px;
    margin-right: 10px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.file-info {
    flex: 1;
    min-width: 0;
}

.file-name {
    font-weight: 500;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    font-size: 0.9rem;
}

.file-size {
    font-size: 0.75rem;
    color: #6c757d;
}

.progress-container {
    width: 100%;
    height: 5px;
    background: #e9ecef;
    border-radius: 3px;
    overflow: hidden;
    margin-top: 5px;
}

.progress-bar {
    height: 100%;
    background: linear-gradient(90deg, #0d6efd, #0dcaf0);
    border-radius: 3px;
    transition: width 0.3s ease;
}

.file-status {
    min-width: 80px;
    text-align: right;
    font-size: 0.8rem;
}

#noFilesMessage {
    display: block;
}

#uploadProgressContainer {
    display: none;
}

.stat-card {
    padding: 10px;
}

.stat-value {
    font-size: 1.8rem;
    font-weight: bold;
}

.stat-label {
    font-size: 0.9rem;
    color: #6c757d;
}
</style>
@endsection

@section('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/crypto-js/4.1.1/crypto-js.min.js"></script>
<script>
// Initialize tooltips
var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
    return new bootstrap.Tooltip(tooltipTriggerEl)
})

// CSV Import Functionality
document.getElementById('csvImportForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const fileInput = document.getElementById('csvFile');
    const importBtn = document.getElementById('importBtn');
    const importSpinner = document.getElementById('importSpinner');
    
    if (!fileInput.files.length) {
        showAlert('Please select a CSV file', 'warning');
        return;
    }

    importBtn.disabled = true;
    importSpinner.classList.remove('hidden');
    importBtn.innerHTML = '<span class="spinner-border spinner-border-sm" id="importSpinner"></span> Importing...';

    const formData = new FormData();
    formData.append('csv_file', fileInput.files[0]);
    formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);

    try {
        const response = await fetch('/api/import/csv', {
            method: 'POST',
            body: formData
        });

        const data = await response.json();

        if (data.success) {
            showImportResults(data.results);
            showAlert('CSV imported successfully!', 'success');
        } else {
            showAlert('Import failed: ' + data.message, 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        showAlert('Import failed: ' + error.message, 'error');
    } finally {
        importBtn.disabled = false;
        importSpinner.classList.add('hidden');
        importBtn.innerHTML = '<i class="fas fa-upload me-2"></i>Import CSV';
        fileInput.value = '';
    }
});

function showImportResults(results) {
    // Update modal stats
    document.getElementById('modalTotal').textContent = results.total;
    document.getElementById('modalImported').textContent = results.imported;
    document.getElementById('modalUpdated').textContent = results.updated;
    document.getElementById('modalInvalid').textContent = results.invalid;
    
    // Update errors section
    const errorsContainer = document.getElementById('modalErrors');
    if (results.errors.length) {
        errorsContainer.innerHTML = `
            <h6 class="text-danger"><i class="fas fa-exclamation-triangle me-2"></i>Errors</h6>
            <div class="error-list">
                ${results.errors.map(error => `<div class="error-item">${error}</div>`).join('')}
            </div>
        `;
    } else {
        errorsContainer.innerHTML = '<div class="text-center text-success py-3"><i class="fas fa-check-circle fa-2x mb-2"></i><p>No errors found!</p></div>';
    }
    
    // Show modal
    new bootstrap.Modal(document.getElementById('resultsModal')).show();
}

function showAlert(message, type) {
    // Create alert element
    const alert = document.createElement('div');
    alert.className = `alert alert-${type === 'error' ? 'danger' : type} alert-dismissible fade show`;
    alert.innerHTML = `
        <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'warning' ? 'exclamation-triangle' : 'exclamation-circle'} me-2"></i>
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    // Add to page
    const container = document.querySelector('.container');
    const firstRow = container ? container.querySelector('.row') : null;

    if (container) {
        if (firstRow) {
            container.insertBefore(alert, firstRow);
        } else {
            container.prepend(alert);
        }
    }
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        if (alert.parentNode) {
            alert.remove();
        }
    }, 5000);
}

// Enhanced Image Upload Functionality
class ChunkedUploader {
    constructor() {
        this.CHUNK_SIZE = 1024 * 1024; // 1MB
        this.uploadArea = document.getElementById('uploadArea');
        this.filesList = document.getElementById('filesList');
        this.uploadProgressContainer = document.getElementById('uploadProgressContainer');
        this.noFilesMessage = document.getElementById('noFilesMessage');
        this.productSku = document.getElementById('productSku');
        this.overallProgressBar = document.getElementById('overallProgressBar');
        this.overallProgressPercent = document.getElementById('overallProgressPercent');
        this.completedCount = document.getElementById('completedCount');
        this.uploadingCount = document.getElementById('uploadingCount');
        this.pendingCount = document.getElementById('pendingCount');
        
        this.selectedFiles = [];
        this.uploadQueue = [];
        this.activeUploads = 0;
        this.maxConcurrentUploads = 3;
        this.completedUploads = 0;
        
        this.initializeEventListeners();
    }

    initializeEventListeners() {
        const fileInput = document.getElementById('imageFiles');
        
        // File input change
        fileInput.addEventListener('change', (e) => {
            this.handleFiles(Array.from(e.target.files));
        });

        // Drag and drop events
        this.uploadArea.addEventListener('dragover', (e) => {
            e.preventDefault();
            this.uploadArea.classList.add('drag-over');
        });

        this.uploadArea.addEventListener('dragleave', () => {
            this.uploadArea.classList.remove('drag-over');
        });

        this.uploadArea.addEventListener('drop', (e) => {
            e.preventDefault();
            this.uploadArea.classList.remove('drag-over');
            this.handleFiles(Array.from(e.dataTransfer.files));
        });
    }

    handleFiles(files) {
        if (!this.productSku.value.trim()) {
            showAlert('Please enter a product SKU first', 'warning');
            return;
        }

        const imageFiles = files.filter(file => file.type.startsWith('image/'));
        
        if (imageFiles.length === 0) {
            showAlert('Please select valid image files', 'warning');
            return;
        }
        
        if (files.length !== imageFiles.length) {
            showAlert('Some files were skipped as they are not images', 'info');
        }
        
        // Add files to selection
        imageFiles.forEach(file => {
            if (!this.selectedFiles.some(f => f.name === file.name && f.size === file.size)) {
                this.selectedFiles.push(file);
                this.addFileToProgressList(file);
            }
        });
        
        this.updateProgressDisplay();
        
        // Auto-start upload when files are selected
        if (this.selectedFiles.length > 0) {
            this.startUpload();
        }
    }

    addFileToProgressList(file) {
        const fileId = Date.now() + Math.random();
        const fileElement = document.createElement('div');
        fileElement.className = 'file-item';
        fileElement.id = `file-${fileId}`;
        fileElement.innerHTML = `
            <div class="file-icon">
                <i class="fas fa-image"></i>
            </div>
            <div class="file-info">
                <div class="file-name">${file.name}</div>
                <div class="file-size small text-muted">${this.formatFileSize(file.size)}</div>
                <div class="progress-container">
                    <div class="progress-bar" style="width: 0%"></div>
                </div>
            </div>
            <div class="file-status small text-muted">Pending</div>
        `;
        
        // Store file ID with the file object
        file._id = fileId;
        this.filesList.appendChild(fileElement);
    }

    updateProgressDisplay() {
        if (this.selectedFiles.length > 0) {
            this.noFilesMessage.style.display = 'none';
            this.uploadProgressContainer.style.display = 'block';
        } else {
            this.noFilesMessage.style.display = 'block';
            this.uploadProgressContainer.style.display = 'none';
        }
        
        this.updateStats();
    }

    startUpload() {
        if (this.selectedFiles.length === 0) return;
        
        // Initialize upload queue with all selected files
        this.uploadQueue = [...this.selectedFiles];
        this.activeUploads = 0;
        this.completedUploads = 0;
        
        // Reset progress indicators
        this.updateOverallProgress();
        this.updateStats();
        
        // Start processing uploads
        this.processUploadQueue();
    }

    processUploadQueue() {
        while (this.activeUploads < this.maxConcurrentUploads && this.uploadQueue.length > 0) {
            const file = this.uploadQueue.shift();
            this.uploadFile(file);
            this.activeUploads++;
        }
        
        this.updateStats();
    }

    async uploadFile(file) {
        const totalChunks = Math.ceil(file.size / this.CHUNK_SIZE);
        
        this.updateFileStatus(file._id, 'uploading', 'Preparing...');

        try {
            // Initialize upload
            const uploadId = await this.initUpload(file);
            this.updateFileStatus(file._id, 'uploading', 'Initializing...');

            // Upload chunks
            for (let chunkIndex = 0; chunkIndex < totalChunks; chunkIndex++) {
                const chunk = file.slice(
                    chunkIndex * this.CHUNK_SIZE,
                    Math.min((chunkIndex + 1) * this.CHUNK_SIZE, file.size)
                );
                
                await this.uploadChunk(uploadId, chunk, chunkIndex);
                
                const progress = ((chunkIndex + 1) / totalChunks) * 100;
                this.updateFileProgress(file._id, progress, `Uploading... ${Math.round(progress)}%`);
            }

            // Complete upload
            await this.completeUpload(uploadId, file);
            this.updateFileStatus(file._id, 'completed', 'Completed');
            this.completedUploads++;
            showAlert(`Image "${file.name}" uploaded successfully!`, 'success');

        } catch (error) {
            console.error('Upload failed:', error);
            this.updateFileStatus(file._id, 'failed', 'Failed: ' + error.message);
            showAlert(`Upload failed for "${file.name}": ${error.message}`, 'error');
        } finally {
            this.activeUploads--;
            this.updateOverallProgress();
            this.updateStats();
            this.processUploadQueue(); // Process next in queue
            
            // Check if all uploads are complete
            if (this.activeUploads === 0 && this.uploadQueue.length === 0) {
                if (this.completedUploads === this.selectedFiles.length) {
                    showAlert('All images uploaded successfully!', 'success');
                    // Reset after a delay
                    setTimeout(() => {
                        this.selectedFiles = [];
                        this.filesList.innerHTML = '';
                        this.updateProgressDisplay();
                    }, 3000);
                } else {
                    showAlert(`Upload completed with ${this.selectedFiles.length - this.completedUploads} failures`, 'warning');
                }
            }
        }
    }

    async initUpload(file) {
        const response = await fetch('/api/upload/init', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({
                original_name: file.name,
                file_size: file.size,
                total_chunks: Math.ceil(file.size / this.CHUNK_SIZE),
                chunk_size: this.CHUNK_SIZE,
                mime_type: file.type
            })
        });

        const data = await response.json();
        return data.upload_id;
    }

    async uploadChunk(uploadId, chunk, chunkIndex) {
        const formData = new FormData();
        formData.append('chunk_index', chunkIndex);
        formData.append('chunk_file', chunk);

        const response = await fetch(`/api/upload/${uploadId}/chunk`, {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        });

        if (!response.ok) {
            throw new Error('Chunk upload failed');
        }
    }

    async completeUpload(uploadId, file) {
        const checksum = await this.calculateChecksum(file);
        
        const response = await fetch(`/api/upload/${uploadId}/complete`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({
                checksum: checksum,
                product_sku: this.productSku.value.trim()
            })
        });

        if (!response.ok) {
            const error = await response.json();
            throw new Error(error.message || 'Upload completion failed');
        }

        return await response.json();
    }

    async calculateChecksum(file) {
        return new Promise((resolve) => {
            const reader = new FileReader();
            reader.onload = () => {
                const hash = CryptoJS.MD5(CryptoJS.enc.Latin1.parse(reader.result));
                resolve(hash.toString());
            };
            reader.readAsBinaryString(file);
        });
    }

    formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }

    updateFileProgress(fileId, progress, status) {
        const fileElement = document.getElementById(`file-${fileId}`);
        if (fileElement) {
            const progressBar = fileElement.querySelector('.progress-bar');
            const statusElement = fileElement.querySelector('.file-status');
            
            progressBar.style.width = `${progress}%`;
            statusElement.textContent = status;
        }
    }

    updateFileStatus(fileId, status, message) {
        const fileElement = document.getElementById(`file-${fileId}`);
        if (fileElement) {
            fileElement.className = `file-item ${status}`;
            const statusElement = fileElement.querySelector('.file-status');
            statusElement.textContent = message;
            
            if (status === 'completed') {
                statusElement.className = 'file-status small text-success';
            } else if (status === 'failed') {
                statusElement.className = 'file-status small text-danger';
            } else if (status === 'uploading') {
                statusElement.className = 'file-status small text-warning';
            } else {
                statusElement.className = 'file-status small text-muted';
            }
        }
    }

    updateOverallProgress() {
        const totalFiles = this.selectedFiles.length;
        const progress = totalFiles > 0 ? (this.completedUploads / totalFiles) * 100 : 0;
        
        this.overallProgressBar.style.width = `${progress}%`;
        this.overallProgressPercent.textContent = `${Math.round(progress)}%`;
    }

    updateStats() {
        const totalFiles = this.selectedFiles.length;
        const pendingFiles = totalFiles - this.completedUploads - this.activeUploads;
        
        this.completedCount.textContent = this.completedUploads;
        this.uploadingCount.textContent = this.activeUploads;
        this.pendingCount.textContent = pendingFiles > 0 ? pendingFiles : 0;
    }
}

// Initialize the uploader when page loads
document.addEventListener('DOMContentLoaded', () => {
    new ChunkedUploader();
});
</script>
@endsection