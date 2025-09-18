<x-layouts.app :title="$title">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-eye me-2"></i>Link Preview
                    </h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i>
                        <strong>Preview Mode:</strong> This is a preview of the shortlink. Click "Continue" to be redirected to the destination.
                    </div>
                    
                    <!-- Shortlink Info -->
                    <div class="row mb-4">
                        <div class="col-md-8">
                            <label class="form-label fw-bold text-muted small">SHORT URL</label>
                            <div class="input-group">
                                <input 
                                    type="text" 
                                    class="form-control" 
                                    value="{{ request()->getSchemeAndHttpHost() }}/{{ $domain }}/{{ $shortCode }}" 
                                    readonly
                                >
                                <button class="btn btn-outline-primary" onclick="copyToClipboard()" title="Copy URL">
                                    <i class="bi bi-copy"></i>
                                </button>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold text-muted small">TOTAL CLICKS</label>
                            <div class="h4 text-primary mb-0">
                                {{ number_format($shortlink->clicks_count) }}
                            </div>
                        </div>
                    </div>

                    <!-- Destination -->
                    <div class="mb-4">
                        <label class="form-label fw-bold text-muted small">DESTINATION URL</label>
                        <div class="input-group">
                            <input 
                                type="text" 
                                class="form-control" 
                                value="{{ $shortlink->original_url }}" 
                                readonly
                            >
                            <a href="{{ $shortlink->original_url }}" target="_blank" class="btn btn-outline-success" title="Visit Original URL">
                                <i class="bi bi-box-arrow-up-right"></i>
                            </a>
                        </div>
                    </div>

                    <!-- Details -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold text-muted small">STATUS</label>
                                <div>
                                    <span class="badge bg-{{ $shortlink->is_active ? 'success' : 'secondary' }} me-2">
                                        {{ $shortlink->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                    @if($shortlink->expires_at)
                                        <span class="badge bg-{{ $shortlink->is_expired ? 'danger' : 'warning' }}">
                                            {{ $shortlink->is_expired ? 'Expired' : 'Expires' }} {{ $shortlink->expires_at->diffForHumans() }}
                                        </span>
                                    @endif
                                    @if($shortlink->password)
                                        <span class="badge bg-warning">Password Protected</span>
                                    @endif
                                </div>
                            </div>
                            
                            @if($shortlink->description)
                                <div class="mb-3">
                                    <label class="form-label fw-bold text-muted small">DESCRIPTION</label>
                                    <p class="mb-0">{{ $shortlink->description }}</p>
                                </div>
                            @endif
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold text-muted small">CREATED</label>
                                <div>{{ $shortlink->created_at->format('M d, Y \a\t H:i') }}</div>
                                <small class="text-muted">{{ $shortlink->created_at->diffForHumans() }}</small>
                            </div>
                            
                            @if($shortlink->expires_at)
                                <div class="mb-3">
                                    <label class="form-label fw-bold text-muted small">EXPIRES</label>
                                    <div>{{ $shortlink->expires_at->format('M d, Y \a\t H:i') }}</div>
                                    <small class="text-{{ $shortlink->is_expired ? 'danger' : 'warning' }}">
                                        {{ $shortlink->is_expired ? 'Expired' : 'Expires' }} {{ $shortlink->expires_at->diffForHumans() }}
                                    </small>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Tags -->
                    @if($shortlink->tags && count($shortlink->tags) > 0)
                        <div class="mb-4">
                            <label class="form-label fw-bold text-muted small">TAGS</label>
                            <div>
                                @foreach($shortlink->tags as $tag)
                                    <span class="badge bg-info me-2">{{ $tag }}</span>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <!-- Actions -->
                    <div class="d-flex justify-content-between align-items-center mt-4">
                        <button onclick="history.back()" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left me-2"></i>Go Back
                        </button>
                        
                        <div>
                            @if($shortlink->is_active && (!$shortlink->expires_at || !$shortlink->is_expired))
                                <a href="{{ route('redirect', ['domain' => $domain, 'shortCode' => $shortCode]) }}" 
                                   class="btn btn-success">
                                    <i class="bi bi-box-arrow-up-right me-2"></i>Continue to Destination
                                </a>
                            @else
                                <button class="btn btn-secondary" disabled>
                                    <i class="bi bi-x-circle me-2"></i>Link Not Available
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Security Notice -->
            <div class="card mt-4">
                <div class="card-header">
                    <h6 class="mb-0 text-warning">
                        <i class="bi bi-shield-exclamation me-2"></i>Security Notice
                    </h6>
                </div>
                <div class="card-body">
                    <p class="mb-2">Before proceeding, please verify:</p>
                    <ul class="mb-0">
                        <li>The destination URL is what you expected</li>
                        <li>You trust the source of this shortlink</li>
                        <li>The URL doesn't seem suspicious or malicious</li>
                    </ul>
                    <div class="alert alert-light mt-3 mb-0">
                        <small class="text-muted">
                            <i class="bi bi-info-circle me-1"></i>
                            Shortlink services can hide the true destination. Always verify links from unknown sources.
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    @push('scripts')
    <script>
        function copyToClipboard() {
            const input = document.querySelector('input[readonly]');
            input.select();
            input.setSelectionRange(0, 99999);
            
            try {
                document.execCommand('copy');
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
    </script>
    @endpush
</x-layouts.app>