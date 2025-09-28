@extends('layouts.app')

@section('title', 'Discount Package Test')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">
                        <i class="fas fa-percentage me-2"></i>
                        Discount Package Testing Interface (No Auth Required)
                    </h4>
                </div>
                <div class="card-body">
                    <!-- Alert Section -->
                    <div id="alertContainer"></div>

                    <!-- User Selection Section -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">User Management</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <label class="form-label">Select Test User ID</label>
                                    <div class="input-group">
                                        <select name="userIdInput" id="userIdInput">
                                            @foreach($users as $userId => $userName)
                                                <option value="{{ $userId }}">{{ $userName }} (ID: {{ $userId }})</option>
                                            @endforeach
                                        </select>
                                        {{-- <input type="number" id="userIdInput" class="form-control" value="1" min="1"> --}}
                                        <button class="btn btn-primary" onclick="setUser()">
                                            <i class="fas fa-user me-2"></i>Set User
                                        </button>
                                    </div>
                                    <small class="text-muted">Current User ID: <span id="currentUserId">1</span></small>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Available Users</label>
                                    <div id="availableUsers" class="mt-1">
                                        @foreach($users as $userId => $userName)
                                            <span class="badge bg-secondary me-1 mb-1 user-badge" onclick="setUserById({{ $userId }})" style="cursor: pointer;">
                                                {{ $userName }}
                                            </span>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <!-- Left Column: Discount Management -->
                        <div class="col-md-6">
                            <!-- Create Discount Form -->
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h5 class="mb-0">Create New Discount</h5>
                                </div>
                                <div class="card-body">
                                    <div id="alertContainerNewDiscount"></div>
                                    <form id="createDiscountForm">
                                        @csrf
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <label class="form-label">Name</label>
                                                <input type="text" name="name" class="form-control" required value="Test Discount {{ rand(100, 999) }}">
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Code</label>
                                                <input type="text" name="code" class="form-control" required value="TEST{{ rand(100, 999) }}">
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Type</label>
                                                <select name="type" class="form-select" required>
                                                    <option value="percentage">Percentage</option>
                                                    <option value="fixed">Fixed Amount</option>
                                                </select>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Value</label>
                                                <input type="number" step="0.01" name="value" class="form-control" required value="10">
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Max Uses (Optional)</label>
                                                <input type="number" name="max_uses" class="form-control" value="10">
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Starts At (Optional)</label>
                                                <input type="datetime-local" name="starts_at" class="form-control">
                                            </div>
                                            <div class="col-12">
                                                <label class="form-label">Expires At (Optional)</label>
                                                <input type="datetime-local" name="expires_at" class="form-control">
                                            </div>
                                            <div class="col-12">
                                                <button type="submit" class="btn btn-success w-100">
                                                    <i class="fas fa-plus me-2"></i>Create Discount
                                                </button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>

                            <!-- Assign Discount Form -->
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0">Assign Discount to User</h5>
                                </div>
                                <div class="card-body">
                                    <div id="alertContainerAssignDiscount"></div>
                                    <form id="assignDiscountForm">
                                        @csrf
                                        <input type="hidden" name="user_id" id="assignUserId">
                                        <div class="mb-3">
                                            <label class="form-label">Select Discount</label>
                                            <select name="discount_id" class="form-select" required>
                                                <option value="">Choose a discount...</option>
                                                @foreach($discounts as $discount)
                                                    <option value="{{ $discount->id }}">
                                                        {{ $discount->name }} ({{ $discount->code }}) - 
                                                        {{ $discount->value }}{{ $discount->type === 'percentage' ? '%' : '$' }}
                                                        (Uses: {{ $discount->uses }}/{{ $discount->max_uses ?? '∞' }})
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Max User Uses (Optional)</label>
                                            <input type="number" name="max_uses" class="form-control" placeholder="Leave empty for no limit">
                                        </div>
                                        <button type="submit" class="btn btn-primary w-100">
                                            <i class="fas fa-user-plus me-2"></i>Assign to Current User
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- Right Column: Testing Actions -->
                        <div class="col-md-6">
                            <!-- Apply Discount Form -->
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h5 class="mb-0">Test Discount Application</h5>
                                </div>
                                <div class="card-body">
                                    <div id="alertContainerApplyDiscount"></div>
                                    <form id="applyDiscountForm">
                                        @csrf
                                        <input type="hidden" name="user_id" id="applyUserId">
                                        <div class="mb-3">
                                            <label class="form-label">Original Amount</label>
                                            <div class="input-group">
                                                <span class="input-group-text">$</span>
                                                <input type="number" step="0.01" name="amount" class="form-control" value="100.00" required>
                                            </div>
                                        </div>
                                        <button type="submit" class="btn btn-warning w-100">
                                            <i class="fas fa-calculator me-2"></i>Apply Discounts
                                        </button>
                                    </form>
                                    
                                    <!-- Results Display -->
                                    <div id="applyResults" class="mt-3 hidden">
                                        <div class="alert alert-info">
                                            <h6>Application Results:</h6>
                                            <div id="resultsContent"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- User's Discounts -->
                            <div class="card">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <h5 class="mb-0">Current User's Discounts</h5>
                                    <div>
                                        <button onclick="loadUserDiscounts()" class="btn btn-sm btn-outline-primary me-2">
                                            <i class="fas fa-sync-alt me-1"></i>Refresh
                                        </button>
                                        <button onclick="loadEligibleDiscounts()" class="btn btn-sm btn-outline-info">
                                            <i class="fas fa-list me-1"></i>Eligible
                                        </button>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div id="userDiscountsList">
                                        <p class="text-muted">Select a user to view their discounts</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- System Information -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0">Package Information</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <strong>Config:</strong>
                                            <pre class="bg-light p-2 mt-1"><code>@json(config('user-discounts'), JSON_PRETTY_PRINT)</code></pre>
                                        </div>
                                        <div class="col-md-4">
                                            <strong>Available Discounts:</strong>
                                            <div class="mt-1">
                                                @foreach($discounts as $discount)
                                                    <span class="badge bg-secondary me-1 mb-1">
                                                        {{ $discount->code }} ({{ $discount->uses }}/{{ $discount->max_uses ?? '∞' }})
                                                    </span>
                                                @endforeach
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <strong>Current User ID:</strong>
                                            <div class="mt-1" id="configUserId">1</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@section('scripts')
