<x-layouts.app :title="$title" :actions="'<a href=\'' . route('shortlinks.index') . '\' class=\'btn btn-outline-secondary\'><i class=\'bi bi-arrow-left me-2\'></i>Back to Shortlinks</a>'">
    <div class="row">
        <div class="col-lg-8">
            <!-- Main Create Form -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-plus-circle me-2"></i>Create New Shortlink
                    </h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('shortlinks.store') }}" id="shortlinkForm">
                        @csrf
                        
                        <!-- Original URL -->
                        <div class="mb-4">
                            <label for="original_url" class="form-label fw-bold">
                                Original URL <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="bi bi-globe"></i>
                                </span>
                                <input 
                                    type="url" 
                                    class="form-control form-control-lg @error('original_url') is-invalid @enderror" 
                                    id="original_url" 
                                    name="original_url" 
                                    value="{{ old('original_url') }}"
                                    placeholder="https://example.com/very-long-url"
                                    required
                                    autocomplete="url"
                                >
                                <button type="button" class="btn btn-outline-secondary" onclick="validateUrl()" title="Validate URL">
                                    <i class="bi bi-check-circle"></i>
                                </button>
                            </div>
                            @error('original_url')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">
                                Enter the long URL you want to shorten. The URL will be validated before creating the shortlink.
                            </div>
                            <div id="urlValidationResult" class="mt-2" style="display: none;"></div>
                        </div>

                        <!-- Domain Selection -->
                        <div class="mb-4">
                            <label for="domain_id" class="form-label fw-bold">
                                Domain <span class="text-danger">*</span>
                            </label>
                            <select class="form-select @error('domain_id') is-invalid @enderror" id="domain_id" name="domain_id" required>
                                <option value="">Select a domain</option>
                                @foreach($domains as $domain)
                                    <option value="{{ $domain->id }}" {{ old('domain_id') == $domain->id ? 'selected' : '' }}>
                                        {{ $domain->name }}
                                        @if(!$domain->is_active)
                                            <span class="text-muted">(Inactive)</span>
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                            @error('domain_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            @if($domains->isEmpty())
                                <div class="alert alert-warning mt-2">
                                    <i class="bi bi-exclamation-triangle me-2"></i>
                                    No domains available. <a href="{{ route('domains.create') }}">Create a domain first</a>.
                                </div>
                            @endif
                        </div>

                        <!-- Short Code Options -->
                        <div class="mb-4">
                            <label class="form-label fw-bold">Short Code Options</label>
                            
                            <div class="form-check mb-2">
                                <input 
                                    class="form-check-input" 
                                    type="radio" 
                                    name="code_type" 
                                    id="auto_generate" 
                                    value="auto" 
                                    {{ old('code_type', 'auto') === 'auto' ? 'checked' : '' }}
                                >
                                <label class="form-check-label" for="auto_generate">
                                    <strong>Auto-generate</strong> - System will create a random short code
                                </label>
                            </div>
                            
                            <div class="form-check">
                                <input 
                                    class="form-check-input" 
                                    type="radio" 
                                    name="code_type" 
                                    id="custom_code" 
                                    value="custom"
                                    {{ old('code_type') === 'custom' ? 'checked' : '' }}
                                >
                                <label class="form-check-label" for="custom_code">
                                    <strong>Custom code</strong> - Choose your own short code
                                </label>
                            </div>

                            <div id="customCodeInput" class="mt-3" style="{{ old('code_type') === 'custom' ? '' : 'display: none;' }}">
                                <div class="input-group">
                                    <span class="input-group-text" id="domainPrefix">domain.com/</span>
                                    <input 
                                        type="text" 
                                        class="form-control @error('short_code') is-invalid @enderror" 
                                        id="short_code" 
                                        name="short_code" 
                                        value="{{ old('short_code') }}"
                                        placeholder="my-custom-code"
                                        pattern="[a-zA-Z0-9\-_]+"
                                        title="Only letters, numbers, hyphens, and underscores allowed"
                                    >
                                    <button type="button" class="btn btn-outline-secondary" onclick="checkAvailability()" title="Check availability">
                                        <i class="bi bi-search"></i>
                                    </button>
                                </div>
                                @error('short_code')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">
                                    Only letters, numbers, hyphens (-), and underscores (_) are allowed. 3-50 characters.
                                </div>
                                <div id="availabilityResult" class="mt-2" style="display: none;"></div>
                            </div>
                        </div>

                        <!-- Advanced Options -->
                        <div class="card bg-light">
                            <div class="card-header">
                                <button 
                                    class="btn btn-link p-0 text-decoration-none" 
                                    type="button" 
                                    data-bs-toggle="collapse" 
                                    data-bs-target="#advancedOptions"
                                >
                                    <i class="bi bi-chevron-down me-2"></i>Advanced Options
                                </button>
                            </div>
                            <div class="collapse" id="advancedOptions">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <!-- Description -->
                                            <div class="mb-3">
                                                <label for="description" class="form-label">Description</label>
                                                <textarea 
                                                    class="form-control @error('description') is-invalid @enderror" 
                                                    id="description" 
                                                    name="description" 
                                                    rows="3"
                                                    maxlength="500"
                                                    placeholder="Optional description for this shortlink..."
                                                >{{ old('description') }}</textarea>
                                                @error('description')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                                <div class="form-text">
                                                    <span id="descriptionCount">0</span>/500 characters
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <!-- Expiration Date -->
                                            <div class="mb-3">
                                                <label for="expires_at" class="form-label">Expiration Date</label>
                                                <input 
                                                    type="datetime-local" 
                                                    class="form-control @error('expires_at') is-invalid @enderror" 
                                                    id="expires_at" 
                                                    name="expires_at" 
                                                    value="{{ old('expires_at') }}"
                                                    min="{{ now()->format('Y-m-d\TH:i') }}"
                                                >
                                                @error('expires_at')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                                <div class="form-text">
                                                    Leave empty for permanent shortlink
                                                </div>
                                            </div>

                                            <!-- Password Protection -->
                                            <div class="mb-3">
                                                <label for="password" class="form-label">Password Protection</label>
                                                <input 
                                                    type="password" 
                                                    class="form-control @error('password') is-invalid @enderror" 
                                                    id="password" 
                                                    name="password" 
                                                    value="{{ old('password') }}"
                                                    placeholder="Optional password"
                                                    autocomplete="new-password"
                                                >
                                                @error('password')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                                <div class="form-text">
                                                    Users will need this password to access the link
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Tags -->
                                    <div class="mb-3">
                                        <label for="tags" class="form-label">Tags</label>
                                        <input 
                                            type="text" 
                                            class="form-control @error('tags') is-invalid @enderror" 
                                            id="tags" 
                                            name="tags" 
                                            value="{{ old('tags') }}"
                                            placeholder="marketing, campaign, social-media"
                                            data-bs-toggle="tooltip"
                                            title="Separate tags with commas"
                                        >
                                        @error('tags')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <div class="form-text">
                                            Separate multiple tags with commas. Use for organizing and filtering shortlinks.
                                        </div>
                                    </div>

                                    <!-- Status -->
                                    <div class="form-check">
                                        <input 
                                            class="form-check-input" 
                                            type="checkbox" 
                                            id="is_active" 
                                            name="is_active" 
                                            value="1"
                                            {{ old('is_active', true) ? 'checked' : '' }}
                                        >
                                        <label class="form-check-label" for="is_active">
                                            <strong>Active</strong> - Shortlink will be immediately accessible
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="d-flex justify-content-between align-items-center mt-4">
                            <a href="{{ route('shortlinks.index') }}" class="btn btn-outline-secondary">
                                <i class="bi bi-x-circle me-2"></i>Cancel
                            </a>
                            
                            <div>
                                <button type="button" class="btn btn-outline-primary me-2" onclick="previewShortlink()">
                                    <i class="bi bi-eye me-2"></i>Preview
                                </button>
                                <button type="submit" class="btn btn-primary" id="submitBtn">
                                    <i class="bi bi-plus-circle me-2"></i>Create Shortlink
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <!-- Preview Card -->
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="bi bi-eye me-2"></i>Preview
                    </h6>
                </div>
                <div class="card-body">
                    <div id="shortlinkPreview">
                        <div class="text-center text-muted py-4">
                            <i class="bi bi-link" style="font-size: 2rem;"></i>
                            <p class="mt-2">Fill in the form to see a preview</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tips Card -->
            <div class="card mt-4">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="bi bi-lightbulb me-2"></i>Tips & Guidelines
                    </h6>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled mb-0">
                        <li class="mb-2">
                            <i class="bi bi-check-circle text-success me-2"></i>
                            <strong>Custom codes</strong> are great for branding but check availability first
                        </li>
                        <li class="mb-2">
                            <i class="bi bi-check-circle text-success me-2"></i>
                            <strong>Auto-generated codes</strong> are always unique and available immediately
                        </li>
                        <li class="mb-2">
                            <i class="bi bi-check-circle text-success me-2"></i>
                            <strong>Tags</strong> help organize shortlinks for campaigns and analytics
                        </li>
                        <li class="mb-2">
                            <i class="bi bi-check-circle text-success me-2"></i>
                            <strong>Password protection</strong> adds an extra security layer
                        </li>
                        <li>
                            <i class="bi bi-check-circle text-success me-2"></i>
                            <strong>Expiration dates</strong> are useful for time-sensitive campaigns
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Recent Activity -->
            @if(isset($recentShortlinks) && $recentShortlinks->isNotEmpty())
                <div class="card mt-4">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="bi bi-clock me-2"></i>Recently Created
                        </h6>
                    </div>
                    <div class="card-body p-0">
                        <div class="list-group list-group-flush">
                            @foreach($recentShortlinks as $recent)
                                <div class="list-group-item d-flex justify-content-between align-items-start">
                                    <div class="ms-2 me-auto">
                                        <div class="fw-bold">{{ $recent->short_code }}</div>
                                        <small class="text-muted">
                                            {{ Str::limit($recent->original_url, 30) }}
                                        </small>
                                    </div>
                                    <span class="badge bg-primary rounded-pill">{{ $recent->click_count }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    @push('scripts')
    <script>
        // Initialize form interactions
        document.addEventListener('DOMContentLoaded', function() {
            // Handle code type radio buttons
            const autoGenerate = document.getElementById('auto_generate');
            const customCode = document.getElementById('custom_code');
            const customCodeInput = document.getElementById('customCodeInput');

            autoGenerate.addEventListener('change', function() {
                if (this.checked) {
                    customCodeInput.style.display = 'none';
                }
            });

            customCode.addEventListener('change', function() {
                if (this.checked) {
                    customCodeInput.style.display = 'block';
                    document.getElementById('short_code').focus();
                }
            });

            // Update domain prefix when domain changes
            const domainSelect = document.getElementById('domain_id');
            const domainPrefix = document.getElementById('domainPrefix');
            
            domainSelect.addEventListener('change', function() {
                const selectedOption = this.options[this.selectedIndex];
                if (selectedOption.value) {
                    domainPrefix.textContent = selectedOption.text + '/';
                } else {
                    domainPrefix.textContent = 'domain.com/';
                }
                updatePreview();
            });

            // Character counter for description
            const descriptionTextarea = document.getElementById('description');
            const descriptionCount = document.getElementById('descriptionCount');
            
            descriptionTextarea.addEventListener('input', function() {
                descriptionCount.textContent = this.value.length;
            });

            // Initial count
            descriptionCount.textContent = descriptionTextarea.value.length;

            // Auto-preview on form changes
            const formInputs = document.querySelectorAll('#shortlinkForm input, #shortlinkForm select, #shortlinkForm textarea');
            formInputs.forEach(input => {
                input.addEventListener('input', updatePreview);
                input.addEventListener('change', updatePreview);
            });

            // Initialize tooltips
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });

            // Initial preview update
            updatePreview();
        });

        function validateUrl() {
            const urlInput = document.getElementById('original_url');
            const resultDiv = document.getElementById('urlValidationResult');
            const url = urlInput.value.trim();

            if (!url) {
                resultDiv.style.display = 'none';
                return;
            }

            // Show loading state
            resultDiv.innerHTML = '<div class="alert alert-info mb-0"><i class="bi bi-hourglass-split me-2"></i>Validating URL...</div>';
            resultDiv.style.display = 'block';

            // Basic URL validation
            try {
                new URL(url);
                
                // Additional validation via fetch (optional)
                fetch('/api/validate-url', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({ url: url })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.valid) {
                        resultDiv.innerHTML = '<div class="alert alert-success mb-0"><i class="bi bi-check-circle me-2"></i>URL is valid and accessible</div>';
                    } else {
                        resultDiv.innerHTML = '<div class="alert alert-warning mb-0"><i class="bi bi-exclamation-triangle me-2"></i>URL might not be accessible: ' + (data.message || 'Unknown error') + '</div>';
                    }
                })
                .catch(error => {
                    resultDiv.innerHTML = '<div class="alert alert-success mb-0"><i class="bi bi-check-circle me-2"></i>URL format is valid</div>';
                });
            } catch (error) {
                resultDiv.innerHTML = '<div class="alert alert-danger mb-0"><i class="bi bi-x-circle me-2"></i>Invalid URL format</div>';
            }
        }

        function checkAvailability() {
            const shortCodeInput = document.getElementById('short_code');
            const domainSelect = document.getElementById('domain_id');
            const resultDiv = document.getElementById('availabilityResult');
            
            const shortCode = shortCodeInput.value.trim();
            const domainId = domainSelect.value;

            if (!shortCode || !domainId) {
                resultDiv.style.display = 'none';
                return;
            }

            // Show loading state
            resultDiv.innerHTML = '<div class="alert alert-info mb-0"><i class="bi bi-hourglass-split me-2"></i>Checking availability...</div>';
            resultDiv.style.display = 'block';

            fetch('/api/check-shortcode-availability', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ 
                    short_code: shortCode, 
                    domain_id: domainId 
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.available) {
                    resultDiv.innerHTML = '<div class="alert alert-success mb-0"><i class="bi bi-check-circle me-2"></i>Short code is available!</div>';
                } else {
                    resultDiv.innerHTML = '<div class="alert alert-danger mb-0"><i class="bi bi-x-circle me-2"></i>Short code is already taken</div>';
                }
            })
            .catch(error => {
                resultDiv.innerHTML = '<div class="alert alert-warning mb-0"><i class="bi bi-exclamation-triangle me-2"></i>Could not check availability</div>';
            });
        }

        function updatePreview() {
            const preview = document.getElementById('shortlinkPreview');
            
            // Get form values
            const originalUrl = document.getElementById('original_url').value;
            const domainId = document.getElementById('domain_id').value;
            const domainText = domainId ? document.getElementById('domain_id').options[document.getElementById('domain_id').selectedIndex].text : '';
            const codeType = document.querySelector('input[name="code_type"]:checked')?.value;
            const shortCode = document.getElementById('short_code').value;
            const description = document.getElementById('description').value;
            const expiresAt = document.getElementById('expires_at').value;
            const isActive = document.getElementById('is_active').checked;

            if (!originalUrl && !domainId) {
                preview.innerHTML = '<div class="text-center text-muted py-4"><i class="bi bi-link" style="font-size: 2rem;"></i><p class="mt-2">Fill in the form to see a preview</p></div>';
                return;
            }

            let previewHtml = '<div class="shortlink-preview">';
            
            // Short URL preview
            if (domainText) {
                const displayCode = (codeType === 'custom' && shortCode) ? shortCode : 'abc123';
                previewHtml += `
                    <div class="mb-3">
                        <label class="form-label text-muted small">SHORT URL</label>
                        <div class="border rounded p-2 bg-light">
                            <i class="bi bi-link me-2"></i>
                            <strong>${domainText}/${displayCode}</strong>
                        </div>
                    </div>
                `;
            }
            
            // Original URL preview
            if (originalUrl) {
                previewHtml += `
                    <div class="mb-3">
                        <label class="form-label text-muted small">REDIRECTS TO</label>
                        <div class="border rounded p-2 bg-light">
                            <i class="bi bi-arrow-right me-2"></i>
                            <span style="word-break: break-all;">${originalUrl}</span>
                        </div>
                    </div>
                `;
            }
            
            // Status badges
            previewHtml += '<div class="mb-3">';
            previewHtml += '<span class="badge bg-' + (isActive ? 'success' : 'secondary') + ' me-2">' + (isActive ? 'Active' : 'Inactive') + '</span>';
            
            if (expiresAt) {
                const expireDate = new Date(expiresAt);
                previewHtml += '<span class="badge bg-warning">Expires ' + expireDate.toLocaleDateString() + '</span>';
            } else {
                previewHtml += '<span class="badge bg-info">Permanent</span>';
            }
            previewHtml += '</div>';
            
            // Description
            if (description) {
                previewHtml += `
                    <div class="mb-2">
                        <label class="form-label text-muted small">DESCRIPTION</label>
                        <p class="mb-0">${description}</p>
                    </div>
                `;
            }
            
            previewHtml += '</div>';
            preview.innerHTML = previewHtml;
        }

        function previewShortlink() {
            // Force update preview
            updatePreview();
            
            // Scroll to preview on mobile
            if (window.innerWidth < 992) {
                document.getElementById('shortlinkPreview').scrollIntoView({ behavior: 'smooth' });
            }
        }

        // Form validation before submit
        document.getElementById('shortlinkForm').addEventListener('submit', function(e) {
            const submitBtn = document.getElementById('submitBtn');
            const originalText = submitBtn.innerHTML;
            
            // Update button to show loading state
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="bi bi-hourglass-split me-2"></i>Creating...';
            
            // Re-enable button after a delay in case of validation errors
            setTimeout(() => {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            }, 5000);
        });
    </script>
    @endpush
</x-layouts.app>