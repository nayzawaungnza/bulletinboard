@extends('layouts.app')

@section('title', 'Users')
@section('page-title', 'Users Management')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <p class="text-muted mb-0">Manage system users</p>
    </div>
    <a href="{{ route('users.create') }}" class="btn btn-primary">
        <i class="bi bi-plus"></i> New User
    </a>
</div>

<!-- Added search and filter form -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('users.index') }}" id="searchForm">
            <div class="row g-3">
                <div class="col-md-3">
                    <label for="search_name" class="form-label">Name</label>
                    <input type="text" class="form-control" id="search_name" name="search_name" 
                           value="{{ request('search_name') }}" placeholder="Search by name">
                </div>
                <div class="col-md-3">
                    <label for="search_email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="search_email" name="search_email" 
                           value="{{ request('search_email') }}" placeholder="Search by email">
                </div>
                <div class="col-md-2">
                    <label for="search_role" class="form-label">Role</label>
                    <select class="form-select" id="search_role" name="search_role">
                        <option value="">All Roles</option>
                        <option value="0" {{ request('search_role') === '0' ? 'selected' : '' }}>Admin</option>
                        <option value="1" {{ request('search_role') === '1' ? 'selected' : '' }}>User</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="date_from" class="form-label">From Date</label>
                    <input type="date" class="form-control" id="date_from" name="date_from" 
                           value="{{ request('date_from') }}">
                </div>
                <div class="col-md-2">
                    <label for="date_to" class="form-label">To Date</label>
                    <input type="date" class="form-control" id="date_to" name="date_to" 
                           value="{{ request('date_to') }}">
                </div>
            </div>
            <div class="row mt-3">
                <div class="col-12">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-search"></i> Search
                    </button>
                    <button type="button" class="btn btn-secondary" onclick="resetForm()">
                        <i class="bi bi-arrow-clockwise"></i> Reset
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body">
        @if($users->count() > 0)
            <!-- Added bulk action controls -->
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <input type="checkbox" id="selectAll" class="form-check-input me-2">
                    <label for="selectAll" class="form-check-label">Select All</label>
                </div>
                <div id="bulkActions" style="display: none;">
                    <button type="button" class="btn btn-success btn-sm" onclick="bulkUnlock()">
                        <i class="bi bi-unlock"></i> Unlock Selected
                    </button>
                    <button type="button" class="btn btn-danger btn-sm" onclick="bulkDelete()">
                        <i class="bi bi-trash"></i> Delete Selected
                    </button>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th width="50">
                                <input type="checkbox" id="selectAllTable" class="form-check-input">
                            </th>
                            <th>User</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Last Login</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($users as $user)
                        <tr>
                            <td>
                                <!-- Added checkbox for bulk selection -->
                                @if($user->id !== auth()->id())
                                <input type="checkbox" class="form-check-input user-checkbox" value="{{ $user->id }}">
                                @endif
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    @if($user->profile_path)
                                        <img src="{{ Storage::url($user->profile_path) }}" alt="Profile" class="rounded-circle me-2" width="32" height="32">
                                    @else
                                        <i class="bi bi-person-circle fs-4 me-2"></i>
                                    @endif
                                    <div>
                                        <div class="fw-bold">{{ $user->name }}</div>
                                        @if($user->phone)
                                        <small class="text-muted">{{ $user->phone }}</small>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td>{{ $user->email }}</td>
                            <td>
                                <span class="badge bg-{{ $user->isAdmin() ? 'danger' : 'primary' }}">
                                    {{ $user->isAdmin() ? 'Admin' : 'User' }}
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-{{ $user->isLocked() ? 'danger' : 'success' }}">
                                    {{ $user->isLocked() ? 'Locked' : 'Active' }}
                                </span>
                                @if($user->lock_count > 0)
                                <small class="text-muted d-block">Failed: {{ $user->lock_count }}</small>
                                @endif
                            </td>
                            <td>
                                {{ $user->last_login_at ? $user->last_login_at->format('M d, Y H:i') : 'Never' }}
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="{{ route('users.show', $user) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="{{ route('users.edit', $user) }}" class="btn btn-sm btn-outline-secondary">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    @if($user->id !== auth()->id())
                                    <!-- Updated lock/unlock button to use modal confirmation -->
                                    <button type="button" class="btn btn-sm btn-outline-{{ $user->isLocked() ? 'success' : 'warning' }}" 
                                            onclick="confirmToggleLock({{ $user->id }}, '{{ $user->name }}', {{ $user->isLocked() ? 'true' : 'false' }})"
                                            title="{{ $user->isLocked() ? 'Unlock' : 'Lock' }} User">
                                        <i class="bi bi-{{ $user->isLocked() ? 'unlock' : 'lock' }}"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-danger" 
                                            onclick="confirmDelete({{ $user->id }}, '{{ $user->name }}')">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            {{ $users->links() }}
            <div class="pagination-info">
    Showing {{ $users->firstItem() }} to {{ $users->lastItem() }} of {{ $users->total() }} entries
