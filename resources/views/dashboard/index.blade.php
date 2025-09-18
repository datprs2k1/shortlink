<x-layouts.app :title="$title">
    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <x-stats-card 
                title="Total Shortlinks" 
                :value="number_format($shortlinkStats['total_shortlinks'])"
                icon="link"
                color="primary"
            />
        </div>
        <div class="col-md-3 mb-3">
            <x-stats-card 
                title="Total Clicks" 
                :value="number_format($clickStats['total_clicks'])"
                icon="cursor-fill"
                color="success"
            />
        </div>
        <div class="col-md-3 mb-3">
            <x-stats-card 
                title="Active Domains" 
                :value="number_format($domainStats['active_domains'])"
                icon="globe"
                color="info"
            />
        </div>
        <div class="col-md-3 mb-3">
            <x-stats-card 
                title="Total Users" 
                :value="number_format($userStats['total_users'])"
                icon="people"
                color="warning"
            />
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <x-stats-card 
                title="Today's Clicks" 
                :value="number_format($clickStats['today_clicks'])"
                icon="graph-up-arrow"
                color="success"
            />
        </div>
        <div class="col-md-3 mb-3">
            <x-stats-card 
                title="Active Shortlinks" 
                :value="number_format($shortlinkStats['active_shortlinks'])"
                icon="link-45deg"
                color="primary"
            />
        </div>
        <div class="col-md-3 mb-3">
            <x-stats-card 
                title="Unique IPs" 
                :value="number_format($clickStats['unique_ips'])"
                icon="geo-alt"
                color="info"
            />
        </div>
        <div class="col-md-3 mb-3">
            <x-stats-card 
                title="Countries" 
                :value="number_format($clickStats['countries_count'])"
                icon="flag"
                color="warning"
            />
        </div>
    </div>

    <!-- Main Dashboard Content -->
    <div class="row">
        <!-- Click Trends Chart -->
        <div class="col-lg-8 mb-4">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Click Trends (Last 7 Days)</h5>
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-sm btn-outline-primary active" data-period="7">7 Days</button>
                        <button type="button" class="btn btn-sm btn-outline-primary" data-period="30">30 Days</button>
                        <button type="button" class="btn btn-sm btn-outline-primary" data-period="90">90 Days</button>
                    </div>
                </div>
                <div class="card-body">
                    <canvas id="clickTrendsChart" style="height: 300px;"></canvas>
                </div>
            </div>
        </div>

        <!-- Top Shortlinks -->
        <div class="col-lg-4 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Top Performing Shortlinks</h5>
                </div>
                <div class="card-body p-0">
                    @forelse($topShortlinks as $shortlink)
                        <div class="d-flex align-items-center p-3 {{ !$loop->last ? 'border-bottom' : '' }}">
                            <div class="flex-grow-1">
                                <h6 class="mb-1">
                                    <a href="{{ route('shortlinks.show', $shortlink) }}" class="text-decoration-none">
                                        {{ $shortlink->short_url ?? $shortlink->short_code }}
                                    </a>
                                </h6>
                                <small class="text-muted">{{ Str::limit($shortlink->original_url, 40) }}</small>
                            </div>
                            <div class="text-end">
                                <span class="badge bg-success">{{ number_format($shortlink->clicks_count ?? 0) }}</span>
                                <small class="d-block text-muted">clicks</small>
                            </div>
                        </div>
                    @empty
                        <div class="text-center p-4 text-muted">
                            <i class="bi bi-link-45deg" style="font-size: 2rem;"></i>
                            <p class="mb-0 mt-2">No shortlinks found</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Recent Activity -->
        <div class="col-lg-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Recent Shortlinks</h5>
                </div>
                <div class="card-body p-0">
                    @forelse($recentShortlinks as $shortlink)
                        <div class="d-flex align-items-center p-3 {{ !$loop->last ? 'border-bottom' : '' }}">
                            <div class="me-3">
                                <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                    <i class="bi bi-link"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="mb-1">{{ $shortlink->short_code }}</h6>
                                <small class="text-muted">{{ Str::limit($shortlink->original_url, 50) }}</small>
                            </div>
                            <div class="text-end">
                                <small class="text-muted d-block">{{ $shortlink->created_at->diffForHumans() }}</small>
                                <span class="badge bg-outline-success">{{ $shortlink->clicks_count ?? 0 }} clicks</span>
                            </div>
                        </div>
                    @empty
                        <div class="text-center p-4 text-muted">
                            <i class="bi bi-clock" style="font-size: 2rem;"></i>
                            <p class="mb-0 mt-2">No recent activity</p>
                        </div>
                    @endforelse
                </div>
                @if($recentShortlinks->isNotEmpty())
                    <div class="card-footer text-center">
                        <a href="{{ route('shortlinks.index') }}" class="btn btn-sm btn-outline-primary">
                            View All Shortlinks
                        </a>
                    </div>
                @endif
            </div>
        </div>

        <!-- Domain Statistics -->
        <div class="col-lg-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Domain Statistics</h5>
                </div>
                <div class="card-body p-0">
                    @forelse($domainData as $domain)
                        <div class="d-flex align-items-center p-3 {{ !$loop->last ? 'border-bottom' : '' }}">
                            <div class="me-3">
                                <div class="bg-info text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                    <i class="bi bi-globe"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="mb-1">{{ $domain->name }}</h6>
                                <small class="text-muted">
                                    <span class="badge bg-{{ $domain->is_active ? 'success' : 'secondary' }}">
                                        {{ $domain->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </small>
                            </div>
                            <div class="text-end">
                                <span class="badge bg-primary">{{ $domain->shortlinks_count ?? 0 }}</span>
                                <small class="d-block text-muted">shortlinks</small>
                            </div>
                        </div>
                    @empty
                        <div class="text-center p-4 text-muted">
                            <i class="bi bi-globe" style="font-size: 2rem;"></i>
                            <p class="mb-0 mt-2">No domains found</p>
                        </div>
                    @endforelse
                </div>
                @if($domainData->isNotEmpty())
                    <div class="card-footer text-center">
                        <a href="{{ route('domains.index') }}" class="btn btn-sm btn-outline-primary">
                            View All Domains
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row">
        <div class="col-12 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Quick Actions</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 mb-2">
                            <a href="{{ route('shortlinks.create') }}" class="btn btn-primary w-100">
                                <i class="bi bi-plus-lg me-2"></i>Create Shortlink
                            </a>
                        </div>
                        <div class="col-md-3 mb-2">
                            <a href="{{ route('domains.create') }}" class="btn btn-success w-100">
                                <i class="bi bi-globe-americas me-2"></i>Add Domain
                            </a>
                        </div>
                        <div class="col-md-3 mb-2">
                            <a href="{{ route('analytics.index') }}" class="btn btn-info w-100">
                                <i class="bi bi-graph-up me-2"></i>View Analytics
                            </a>
                        </div>
                        <div class="col-md-3 mb-2">
                            <a href="{{ route('shortlinks.index') }}" class="btn btn-outline-secondary w-100">
                                <i class="bi bi-list me-2"></i>All Shortlinks
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        // Click Trends Chart
        const ctx = document.getElementById('clickTrendsChart').getContext('2d');
        const clickTrendsChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: @json($clickTrends->pluck('date')),
                datasets: [{
                    label: 'Clicks',
                    data: @json($clickTrends->pluck('clicks')),
                    borderColor: 'rgb(13, 110, 253)',
                    backgroundColor: 'rgba(13, 110, 253, 0.1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4
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
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)'
                        }
                    },
                    x: {
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)'
                        }
                    }
                }
            }
        });

        // Period selection for chart
        document.querySelectorAll('[data-period]').forEach(button => {
            button.addEventListener('click', function() {
                // Update active state
                document.querySelectorAll('[data-period]').forEach(btn => btn.classList.remove('active'));
                this.classList.add('active');

                // Fetch new data (you can implement this with AJAX)
                const period = this.dataset.period;
                console.log('Fetching data for period:', period);
                
                // Here you would make an AJAX call to update the chart
                // fetch('/dashboard/stats?period=' + period)...
            });
        });
    </script>
    @endpush
</x-layouts.app>