<script>
let currentUserId = {{ auth()->check() ? auth()->id() : 1 }};

// Show alert function with enhanced error handling
function showAlert(message, type = 'success', containerId = 'alertContainer') {
    try {
        const alertContainer = document.getElementById(containerId);
        if (!alertContainer) {
            console.error('Alert container not found. Ensure #alertContainer exists in the DOM.');
            return;
        }

        const alert = document.createElement('div');
        alert.className = `alert alert-${type} alert-dismissible fade show`;
        alert.setAttribute('role', 'alert');
        alert.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        `;
        alertContainer.appendChild(alert);
        console.log(`Alert displayed: ${message} (Type: ${type})`);
        setTimeout(() => {
            try {
                alert.remove();
            } catch (e) {
                console.warn('Error removing alert:', e);
            }
        }, 5000);
    } catch (error) {
        console.error('Error in showAlert:', error);
    }
}

// Set current user
async function setUser() {
    const userId = document.getElementById('userIdInput').value;
    if (!userId || userId < 1) {
        showAlert('Please select a valid User', 'danger');
        return;
    }

    try {
        const response = await fetch('{{ route("discount-test.set-user") }}', {
            method: 'POST',
            body: JSON.stringify({ user_id: parseInt(userId) }),
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        });
        
        const data = await response.json();
        
        if (data.success) {
            currentUserId = parseInt(userId);
            document.getElementById('currentUserId').textContent = currentUserId;
            document.getElementById('configUserId').textContent = currentUserId;
            document.getElementById('assignUserId').value = currentUserId;
            document.getElementById('applyUserId').value = currentUserId;
            
            showAlert('User set to: ' + currentUserId, 'success');
            loadUserDiscounts();
        } else {
            showAlert('Error: ' + data.message, 'danger');
        }
    } catch (error) {
        showAlert('Error: ' + error.message, 'danger');
    }
}

// Set user by clicking badge
function setUserById(userId) {
    document.getElementById('userIdInput').value = userId;
    setUser();
}

// Load user discounts
async function loadUserDiscounts() {
    try {
        const response = await fetch('{{ route("discount-test.eligible") }}?user_id=' + currentUserId);
        const data = await response.json();
        
        const discountsList = document.getElementById('userDiscountsList');
        
        if (data.success && data.data.length > 0) {
            discountsList.innerHTML = data.data.map(discount => `
                <div class="discount-item mb-2 p-2 border rounded">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <strong>${discount.discount.name}</strong>
                            <br>
                            <small class="text-muted">
                                Code: ${discount.discount.code} | 
                                Value: ${discount.discount.value}${discount.discount.type === 'percentage' ? '%' : '$'} | 
                                Uses: ${discount.uses}${discount.max_uses ? '/' + discount.max_uses : ''}
                            </small>
                        </div>
                        <button onclick="revokeDiscount(${discount.discount_id})" 
                                class="btn btn-sm btn-danger">
                            <i class="fas fa-times"></i> Revoke
                        </button>
                    </div>
                </div>
            `).join('');
        } else {
            discountsList.innerHTML = '<p class="text-muted">No discounts assigned to this user</p>';
        }
    } catch (error) {
        showAlert('Error loading discounts: ' + error.message, 'danger');
    }
}

// Create Discount Form
document.getElementById('createDiscountForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    
    try {
        const response = await fetch('{{ route("discount-test.create-discount") }}', {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        });
        
        const data = await response.json();
        
        if (data.success) {
            showAlert('Discount created successfully!', 'success', 'alertContainerNewDiscount');
            this.reset();
            location.reload(); // Reload to refresh discount lists
        } else {
            showAlert('Error: ' + data.message, 'danger', 'alertContainerNewDiscount');
        }
    } catch (error) {
        showAlert('Error: ' + error.message, 'danger',  'alertContainerNewDiscount');
    }
});

// Assign Discount Form
document.getElementById('assignDiscountForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    
    try {
        const response = await fetch('{{ route("discount-test.assign") }}', {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        });
        
        const data = await response.json();
        if (data.success) {
            showAlert(data.message, 'success', 'alertContainerAssignDiscount'); // Use dynamic message for duplicate assignments
            this.reset();
            loadUserDiscounts();
        } else {
            showAlert('Error: ' + data.message, 'danger', 'alertContainerAssignDiscount');
        }
    } catch (error) {
        showAlert('Error: ' + error.message, 'danger', 'alertContainerAssignDiscount');
    }
});

// Apply Discount Form
document.getElementById('applyDiscountForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    const resultsDiv = document.getElementById('applyResults');
    const resultsContent = document.getElementById('resultsContent');
    
    try {
        const response = await fetch('{{ route("discount-test.apply") }}', {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        });
        
        const data = await response.json();
        
        if (data.success) {
            resultsContent.innerHTML = `
                <p><strong>User ID:</strong> ${currentUserId}</p>
                <p><strong>Original Amount:</strong> $${data.data.original_amount}</p>
                <p><strong>Final Amount:</strong> $${data.data.final_amount}</p>
                <p><strong>Total Discount:</strong> $${data.data.total_discount}</p>
                <p><strong>Applied Discounts:</strong> ${data.data.applied_discounts.length}</p>
                ${data.data.applied_discounts.map(discount => `
                    <small>• ${discount.type}: ${discount.value}${discount.type === 'percentage' ? '%' : '$'} ($${discount.amount})</small><br>
                `).join('')}
            `;
            resultsDiv.classList.remove('hidden');
            showAlert('Discount applied successfully for User ' + currentUserId + '!', 'success', 'alertContainerApplyDiscount');
            loadUserDiscounts();
        } else {
            showAlert('Error: ' + data.message, 'danger', 'alertContainerApplyDiscount');
        }
    } catch (error) {
        showAlert('Error: ' + error.message, 'danger', 'alertContainerApplyDiscount');
    }
});

// Revoke Discount
async function revokeDiscount(discountId) {
    if (!confirm('Are you sure you want to revoke this discount from User ' + currentUserId + '?')) return;
    
    try {
        const response = await fetch('{{ route("discount-test.revoke") }}', {
            method: 'POST',
            body: JSON.stringify({ 
                discount_id: discountId,
                user_id: currentUserId 
            }),
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        });
        
        const data = await response.json();
        
        if (data.success) {
            showAlert('Discount revoked successfully from User ' + currentUserId + '!', 'success');
            loadUserDiscounts();
        } else {
            showAlert('Error: ' + data.message, 'danger');
        }
    } catch (error) {
        showAlert('Error: ' + error.message, 'danger');
    }
}

// Load eligible discounts
async function loadEligibleDiscounts() {
    try {
        const response = await fetch('{{ route("discount-test.eligible") }}?user_id=' + currentUserId);
        const data = await response.json();
        
        if (data.success) {
            showAlert('User ' + currentUserId + ' has ' + data.data.length + ' eligible discounts', 'info');
            console.log('Eligible discounts:', data.data);
        } else {
            showAlert('Error: ' + data.message, 'danger');
        }
    } catch (error) {
        showAlert('Error: ' + error.message, 'danger');
    }
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    // Set initial user ID in forms
    document.getElementById('assignUserId').value = currentUserId;
    document.getElementById('applyUserId').value = currentUserId;
    
    // Load initial user discounts
    loadUserDiscounts();
});
</script>

<style>
.hidden { display: none; }
.discount-item { transition: all 0.3s; }
.discount-item:hover { background-color: #f8f9fa; }
.user-badge:hover { background-color: #0d6efd !important; color: white !important; }
/* #alertContainer { min-height: 50px; } */
</style>
@endsection