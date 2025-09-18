<x-layouts.app :title="$title" :breadcrumbs="$breadcrumbs">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Add New Domain</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('domains.store') }}" method="POST">
                        @csrf
                        
                        <div class="mb-3">
                            <label for="name" class="form-label">Domain Name <span class="text-danger">*</span></label>
                            <input 
                                type="text" 
                                class="form-control @error('name') is-invalid @enderror" 
                                id="name" 
                                name="name" 
                                value="{{ old('name') }}"
                                placeholder="example.com"
                                required
                            >
                            <div class="form-text">
                                Enter the domain name without protocol (e.g., example.com, not https://example.com)
                            </div>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input 
                                    class="form-check-input @error('is_active') is-invalid @enderror" 
                                    type="checkbox" 
                                    id="is_active" 
                                    name="is_active" 
                                    value="1"
                                    {{ old('is_active', '1') ? 'checked' : '' }}
                                >
                                <label class="form-check-label" for="is_active">
                                    Active Domain
                                </label>
                            </div>
                            <div class="form-text">
                                Active domains can be used to create shortlinks
                            </div>
                            @error('is_active')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <div class="alert alert-info">
                                <i class="bi bi-info-circle me-2"></i>
                                <strong>Note:</strong> Make sure the domain is properly configured to point to your shortlink service before creating shortlinks.
                            </div>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('domains.index') }}" class="btn btn-secondary">
                                <i class="bi bi-arrow-left me-2"></i>Back to Domains
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-plus-lg me-2"></i>Create Domain
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Domain Setup Guide -->
            <div class="card mt-4">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="bi bi-lightbulb me-2"></i>Domain Setup Guide
                    </h6>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-3">To use your domain for shortlinks, you need to:</p>
                    <ol class="text-muted mb-0">
                        <li>Point your domain's DNS to your shortlink service server</li>
                        <li>Configure SSL certificate for HTTPS (recommended)</li>
                        <li>Test the domain configuration</li>
                        <li>Create your first shortlink using this domain</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        // Auto-format domain name input
        document.getElementById('name').addEventListener('input', function() {
            let value = this.value.toLowerCase();
            
            // Remove protocol if entered
            value = value.replace(/^https?:\/\//, '');
            
            // Remove trailing slash
            value = value.replace(/\/$/, '');
            
            this.value = value;
        });

        // Form validation
        document.querySelector('form').addEventListener('submit', function(e) {
            const domainInput = document.getElementById('name');
            const domainName = domainInput.value.trim();
            
            if (!domainName) {
                e.preventDefault();
                domainInput.focus();
                return false;
            }
            
            // Basic domain validation
            const domainRegex = /^[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
            if (!domainRegex.test(domainName)) {
                e.preventDefault();
                alert('Please enter a valid domain name (e.g., example.com)');
                domainInput.focus();
                return false;
            }
        });
    </script>
    @endpush
</x-layouts.app>