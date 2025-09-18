<x-layouts.app :title="$title" :actions="'<a href=\'' . route('shortlinks.create') . '\' class=\'btn btn-primary\'><i class=\'bi bi-plus-lg me-2\'></i>Create Shortlink</a>'">
    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <x-stats-card 
                title="Total Shortlinks" 
                :value="number_format($stats['total_shortlinks'])"
                icon="link"
                color="primary"
            />
        </div>
        <div class="col-md-3">
            <x-stats-card 
                title="Active Shortlinks" 
                :value="number_format($stats['active_shortlinks'])"
                icon="check-circle"
                color="success"
            />
        </div>
        <div class="col-md-3">
            <x-stats-card 
                title="Expired Shortlinks" 
                :value="number_format($stats['expired_shortlinks'])"
                icon="clock"
                color="warning"
            />
        </div>
        <div class="col-md-3">
            <x-stats-card 
                title="Total Clicks" 
                :value="number_format($stats['total_clicks'])"
                icon="cursor-fill"
                color="info"
            />
        </div>
    </div>

    <!-- Filters and Search -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('shortlinks.index') }}" class="row g-3">
                <div class="col-md-3">
                    <label for="search" class="form-label">Search</label>
                    <input type="text" class="form-control" id="search" name="search" 
                           value="{{ $filters['search'] }}" placeholder="Short code or URL...">
                </div>
                <div class="col-md-2">
                    <label for="domain_id" class="form-label">Domain</label>
                    <select class="form-select" id="domain_id" name="domain_id">
                        <option value="">All Domains</option>
                        @foreach($domains as $domain)
                            <option value="{{ $domain->id }}" {{ $filters['domain_id'] == $domain->id ? 'selected' : '' }}>
                                {{ $domain->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="status" class="form-label">Status</label>
                    <select class="form-select" id="status" name="status">
                        <option value="">All Status</option>
                        <option value="active" {{ $filters['status'] === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ $filters['status'] === 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="sort" class="form-label">Sort By</label>
                    <select class="form-select" id="sort" name="sort">
                        <option value="created_at" {{ $filters['sort'] === 'created_at' ? 'selected' : '' }}>Created Date</option>
                        <option value="click_count" {{ $filters['sort'] === 'clicks_count' ? 'selected' : '' }}>Click Count</option>
                        <option value="short_code" {{ $filters['sort'] === 'short_code' ? 'selected' : '' }}>Short Code</option>
                    </select>
                </div>
                <div class="col-md-1">
                    <label for="direction" class="form-label">Order</label>
                    <select class="form-select" id="direction" name="direction">
                        <option value="desc" {{ $filters['direction'] === 'desc' ? 'selected' : '' }}>Desc</option>
                        <option value="asc" {{ $filters['direction'] === 'asc' ? 'selected' : '' }}>Asc</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-outline-primary me-2">
                        <i class="bi bi-search"></i> Filter
                    </button>
                    <a href="{{ route('shortlinks.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-x-circle"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Shortlinks Table -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">All Shortlinks ({{ $shortlinks->total() }})</h5>
            <div>
                <button type="button" class="btn btn-sm btn-outline-success" onclick="bulkAction('activate')" disabled id="bulkActivateBtn">
                    <i class="bi bi-check-circle"></i> Activate Selected
                </button>
                <button type="button" class="btn btn-sm btn-outline-warning" onclick="bulkAction('deactivate')" disabled id="bulkDeactivateBtn">
                    <i class="bi bi-pause-circle"></i> Deactivate Selected
                </button>
                <button type="button" class="btn btn-sm btn-outline-danger" onclick="bulkAction('delete')" disabled id="bulkDeleteBtn">
                    <i class="bi bi-trash"></i> Delete Selected
                </button>
            </div>
        </div>
        <div class="card-body p-0">
            @if($shortlinks->isNotEmpty())
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th width="50">
                                    <input type="checkbox" class="form-check-input" id="selectAll">
                                </th>
                                <th>Short URL</th>
                                <th>Original URL</th>
                                <th>Domain</th>
                                <th>Clicks</th>
                                <th>Status</th>
                                <th>Created</th>
                                <th width="120">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($shortlinks as $shortlink)
                                <tr>
                                    <td>
                                        <input type="checkbox" class="form-check-input shortlink-checkbox" 
                                               value="{{ $shortlink->id }}" name="selected_shortlinks[]">
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 35px; height: 35px;">
                                                <i class="bi bi-link"></i>
                                            </div>
                                            <div>
                                                <h6 class="mb-0">
                                                    <a href="{{ route('shortlinks.show', $shortlink) }}" class="text-decoration-none">
                                                        {{ $shortlink->short_code }}
                                                    </a>
                                                </h6>
                                                <small class="text-muted">{{ $shortlink->domain->name ?? 'N/A' }}</small>
                                                <button class="btn btn-sm btn-link p-0 ms-2" onclick="copyToClipboard('{{ $shortlink->domain->name }}/{{ $shortlink->short_code }}')" title="Copy URL">
                                                    <i class="bi bi-copy"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="text-truncate" style="max-width: 300px;" title="{{ $shortlink->original_url }}">
                                            <a href="{{ $shortlink->original_url }}" target="_blank" class="text-decoration-none">
                                                {{ Str::limit($shortlink->original_url, 50) }}
                                                <i class="bi bi-box-arrow-up-right ms-1"></i>
                                            </a>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-info">{{ $shortlink->domain->name ?? 'N/A' }}</span>
                                    </td>
                                    <td>
                                        <span class="badge bg-success">{{ number_format($shortlink->click_count) }}</span>
                                    </td>
                                    <td>
                                        <div class="form-check form-switch">
                                            <input 
                                                class="form-check-input" 
                                                type="checkbox" 
                                                {{ $shortlink->is_active ? 'checked' : '' }}
                                                onchange="toggleShortlinkStatus({{ $shortlink->id }}, this)"
                                            >
                                            <label class="form-check-label">
                                                <span class="badge bg-{{ $shortlink->is_active ? 'success' : 'secondary' }}">
                                                    {{ $shortlink->is_active ? 'Active' : 'Inactive' }}
                                                </span>
                                            </label>
                                        </div>
                                        @if($shortlink->expires_at)
                                            <small class="text-{{ $shortlink->is_expired ? 'danger' : 'warning' }}">
                                                {{ $shortlink->is_expired ? 'Expired' : 'Expires' }} {{ $shortlink->expires_at->diffForHumans() }}
                                            </small>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="text-muted">{{ $shortlink->created_at->format('M d, Y') }}</span>
                                        <small class="d-block text-muted">{{ $shortlink->created_at->diffForHumans() }}</small>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('shortlinks.show', $shortlink) }}" class="btn btn-sm btn-outline-primary" title="View">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <a href="{{ route('shortlinks.edit', $shortlink) }}" class="btn btn-sm btn-outline-secondary" title="Edit">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <button type="button" class="btn btn-sm btn-outline-danger" 
                                                onclick="deleteShortlink({{ $shortlink->id }}, '{{ $shortlink->short_code }}')" 
                                                title="Delete">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="card-footer">
                    {{ $shortlinks->links() }}
                </div>
            @else
                <div class="text-center py-5">
                    <i class="bi bi-link text-muted" style="font-size: 3rem;"></i>
                    <h5 class="text-muted mt-3">No Shortlinks Found</h5>
                    @if(!empty(array_filter($filters)))
                        <p class="text-muted">Try adjusting your filters or search terms.</p>
                        <a href="{{ route('shortlinks.index') }}" class="btn btn-outline-primary">Clear Filters</a>
                    @else
                        <p class="text-muted">Get started by creating your first shortlink.</p>
                        <a href="{{ route('shortlinks.create') }}" class="btn btn-primary">
                            <i class="bi bi-plus-lg me-2"></i>Create Your First Shortlink
                        </a>
                    @endif
                </div>
            @endif
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirm Deletion</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete the shortlink <strong id="shortlinkCode"></strong>?</p>
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        This action will also delete all associated click data and cannot be undone.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <form id="deleteForm" method="POST" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">
                            <i class="bi bi-trash me-2"></i>Delete Shortlink
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Bulk Action Modal -->
    <div class="modal fade" id="bulkActionModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="bulkActionTitle">Confirm Bulk Action</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p id="bulkActionMessage"></p>
                    <div class="alert alert-info" id="bulkActionWarning" style="display: none;">
                        <i class="bi bi-info-circle me-2"></i>
                        <span id="bulkActionWarningText"></span>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <form id="bulkActionForm" method="POST" action="{{ route('shortlinks.bulk-action') }}" class="d-inline">
                        @csrf
                        <input type="hidden" name="action" id="bulkActionType">
                        <div id="bulkActionInputs"></div>
                        <button type="submit" class="btn" id="bulkActionSubmit">
                            <i class="bi bi-check me-2"></i>Confirm
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        // Select all functionality
        document.getElementById('selectAll').addEventListener('change', function() {
            const checkboxes = document.querySelectorAll('.shortlink-checkbox');
            checkboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
            updateBulkActionButtons();
        });

        // Individual checkbox change
        document.querySelectorAll('.shortlink-checkbox').forEach(checkbox => {
            checkbox.addEventListener('change', updateBulkActionButtons);
        });

        function updateBulkActionButtons() {
            const checkedBoxes = document.querySelectorAll('.shortlink-checkbox:checked');
            const hasSelection = checkedBoxes.length > 0;
            
            document.getElementById('bulkActivateBtn').disabled = !hasSelection;
            document.getElementById('bulkDeactivateBtn').disabled = !hasSelection;
            document.getElementById('bulkDeleteBtn').disabled = !hasSelection;
        }

        function toggleShortlinkStatus(shortlinkId, checkbox) {
            const originalState = checkbox.checked;
            
            fetch(`/shortlinks/${shortlinkId}/toggle-status`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
            })
            .then(response => response.json())
            .then(data => {
                if (!data.success) {
                    checkbox.checked = !originalState;
                    alert(data.message || 'Failed to update shortlink status.');
                } else {
                    const badge = checkbox.parentElement.querySelector('.badge');
                    if (checkbox.checked) {
                        badge.className = 'badge bg-success';
                        badge.textContent = 'Active';
                    } else {
                        badge.className = 'badge bg-secondary';
                        badge.textContent = 'Inactive';
                    }
                }
            })
            .catch(error => {
                checkbox.checked = !originalState;
                alert('An error occurred while updating the shortlink status.');
                console.error('Error:', error);
            });
        }

        function deleteShortlink(id, code) {
            document.getElementById('shortlinkCode').textContent = code;
            document.getElementById('deleteForm').action = `/shortlinks/${id}`;
            
            const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
            modal.show();
        }

        function bulkAction(action) {
            const checkedBoxes = document.querySelectorAll('.shortlink-checkbox:checked');
            if (checkedBoxes.length === 0) return;

            const ids = Array.from(checkedBoxes).map(cb => cb.value);
            
            // Setup modal content
            const modal = document.getElementById('bulkActionModal');
            const title = modal.querySelector('#bulkActionTitle');
            const message = modal.querySelector('#bulkActionMessage');
            const warning = modal.querySelector('#bulkActionWarning');
            const warningText = modal.querySelector('#bulkActionWarningText');
            const submitBtn = modal.querySelector('#bulkActionSubmit');
            const actionInput = modal.querySelector('#bulkActionType');
            const inputsContainer = modal.querySelector('#bulkActionInputs');

            // Clear previous inputs
            inputsContainer.innerHTML = '';
            
            // Add hidden inputs for selected IDs
            ids.forEach(id => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'shortlinks[]';
                input.value = id;
                inputsContainer.appendChild(input);
            });

            actionInput.value = action;

            switch(action) {
                case 'activate':
                    title.textContent = 'Activate Shortlinks';
                    message.textContent = `Are you sure you want to activate ${ids.length} shortlink(s)?`;
                    submitBtn.className = 'btn btn-success';
                    submitBtn.innerHTML = '<i class="bi bi-check me-2"></i>Activate';
                    warning.style.display = 'none';
                    break;
                case 'deactivate':
                    title.textContent = 'Deactivate Shortlinks';
                    message.textContent = `Are you sure you want to deactivate ${ids.length} shortlink(s)?`;
                    submitBtn.className = 'btn btn-warning';
                    submitBtn.innerHTML = '<i class="bi bi-pause me-2"></i>Deactivate';
                    warning.style.display = 'block';
                    warningText.textContent = 'Deactivated shortlinks will stop working until reactivated.';
                    break;
                case 'delete':
                    title.textContent = 'Delete Shortlinks';
                    message.textContent = `Are you sure you want to delete ${ids.length} shortlink(s)?`;
                    submitBtn.className = 'btn btn-danger';
                    submitBtn.innerHTML = '<i class="bi bi-trash me-2"></i>Delete';
                    warning.style.display = 'block';
                    warningText.textContent = 'This action cannot be undone. All click data will also be deleted.';
                    break;
            }

            const modalInstance = new bootstrap.Modal(modal);
            modalInstance.show();
        }

        function copyToClipboard(text) {
            const protocol = window.location.protocol;
            const fullUrl = protocol + '//' + text;
            
            navigator.clipboard.writeText(fullUrl).then(() => {
                // Show success message
                const button = event.target.closest('button');
                const originalIcon = button.innerHTML;
                button.innerHTML = '<i class="bi bi-check text-success"></i>';
                
                setTimeout(() => {
                    button.innerHTML = originalIcon;
                }, 2000);
            }).catch(() => {
                alert('Failed to copy URL to clipboard');
            });
        }
    </script>
    @endpush
</x-layouts.app>