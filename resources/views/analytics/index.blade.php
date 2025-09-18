<x-layouts.app :title="$title" :actions="'<div class=\'btn-group\'><button class=\'btn btn-outline-primary\' data-bs-toggle=\'modal\' data-bs-target=\'#exportModal\'><i class=\'bi bi-download me-2\'></i>Export Data</button><button class=\'btn btn-outline-success\' id=\'realtimeToggle\'><i class=\'bi bi-broadcast me-2\'></i>Real-time</button></div>'">
    
    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('analytics.index') }}" class="row g-3">
                <div class="col-md-2">
                    <label for="date_from" class="form-label">From Date</label>
                    <input type="date" class="form-control" id="date_from" name="date_from" value="{{ $filters['date_from'] }}">
                </div>
                <div class="col-md-2">
                    <label for="date_to" class="form-label">To Date</label>
                    <input type="date" class="form-control" id="date_to" name="date_to" value="{{ $filters['date_to'] }}">
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
                    <label for="device_type" class="form-label">Device Type</label>
                    <select class="form-select" id="device_type" name="device_type">
                        <option value="">All Devices</option>
                        <option value="mobile" {{ $filters['device_type'] === 'mobile' ? 'selected' : '' }}>Mobile</option>
                        <option value="tablet" {{ $filters['device_type'] === 'tablet' ? 'selected' : '' }}>Tablet</option>
                        <option value="desktop" {{ $filters['device_type'] === 'desktop' ? 'selected' : '' }}>Desktop</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="country" class="form-label">Country</label>
                    <select class="form-select" id="country" name="country">
                        <option value="">All Countries</option>
                        @foreach(array_keys($geoData['countries']) as $country)
                            <option value="{{ $country }}" {{ $filters['country'] === $country ? 'selected' : '' }}>
                                {{ $country }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <div class="btn-group w-100">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-funnel"></i> Filter
                        </button>
                        <a href="{{ route('analytics.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-x-circle"></i>
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Overview Stats -->
    <div class="row mb-4">
        <div class="col-md-2">
            <x-stats-card 
                title="Total Clicks" 
                :value="number_format($stats['total_clicks'])"
                icon="cursor-fill"
                color="primary"
            />
        </div>
        <div class="col-md-2">
            <x-stats-card 
                title="Unique Visitors" 
                :value="number_format($stats['unique_visitors'])"
                icon="people"
                color="success"
            />
        </div>
        <div class="col-md-2">
            <x-stats-card 
                title="Active Shortlinks" 
                :value="number_format($stats['total_shortlinks'])"
                icon="link"
                color="info"
            />
        </div>
        <div class="col-md-2">
            <x-stats-card 
                title="Active Domains" 
                :value="number_format($stats['active_domains'])"
                icon="globe"
                color="warning"
            />
        </div>
        <div class="col-md-2">
            <x-stats-card 
                title="Avg Clicks/Link" 
                :value="$stats['avg_clicks_per_link']"
                icon="graph-up"
                color="secondary"
            />
        </div>
        <div class="col-md-2">
            <x-stats-card 
                title="Top Country" 
                :value="$stats['top_country']"
                icon="flag"
                color="dark"
            />
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <!-- Clicks Over Time Chart -->
            <div class="card mb-4">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="bi bi-graph-up me-2"></i>Clicks Over Time
                        </h5>
                        <div class="btn-group btn-group-sm">
                            <button type="button" class="btn btn-outline-primary active" data-chart-period="daily">Daily</button>
                            <button type="button" class="btn btn-outline-primary" data-chart-period="hourly">Hourly</button>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <canvas id="clicksChart" height="100"></canvas>
                </div>
            </div>

            <!-- Top Performing Shortlinks -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-trophy me-2"></i>Top Performing Shortlinks
                    </h5>
                </div>
                <div class="card-body p-0">
                    @if($topShortlinks->isNotEmpty())
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Rank</th>
                                        <th>Short URL</th>
                                        <th>Original URL</th>
                                        <th>Clicks</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($topShortlinks as $index => $shortlink)
                                        <tr>
                                            <td>
                                                <span class="badge bg-warning">{{ $index + 1 }}</span>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 30px; height: 30px; font-size: 0.8rem;">
                                                        <i class="bi bi-link"></i>
                                                    </div>
                                                    <div>
                                                        <div class="fw-bold">{{ $shortlink->short_code }}</div>
                                                        <small class="text-muted">{{ $shortlink->domain->name }}</small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="text-truncate" style="max-width: 300px;" title="{{ $shortlink->original_url }}">
                                                    {{ Str::limit($shortlink->original_url, 50) }}
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge bg-success">{{ number_format($shortlink->clicks_count) }}</span>
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <a href="{{ route('shortlinks.show', $shortlink->id) }}" class="btn btn-outline-primary" title="View Details">
                                                        <i class="bi bi-eye"></i>
                                                    </a>
                                                    <button class="btn btn-outline-info" onclick="copyShortlink('{{ $shortlink->domain->name }}/{{ $shortlink->short_code }}')"  title="Copy Link">
                                                        <i class="bi bi-copy"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="bi bi-graph-down text-muted" style="font-size: 2rem;"></i>
                            <p class="text-muted mt-2">No shortlinks data for the selected period</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Real-time Activity (Hidden by default) -->
            <div class="card mb-4" id="realtimeCard" style="display: none;">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="bi bi-broadcast-pin me-2"></i>Real-time Activity
                        </h5>
                        <div class="text-muted small">
                            Last updated: <span id="lastUpdated">-</span>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <canvas id="realtimeChart" height="100"></canvas>
                        </div>
                        <div class="col-md-6">
                            <div id="realtimeClicks" class="overflow-auto" style="max-height: 200px;">
                                <!-- Real-time clicks will be populated here -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <!-- Geographic Distribution -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="bi bi-globe me-2"></i>Geographic Distribution
                    </h6>
                </div>
                <div class="card-body">
                    @if(!empty($geoData['cities']))
                        <canvas id="geoChart" height="200"></canvas>
                        <div class="mt-3">
                            @foreach(array_slice($geoData['cities'], 0, 5) as $geo)
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <div>
                                        <i class="bi bi-flag me-1"></i>{{ $geo['country'] }}
                                        <small class="text-muted">({{ number_format($geo['city']) }} unique)</small>
                                    </div>
                                    <span class="badge bg-primary">{{ number_format($geo['click_count']) }}</span>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-3">
                            <i class="bi bi-globe text-muted" style="font-size: 2rem;"></i>
                            <p class="text-muted small mt-2">No geographic data available</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Device Types -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="bi bi-devices me-2"></i>Device Types
                    </h6>
                </div>
                <div class="card-body">
                    @if(!empty($deviceData['devices']))
                        <canvas id="deviceChart" height="150" style="max-height: 150px !important; height: 150px !important;"></canvas>
                        <div class="mt-3">
                            @foreach($deviceData['devices'] as $device)
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <div>
                                        <i class="bi bi-{{ $device['device_type'] === 'Mobile' ? 'phone' : ($device['device_type'] === 'Tablet' ? 'tablet' : 'laptop') }} me-1"></i>
                                        {{ $device['device_type'] }}
                                    </div>
                                    <span class="badge bg-info">{{ number_format($device['count']) }}</span>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-3">
                            <i class="bi bi-devices text-muted" style="font-size: 2rem;"></i>
                            <p class="text-muted small mt-2">No device data available</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Top Referrers -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="bi bi-link-45deg me-2"></i>Top Referrers
                    </h6>
                </div>
                <div class="card-body">
                    @if(!empty($referrerData['top_referrers']))
                        @foreach($referrerData['top_referrers'] as $referrer)
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div class="flex-grow-1">
                                    <div class="fw-bold">{{ $referrer['referer'] }}</div>
                                    <div class="progress mt-1" style="height: 4px;">
                                        <div class="progress-bar bg-success" style="width: {{ ($referrer['click_count'] / $referrerData['top_referrers'][0]['click_count']) * 100 }}%"></div>
                                    </div>
                                </div>
                                <span class="badge bg-success ms-2">{{ number_format($referrer['click_count']) }}</span>
                            </div>
                        @endforeach
                    @else
                        <div class="text-center py-3">
                            <i class="bi bi-link-45deg text-muted" style="font-size: 2rem;"></i>
                            <p class="text-muted small mt-2">No referrer data available</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="bi bi-lightning me-2"></i>Quick Actions
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('shortlinks.create') }}" class="btn btn-primary btn-sm">
                            <i class="bi bi-plus-circle me-2"></i>Create Shortlink
                        </a>
                        <a href="{{ route('domains.create') }}" class="btn btn-outline-primary btn-sm">
                            <i class="bi bi-globe-central-south-asia me-2"></i>Add Domain
                        </a>
                        <button class="btn btn-outline-success btn-sm" onclick="refreshAnalytics()">
                            <i class="bi bi-arrow-clockwise me-2"></i>Refresh Data
                        </button>
                        <button class="btn btn-outline-info btn-sm" data-bs-toggle="modal" data-bs-target="#exportModal">
                            <i class="bi bi-download me-2"></i>Export Report
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Export Modal -->
    <div class="modal fade" id="exportModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Export Analytics Data</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="{{ route('analytics.export') }}">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="export_format" class="form-label">Export Format</label>
                            <select class="form-select" id="export_format" name="format" required>
                                <option value="csv">CSV</option>
                                <option value="xlsx">Excel (XLSX)</option>
                                <option value="json">JSON</option>
                            </select>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="export_date_from" class="form-label">From Date</label>
                                    <input type="date" class="form-control" id="export_date_from" name="date_from" value="{{ $filters['date_from'] }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="export_date_to" class="form-label">To Date</label>
                                    <input type="date" class="form-control" id="export_date_to" name="date_to" value="{{ $filters['date_to'] }}">
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="export_domain" class="form-label">Domain (Optional)</label>
                            <select class="form-select" id="export_domain" name="domain_id">
                                <option value="">All Domains</option>
                                @foreach($domains as $domain)
                                    <option value="{{ $domain->id }}">{{ $domain->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-download me-2"></i>Export
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
    <script>
        let clicksChart, geoChart, deviceChart, realtimeChart;
        let realtimeInterval;
        let isRealtimeActive = false;

        document.addEventListener('DOMContentLoaded', function() {
            initializeCharts();
            
            // Real-time toggle
            document.getElementById('realtimeToggle').addEventListener('click', function() {
                toggleRealtime();
            });

            // Chart period buttons
            document.querySelectorAll('[data-chart-period]').forEach(btn => {
                btn.addEventListener('click', function() {
                    document.querySelectorAll('[data-chart-period]').forEach(b => b.classList.remove('active'));
                    this.classList.add('active');
                    // Implement period change logic here
                });
            });
        });

        function initializeCharts() {
            // Clicks over time chart
            const clicksCtx = document.getElementById('clicksChart').getContext('2d');
            clicksChart = new Chart(clicksCtx, {
                type: 'line',
                data: {
                    labels: @json($chartData['dates']),
                    datasets: [{
                        label: 'Clicks',
                        data: @json($chartData['clicks']),
                        borderColor: '#0d6efd',
                        backgroundColor: '#0d6efd20',
                        tension: 0.3,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                precision: 0
                            }
                        }
                    }
                }
            });

            // Geographic chart
            @if(!empty($geoData['cities']))
            const geoCtx = document.getElementById('geoChart').getContext('2d');
            geoChart = new Chart(geoCtx, {
                type: 'doughnut',
                data: {
                    labels: @json($geoData->take(5)->pluck('country')),
                    datasets: [{
                        data: @json($geoData->take(5)->pluck('clicks')),
                        backgroundColor: [
                            '#0d6efd', '#6c757d', '#198754', '#fd7e14', '#dc3545'
                        ]
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    }
                }
            });
            @endif

            // Device types chart
            @if(!empty($deviceData['devices']))
            const deviceCtx = document.getElementById('deviceChart').getContext('2d');
            deviceChart = new Chart(deviceCtx, {
                type: 'pie',
                data: {
                    labels: @json(collect($deviceData['devices'])->pluck('device_type')),
                    datasets: [{
                        data: @json(collect($deviceData['devices'])->pluck('count')),
                        backgroundColor: ['#198754', '#fd7e14', '#0d6efd', '#6c757d']
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    }
                }
            });
            @endif
        }

        function toggleRealtime() {
            const btn = document.getElementById('realtimeToggle');
            const card = document.getElementById('realtimeCard');
            
            if (isRealtimeActive) {
                // Stop real-time
                clearInterval(realtimeInterval);
                btn.innerHTML = '<i class="bi bi-broadcast me-2"></i>Real-time';
                btn.classList.remove('btn-success');
                btn.classList.add('btn-outline-success');
                card.style.display = 'none';
                isRealtimeActive = false;
            } else {
                // Start real-time
                btn.innerHTML = '<i class="bi bi-broadcast-pin me-2"></i>Stop Real-time';
                btn.classList.remove('btn-outline-success');
                btn.classList.add('btn-success');
                card.style.display = 'block';
                isRealtimeActive = true;
                
                // Initialize real-time chart
                initRealtimeChart();
                
                // Start polling
                realtimeInterval = setInterval(fetchRealtimeData, 5000);
                fetchRealtimeData(); // Initial load
            }
        }

        function initRealtimeChart() {
            const ctx = document.getElementById('realtimeChart').getContext('2d');
            realtimeChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: [],
                    datasets: [{
                        label: 'Clicks',
                        data: [],
                        borderColor: '#198754',
                        backgroundColor: '#19875420',
                        tension: 0.3,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                precision: 0
                            }
                        }
                    },
                    animation: {
                        duration: 0
                    }
                }
            });
        }

        function fetchRealtimeData() {
            fetch('/analytics/realtime')
                .then(response => response.json())
                .then(data => {
                    updateRealtimeChart(data.chart_data);
                    updateRealtimeClicks(data.recent_clicks);
                    updateLastUpdated();
                })
                .catch(error => {
                    console.error('Real-time fetch error:', error);
                });
        }

        function updateRealtimeChart(data) {
            realtimeChart.data.labels = data.map(d => d.time);
            realtimeChart.data.datasets[0].data = data.map(d => d.clicks);
            realtimeChart.update();
        }

        function updateRealtimeClicks(clicks) {
            const container = document.getElementById('realtimeClicks');
            container.innerHTML = clicks.map(click => `
                <div class="d-flex justify-content-between align-items-center mb-2 small">
                    <div>
                        <strong>${click.time}</strong>
                        <span class="text-muted">${click.domain}/${click.short_code}</span>
                    </div>
                    <div class="text-end">
                        <div>${click.country} • ${click.device_type}</div>
                        <small class="text-muted">${click.browser}</small>
                    </div>
                </div>
            `).join('');
        }

        function updateLastUpdated() {
            document.getElementById('lastUpdated').textContent = new Date().toLocaleTimeString();
        }

        function copyShortlink(url) {
            const fullUrl = window.location.protocol + '//' + url;
            navigator.clipboard.writeText(fullUrl).then(() => {
                // Show success feedback
                const btn = event.target.closest('button');
                const originalIcon = btn.innerHTML;
                btn.innerHTML = '<i class="bi bi-check text-success"></i>';
                
                setTimeout(() => {
                    btn.innerHTML = originalIcon;
                }, 2000);
            }).catch(() => {
                alert('Failed to copy URL to clipboard');
            });
        }

        function refreshAnalytics() {
            location.reload();
        }
    </script>
    @endpush
</x-layouts.app>