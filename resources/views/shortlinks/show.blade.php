<x-layouts.app :title="$title" :actions="'<div class=\'btn-group\'><a href=\'' . route('shortlinks.edit', $shortlink) . '\' class=\'btn btn-primary\'><i class=\'bi bi-pencil me-2\'></i>Edit</a><a href=\'' . route('shortlinks.index') . '\' class=\'btn btn-outline-secondary\'><i class=\'bi bi-arrow-left me-2\'></i>Back</a></div>'">
    <div class="row">
        <div class="col-lg-8">
            <!-- Shortlink Details -->
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="bi bi-link me-2"></i>Shortlink Details
                        </h5>
                        <div>
                            <span class="badge bg-{{ $shortlink->is_active ? 'success' : 'secondary' }} me-2">
                                {{ $shortlink->is_active ? 'Active' : 'Inactive' }}
                            </span>
                            @if($shortlink->expires_at)
                                <span class="badge bg-{{ $shortlink->is_expired ? 'danger' : 'warning' }}">
                                    {{ $shortlink->is_expired ? 'Expired' : 'Expires' }} {{ $shortlink->expires_at->diffForHumans() }}
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Short URL Display -->
                    <div class="row mb-4">
                        <div class="col-md-8">
                            <label class="form-label fw-bold text-muted small">SHORT URL</label>
                            <div class="input-group">
                                <input 
                                    type="text" 
                                    class="form-control form-control-lg" 
                                    value="{{ $shortUrl }}" 
                                    readonly
                                    id="shortUrl"
                                >
                                <button class="btn btn-outline-primary" onclick="copyToClipboard('shortUrl')" title="Copy URL">
                                    <i class="bi bi-copy"></i>
                                </button>
                                <a href="{{ $shortUrl }}" target="_blank" class="btn btn-outline-success" title="Visit URL">
                                    <i class="bi bi-box-arrow-up-right"></i>
                                </a>
                            </div>
                        </div>
                        <div class="col-md-4 text-end">
                            <label class="form-label fw-bold text-muted small">TOTAL CLICKS</label>
                            <div class="display-6 text-primary fw-bold">
                                {{ number_format($shortlink->click_count) }}
                            </div>
                        </div>
                    </div>

                    <!-- Original URL -->
                    <div class="mb-4">
                        <label class="form-label fw-bold text-muted small">REDIRECTS TO</label>
                        <div class="input-group">
                            <input 
                                type="text" 
                                class="form-control" 
                                value="{{ $shortlink->original_url }}" 
                                readonly
                                id="originalUrl"
                            >
                            <button class="btn btn-outline-primary" onclick="copyToClipboard('originalUrl')" title="Copy URL">
                                <i class="bi bi-copy"></i>
                            </button>
                            <a href="{{ $shortlink->original_url }}" target="_blank" class="btn btn-outline-success" title="Visit Original URL">
                                <i class="bi bi-box-arrow-up-right"></i>
                            </a>
                        </div>
                    </div>

                    <!-- Details Row -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold text-muted small">DOMAIN</label>
                                <div class="p-2 bg-light rounded">
                                    <i class="bi bi-globe me-2"></i>{{ $shortlink->domain->name ?? 'N/A' }}
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold text-muted small">SHORT CODE</label>
                                <div class="p-2 bg-light rounded">
                                    <code>{{ $shortlink->short_code }}</code>
                                </div>
                            </div>

                            @if($shortlink->description)
                                <div class="mb-3">
                                    <label class="form-label fw-bold text-muted small">DESCRIPTION</label>
                                    <div class="p-2 bg-light rounded">
                                        {{ $shortlink->description }}
                                    </div>
                                </div>
                            @endif
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold text-muted small">CREATED</label>
                                <div class="p-2 bg-light rounded">
                                    <i class="bi bi-calendar me-2"></i>{{ $shortlink->created_at->format('M d, Y \a\t H:i') }}
                                    <small class="text-muted d-block">{{ $shortlink->created_at->diffForHumans() }}</small>
                                </div>
                            </div>

                            @if($shortlink->expires_at)
                                <div class="mb-3">
                                    <label class="form-label fw-bold text-muted small">EXPIRES</label>
                                    <div class="p-2 bg-light rounded">
                                        <i class="bi bi-clock me-2"></i>{{ $shortlink->expires_at->format('M d, Y \a\t H:i') }}
                                        <small class="text-{{ $shortlink->is_expired ? 'danger' : 'warning' }} d-block">
                                            {{ $shortlink->is_expired ? 'Expired' : 'Expires' }} {{ $shortlink->expires_at->diffForHumans() }}
                                        </small>
                                    </div>
                                </div>
                            @endif

                            @if($shortlink->password)
                                <div class="mb-3">
                                    <label class="form-label fw-bold text-muted small">SECURITY</label>
                                    <div class="p-2 bg-light rounded">
                                        <i class="bi bi-shield-lock text-warning me-2"></i>Password Protected
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Tags -->
                    @if($shortlink->tags && is_array($shortlink->tags) && count($shortlink->tags) > 0)
                        <div class="mb-3">
                            <label class="form-label fw-bold text-muted small">TAGS</label>
                            <div>
                                @foreach($shortlink->tags as $tag)
                                    <span class="badge bg-info me-2">{{ $tag }}</span>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Click Analytics -->
            <div class="card mt-4">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="bi bi-bar-chart me-2"></i>Click Analytics
                        </h5>
                        <div class="btn-group btn-group-sm">
                            <button type="button" class="btn btn-outline-primary active" data-period="7">7 Days</button>
                            <button type="button" class="btn btn-outline-primary" data-period="30">30 Days</button>
                            <button type="button" class="btn btn-outline-primary" data-period="90">90 Days</button>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Analytics Charts Container -->
                    <div id="analyticsContainer">
                        <div class="text-center py-5">
                            <i class="bi bi-hourglass-split" style="font-size: 2rem;"></i>
                            <p class="mt-2">Loading analytics...</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Clicks -->
            @if($recentClicks->isNotEmpty())
                <div class="card mt-4">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="bi bi-cursor me-2"></i>Recent Clicks (Last 10)
                        </h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Time</th>
                                        <th>Location</th>
                                        <th>Device</th>
                                        <th>Browser</th>
                                        <th>Referrer</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($recentClicks as $click)
                                        <tr>
                                            <td>
                                                <span class="text-muted">{{ $click->created_at->format('M d, H:i') }}</span>
                                                <small class="d-block text-muted">{{ $click->created_at->diffForHumans() }}</small>
                                            </td>
                                            <td>
                                                <i class="bi bi-geo-alt text-muted me-1"></i>
                                                {{ $click->country ?? 'Unknown' }}
                                                @if($click->city)
                                                    <small class="text-muted">, {{ $click->city }}</small>
                                                @endif
                                            </td>
                                            <td>
                                                <i class="bi bi-{{ $click->device_type === 'mobile' ? 'phone' : ($click->device_type === 'tablet' ? 'tablet' : 'laptop') }} text-muted me-1"></i>
                                                {{ ucfirst($click->device_type ?? 'Unknown') }}
                                            </td>
                                            <td>
                                                {{ $click->browser ?? 'Unknown' }}
                                                @if($click->operating_system)
                                                    <small class="text-muted">on {{ $click->operating_system }}</small>
                                                @endif
                                            </td>
                                            <td>
                                                @if($click->referrer_url)
                                                    <span class="text-truncate d-inline-block" style="max-width: 200px;" title="{{ $click->referrer_url }}">
                                                        {{ $click->referrer_url }}
                                                    </span>
                                                @else
                                                    <span class="text-muted">Direct</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <div class="col-lg-4">
            <!-- QR Code -->
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="bi bi-qr-code me-2"></i>QR Code
                    </h6>
                </div>
                <div class="card-body text-center">
                    <div id="qrcode" class="mb-3"></div>
                    <button class="btn btn-outline-primary btn-sm me-2" onclick="downloadQRCode()">
                        <i class="bi bi-download me-1"></i>Download PNG
                    </button>
                    <button class="btn btn-outline-primary btn-sm" onclick="shareShortlink()">
                        <i class="bi bi-share me-1"></i>Share
                    </button>
                </div>
            </div>

            <!-- Quick Stats -->
            <div class="card mt-4">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="bi bi-speedometer2 me-2"></i>Quick Stats
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-6 mb-3">
                            <div class="text-muted small">TODAY</div>
                            <div class="h5 text-primary mb-0">{{ $stats['today'] ?? 0 }}</div>
                        </div>
                        <div class="col-6 mb-3">
                            <div class="text-muted small">THIS WEEK</div>
                            <div class="h5 text-success mb-0">{{ $stats['week'] ?? 0 }}</div>
                        </div>
                        <div class="col-6 mb-3">
                            <div class="text-muted small">THIS MONTH</div>
                            <div class="h5 text-info mb-0">{{ $stats['month'] ?? 0 }}</div>
                        </div>
                        <div class="col-6 mb-3">
                            <div class="text-muted small">ALL TIME</div>
                            <div class="h5 text-warning mb-0">{{ $shortlink->click_count }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="card mt-4">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="bi bi-tools me-2"></i>Actions
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <!-- Toggle Status -->
                        <form method="POST" action="{{ route('shortlinks.toggle-status', $shortlink) }}" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-{{ $shortlink->is_active ? 'warning' : 'success' }} w-100">
                                <i class="bi bi-{{ $shortlink->is_active ? 'pause-circle' : 'play-circle' }} me-2"></i>
                                {{ $shortlink->is_active ? 'Deactivate' : 'Activate' }}
                            </button>
                        </form>

                        <!-- Reset Password -->
                        @if($shortlink->password)
                            <button type="button" class="btn btn-outline-warning" onclick="resetPassword()">
                                <i class="bi bi-key me-2"></i>Reset Password
                            </button>
                        @else
                            <button type="button" class="btn btn-outline-info" onclick="addPassword()">
                                <i class="bi bi-shield-plus me-2"></i>Add Password
                            </button>
                        @endif

                        <!-- Reset Stats -->
                        <button type="button" class="btn btn-outline-secondary" onclick="confirmResetStats()">
                            <i class="bi bi-arrow-clockwise me-2"></i>Reset Statistics
                        </button>

                        <!-- Delete -->
                        <button type="button" class="btn btn-outline-danger" onclick="confirmDelete()">
                            <i class="bi bi-trash me-2"></i>Delete Shortlink
                        </button>
                    </div>
                </div>
            </div>

            <!-- Share Options -->
            @if($shortlink->is_active)
                <div class="card mt-4">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="bi bi-share me-2"></i>Share This Link
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <a href="https://twitter.com/intent/tweet?url={{ urlencode($shortUrl) }}&text={{ urlencode($shortlink->description ?? 'Check out this link!') }}" 
                               target="_blank" class="btn btn-outline-primary btn-sm">
                                <i class="bi bi-twitter me-2"></i>Twitter
                            </a>
                            <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode($shortUrl) }}" 
                               target="_blank" class="btn btn-outline-primary btn-sm">
                                <i class="bi bi-facebook me-2"></i>Facebook
                            </a>
                            <a href="https://www.linkedin.com/sharing/share-offsite/?url={{ urlencode($shortUrl) }}" 
                               target="_blank" class="btn btn-outline-primary btn-sm">
                                <i class="bi bi-linkedin me-2"></i>LinkedIn
                            </a>
                            <a href="mailto:?subject={{ urlencode($shortlink->description ?? 'Check out this link!') }}&body={{ urlencode($shortUrl) }}" 
                               class="btn btn-outline-secondary btn-sm">
                                <i class="bi bi-envelope me-2"></i>Email
                            </a>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Modals -->
    <div class="modal fade" id="deleteModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirm Deletion</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete this shortlink?</p>
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        This action will delete all associated click data and cannot be undone.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <form method="POST" action="{{ route('shortlinks.destroy', $shortlink) }}" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">
                            <i class="bi bi-trash me-2"></i>Delete
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="resetStatsModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Reset Statistics</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to reset all click statistics for this shortlink?</p>
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        This will permanently delete all click data and cannot be undone.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <form method="POST" action="{{ route('shortlinks.reset-stats', $shortlink) }}" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-warning">
                            <i class="bi bi-arrow-clockwise me-2"></i>Reset
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script src="https://unpkg.com/qrcode-generator@1.4.4/qrcode.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
    <script>
        // Generate QR Code
        document.addEventListener('DOMContentLoaded', function() {
            // Generate QR Code using qrcode-generator library
            try {
                console.log('Generating QR code...');
                const url = '{{ $shortUrl }}';
                console.log('URL length:', url.length);
                
                // Use type 6 with low error correction for longer URLs
                const qr = qrcode(6, 'L');
                qr.addData(url);
                qr.make();
                document.getElementById('qrcode').innerHTML = qr.createImgTag(2, 4);
                console.log('QR code generated successfully');
            } catch (error) {
                console.error('QR code error:', error);
                const qrContainer = document.getElementById('qrcode');
                if (qrContainer) {
                    qrContainer.innerHTML = '<div class="text-center text-muted p-3"><i class="bi bi-exclamation-triangle"></i><br><small>QR generation failed</small></div>';
                }
            }

            // Load initial analytics with delay to ensure Chart.js is loaded
            setTimeout(() => {
                console.log('DOM ready, loading analytics...');
                loadAnalytics(7);
            }, 100);

            // Period button handlers
            document.querySelectorAll('[data-period]').forEach(btn => {
                btn.addEventListener('click', function() {
                    // Remove active class from all buttons
                    document.querySelectorAll('[data-period]').forEach(b => b.classList.remove('active'));
                    // Add active class to clicked button
                    this.classList.add('active');
                    // Load analytics for selected period
                    loadAnalytics(parseInt(this.dataset.period));
                });
            });
        });

        function copyToClipboard(elementId) {
            const element = document.getElementById(elementId);
            element.select();
            element.setSelectionRange(0, 99999); // For mobile devices
            
            try {
                document.execCommand('copy');
                // Show success feedback
                const btn = event.target.closest('button');
                const originalIcon = btn.innerHTML;
                btn.innerHTML = '<i class="bi bi-check text-success"></i>';
                
                setTimeout(() => {
                    btn.innerHTML = originalIcon;
                }, 2000);
            } catch (err) {
                console.error('Failed to copy: ', err);
            }
        }

        function shareShortlink() {
            if (navigator.share) {
                navigator.share({
                    title: '{{ $shortlink->description ?? "Check out this link!" }}',
                    text: '{{ $shortlink->description ?? "Shared via shortlink" }}',
                    url: '{{ $shortUrl }}'
                }).catch(console.error);
            } else {
                // Fallback - copy to clipboard
                copyToClipboard('shortUrl');
            }
        }

        function downloadQRCode() {
            const canvas = document.querySelector('#qrcode canvas');
            if (canvas) {
                const link = document.createElement('a');
                link.download = 'qr-code-{{ $shortlink->short_code }}.png';
                link.href = canvas.toDataURL();
                link.click();
            }
        }

        function loadAnalytics(days) {
            console.log('Loading analytics for', days, 'days');
            const container = document.getElementById('analyticsContainer');
            
            if (!container) {
                console.error('Analytics container not found');
                return;
            }
            
            container.innerHTML = '<div class="text-center py-5"><i class="bi bi-hourglass-split" style="font-size: 2rem;"></i><p class="mt-2">Loading analytics...</p></div>';

            const url = `{{ route('shortlinks.analytics-data', $shortlink) }}?days=${days}`;
            console.log('Fetching analytics from:', url);

            fetch(url, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'same-origin'
            })
                .then(response => {
                    console.log('Response status:', response.status);
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    console.log('Analytics data received:', data);
                    if (typeof Chart === 'undefined') {
                        throw new Error('Chart.js is not loaded');
                    }
                    renderAnalytics(data);
                })
                .catch(error => {
                    console.error('Error loading analytics:', error);
                    container.innerHTML = `
                        <div class="text-center py-5 text-muted">
                            <i class="bi bi-exclamation-triangle" style="font-size: 2rem;"></i>
                            <p class="mt-2">Failed to load analytics</p>
                            <small class="text-muted">Error: ${error.message}</small>
                        </div>
                    `;
                });
        }

        function renderAnalytics(data) {
            console.log('Rendering analytics with data:', data);
            const container = document.getElementById('analyticsContainer');
            
            if (!container) {
                console.error('Analytics container not found in renderAnalytics');
                return;
            }
            
            if (!data) {
                console.error('No data provided to renderAnalytics');
                container.innerHTML = '<div class="text-center py-5 text-muted"><p>No analytics data available</p></div>';
                return;
            }
            
            container.innerHTML = `
                <div class="row mb-4">
                    <div class="col-md-6">
                        <canvas id="clicksChart" width="400" height="200"></canvas>
                    </div>
                    <div class="col-md-6">
                        <canvas id="locationsChart" width="400" height="200"></canvas>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <canvas id="devicesChart" width="400" height="200"></canvas>
                    </div>
                    <div class="col-md-6">
                        <canvas id="referrersChart" width="400" height="200"></canvas>
                    </div>
                </div>
            `;

            // Clicks over time chart
            try {
                console.log('Creating clicks chart...');
                new Chart(document.getElementById('clicksChart'), {
                    type: 'line',
                    data: {
                        labels: data.dates,
                        datasets: [{
                            label: 'Clicks',
                            data: data.clicks,
                            borderColor: '#0d6efd',
                            backgroundColor: '#0d6efd20',
                            tension: 0.3,
                            fill: true
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            title: {
                                display: true,
                                text: 'Clicks Over Time'
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true
                            }
                        }
                    }
                });
            } catch (error) {
                console.error('Error creating clicks chart:', error);
            }

            // Top locations chart
            try {
                console.log('Creating locations chart...');
                new Chart(document.getElementById('locationsChart'), {
                    type: 'doughnut',
                    data: {
                        labels: data.locations.map(l => l.country),
                        datasets: [{
                            data: data.locations.map(l => l.count),
                            backgroundColor: ['#0d6efd', '#6c757d', '#198754', '#fd7e14', '#dc3545']
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            title: {
                                display: true,
                                text: 'Top Locations'
                            }
                        }
                    }
                });
            } catch (error) {
                console.error('Error creating locations chart:', error);
            }

            // Device types chart
            try {
                console.log('Creating devices chart...');
                new Chart(document.getElementById('devicesChart'), {
                    type: 'bar',
                    data: {
                        labels: data.devices.map(d => d.device_type),
                        datasets: [{
                            label: 'Clicks',
                            data: data.devices.map(d => d.count),
                            backgroundColor: '#198754'
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            title: {
                                display: true,
                                text: 'Device Types'
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true
                            }
                        }
                    }
                });
            } catch (error) {
                console.error('Error creating devices chart:', error);
            }

            // Top referrers chart
            try {
                console.log('Creating referrers chart...');
                new Chart(document.getElementById('referrersChart'), {
                    type: 'bar',
                    indexAxis: 'y',
                    data: {
                        labels: data.referrers.map(r => r.referrer || 'Direct'),
                        datasets: [{
                            label: 'Clicks',
                            data: data.referrers.map(r => r.count),
                            backgroundColor: '#fd7e14'
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            title: {
                                display: true,
                                text: 'Top Referrers'
                            }
                        },
                        scales: {
                            x: {
                                beginAtZero: true
                            }
                        }
                    }
                });
            } catch (error) {
                console.error('Error creating referrers chart:', error);
            }
            
            console.log('All charts created successfully!');
        }

        function confirmDelete() {
            const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
            modal.show();
        }

        function confirmResetStats() {
            const modal = new bootstrap.Modal(document.getElementById('resetStatsModal'));
            modal.show();
        }

        function resetPassword() {
            // Implementation for password reset modal/functionality
            alert('Password reset functionality would be implemented here');
        }

        function addPassword() {
            // Implementation for add password modal/functionality
            alert('Add password functionality would be implemented here');
        }
    </script>
    @endpush
</x-layouts.app>