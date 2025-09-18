<x-layouts.app :title="$title" :actions="'<a href=\'' . route('domains.create') . '\' class=\'btn btn-primary\'><i class=\'bi bi-plus-lg me-2\'></i>Add Domain</a>'">
    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-4">
            <x-stats-card 
                title="Total Domains" 
                :value="number_format($stats['total_domains'])"
                icon="globe"
                color="primary"
            />
        </div>
        <div class="col-md-4">
            <x-stats-card 
                title="Active Domains" 
                :value="number_format($stats['active_domains'])"
                icon="check-circle"
                color="success"
            />
        </div>
        <div class="col-md-4">
            <x-stats-card 
                title="Inactive Domains" 
                :value="number_format($stats['inactive_domains'])"
                icon="x-circle"
                color="warning"
            />
        </div>
    </div>

    <!-- Domains Table -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">All Domains</h5>
        </div>
        <div class="card-body p-0">
            @if($domains->isNotEmpty())
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Domain Name</th>
                                <th>Status</th>
                                <th>Shortlinks</th>
                                <th>Active Shortlinks</th>
                                <th>Created</th>
                                <th width="120">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($domains as $domain)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 35px; height: 35px;">
                                                <i class="bi bi-globe"></i>
                                            </div>
                                            <div>
                                                <h6 class="mb-0">
                                                    <a href="{{ route('domains.show', $domain) }}" class="text-decoration-none">
                                                        {{ $domain->name }}
                                                    </a>
                                                </h6>
                                                <small class="text-muted">ID: {{ $domain->id }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="form-check form-switch">
                                            <input 
                                                class="form-check-input" 
                                                type="checkbox" 
                                                {{ $domain->is_active ? 'checked' : '' }}
                                                onchange="toggleDomainStatus({{ $domain->id }}, this)"
                                            >
                                            <label class="form-check-label">
                                                <span class="badge bg-{{ $domain->is_active ? 'success' : 'secondary' }}">
                                                    {{ $domain->is_active ? 'Active' : 'Inactive' }}
                                                </span>
                                            </label>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-primary">{{ $domain->shortlinks_count ?? 0 }}</span>
                                    </td>
                                    <td>
                                        <span class="badge bg-success">{{ $domain->active_shortlinks_count ?? 0 }}</span>
                                    </td>
                                    <td>
                                        <span class="text-muted">{{ $domain->created_at->format('M d, Y') }}</span>
                                        <small class="d-block text-muted">{{ $domain->created_at->diffForHumans() }}</small>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('domains.show', $domain) }}" class="btn btn-sm btn-outline-primary" title="View">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <a href="{{ route('domains.edit', $domain) }}" class="btn btn-sm btn-outline-secondary" title="Edit">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <button type="button" class="btn btn-sm btn-outline-danger" 
                                                onclick="deleteDomain({{ $domain->id }}, '{{ $domain->name }}')" 
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
            @else
                <div class="text-center py-5">
                    <i class="bi bi-globe text-muted" style="font-size: 3rem;"></i>
                    <h5 class="text-muted mt-3">No Domains Found</h5>
                    <p class="text-muted">Get started by adding your first domain.</p>
                    <a href="{{ route('domains.create') }}" class="btn btn-primary">
                        <i class="bi bi-plus-lg me-2"></i>Add Your First Domain
                    </a>
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
                    <p>Are you sure you want to delete the domain <strong id="domainName"></strong>?</p>
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        This action will also delete all associated shortlinks and cannot be undone.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <form id="deleteForm" method="POST" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">
                            <i class="bi bi-trash me-2"></i>Delete Domain
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function toggleDomainStatus(domainId, checkbox) {
            const originalState = checkbox.checked;
            
            fetch(`/domains/${domainId}/toggle-status`, {
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
                    alert(data.message || 'Failed to update domain status.');
                } else {
                    // Update the badge
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
                alert('An error occurred while updating the domain status.');
                console.error('Error:', error);
            });
        }

        function deleteDomain(id, name) {
            document.getElementById('domainName').textContent = name;
            document.getElementById('deleteForm').action = `/domains/${id}`;
            
            const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
            modal.show();
        }
    </script>
    @endpush
</x-layouts.app>