</div>
        @else
            <div class="text-center py-5">
                <i class="bi bi-people fs-1 text-muted"></i>
                <h4 class="text-muted mt-3">No Users Found</h4>
                <a href="{{ route('users.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus"></i> Create User
                </a>
            </div>
        @endif
    </div>
</div>

<!-- Added confirmation modals -->
<!-- Lock/Unlock Confirmation Modal -->
<div class="modal fade" id="lockUnlockModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="lockUnlockTitle">Confirm Action</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p id="lockUnlockMessage"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="confirmLockUnlock">Confirm</button>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p id="deleteMessage"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDelete">Delete</button>
            </div>
        </div>
    </div>
</div>

<!-- Bulk Action Confirmation Modal -->
<div class="modal fade" id="bulkActionModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="bulkActionTitle">Confirm Bulk Action</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p id="bulkActionMessage"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="confirmBulkAction">Confirm</button>
            </div>
        </div>
    </div>
</div>

<!-- Hidden forms for actions -->
<form id="toggleLockForm" method="POST" style="display: none;">
    @csrf
</form>

<form id="deleteForm" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>

<form id="bulkActionForm" method="POST" action="{{ route('users.bulk-action') }}" style="display: none;">
    @csrf
    <input type="hidden" name="action" id="bulkActionType">
    <input type="hidden" name="user_ids" id="bulkUserIds">
</form>

<script>
function resetForm() {
    document.getElementById('searchForm').reset();
    window.location.href = "{{ route('users.index') }}";
}

// Checkbox functionality
document.getElementById('selectAll').addEventListener('change', function() {
    const checkboxes = document.querySelectorAll('.user-checkbox');
    checkboxes.forEach(checkbox => {
        checkbox.checked = this.checked;
    });
    toggleBulkActions();
});

document.getElementById('selectAllTable').addEventListener('change', function() {
    const checkboxes = document.querySelectorAll('.user-checkbox');
    checkboxes.forEach(checkbox => {
        checkbox.checked = this.checked;
    });
    document.getElementById('selectAll').checked = this.checked;
    toggleBulkActions();
});

document.querySelectorAll('.user-checkbox').forEach(checkbox => {
    checkbox.addEventListener('change', toggleBulkActions);
});

function toggleBulkActions() {
    const checkedBoxes = document.querySelectorAll('.user-checkbox:checked');
    const bulkActions = document.getElementById('bulkActions');
    
    if (checkedBoxes.length > 0) {
        bulkActions.style.display = 'block';
    } else {
        bulkActions.style.display = 'none';
    }
}

function confirmToggleLock(userId, userName, isLocked) {
    const action = isLocked ? 'unlock' : 'lock';
    const title = isLocked ? 'Account Unlock' : 'Account Lock';
    const message = `Do you want to ${action} ${userName}'s account?`;
    
    document.getElementById('lockUnlockTitle').textContent = title;
    document.getElementById('lockUnlockMessage').textContent = message;
    
    document.getElementById('confirmLockUnlock').onclick = function() {
        const form = document.getElementById('toggleLockForm');
        form.action = `/users/${userId}/toggle-lock`;
        form.submit();
    };
    
    new bootstrap.Modal(document.getElementById('lockUnlockModal')).show();
}

function confirmDelete(userId, userName) {
    document.getElementById('deleteMessage').textContent = `Are you sure you want to delete ${userName}? This action cannot be undone.`;
    
    document.getElementById('confirmDelete').onclick = function() {
        const form = document.getElementById('deleteForm');
        form.action = `/users/${userId}`;
        form.submit();
    };
    
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}

function bulkUnlock() {
    const checkedBoxes = document.querySelectorAll('.user-checkbox:checked');
    if (checkedBoxes.length === 0) return;
    
    document.getElementById('bulkActionTitle').textContent = 'Bulk Unlock Users';
    document.getElementById('bulkActionMessage').textContent = `Are you sure you want to unlock ${checkedBoxes.length} selected user(s)?`;
    
    document.getElementById('confirmBulkAction').onclick = function() {
        const userIds = Array.from(checkedBoxes).map(cb => cb.value);
        document.getElementById('bulkActionType').value = 'unlock';
        document.getElementById('bulkUserIds').value = JSON.stringify(userIds);
        document.getElementById('bulkActionForm').submit();
    };
    
    new bootstrap.Modal(document.getElementById('bulkActionModal')).show();
}

function bulkDelete() {
    const checkedBoxes = document.querySelectorAll('.user-checkbox:checked');
    if (checkedBoxes.length === 0) return;
    
    document.getElementById('bulkActionTitle').textContent = 'Bulk Delete Users';
    document.getElementById('bulkActionMessage').textContent = `Are you sure you want to delete ${checkedBoxes.length} selected user(s)? This action cannot be undone.`;
    
    document.getElementById('confirmBulkAction').onclick = function() {
        const userIds = Array.from(checkedBoxes).map(cb => cb.value);
        document.getElementById('bulkActionType').value = 'delete';
        document.getElementById('bulkUserIds').value = JSON.stringify(userIds);
        document.getElementById('bulkActionForm').submit();
    };
    
    new bootstrap.Modal(document.getElementById('bulkActionModal')).show();
}
</script>
@endsection
