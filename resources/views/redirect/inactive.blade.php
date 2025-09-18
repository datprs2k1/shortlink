<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Link Inactive' }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-6 text-center">
                <div class="card shadow-sm">
                    <div class="card-body py-5">
                        <i class="bi bi-pause-circle text-warning" style="font-size: 4rem;"></i>
                        <h2 class="mt-3 text-warning">Link Temporarily Inactive</h2>
                        <p class="text-muted mb-4">This shortlink has been temporarily deactivated by its owner.</p>
                        
                        <div class="alert alert-light border">
                            <code>{{ $shortlink->domain->name }}/{{ $shortlink->short_code }}</code>
                        </div>
                        
                        <div class="row text-center mb-4">
                            <div class="col-6">
                                <div class="text-muted small">CREATED</div>
                                <div class="fw-bold">{{ $shortlink->created_at->format('M d, Y') }}</div>
                            </div>
                            <div class="col-6">
                                <div class="text-muted small">TOTAL CLICKS</div>
                                <div class="fw-bold text-primary">{{ number_format($shortlink->click_count) }}</div>
                            </div>
                        </div>
                        
                        @if($shortlink->description)
                            <div class="alert alert-info">
                                <i class="bi bi-info-circle me-2"></i>
                                {{ $shortlink->description }}
                            </div>
                        @endif
                        
                        <p class="text-muted small mb-4">
                            This link may be reactivated by the owner at any time. Please check back later or contact the link owner for more information.
                        </p>
                        
                        <div class="mt-4">
                            <button onclick="history.back()" class="btn btn-outline-primary me-2">
                                <i class="bi bi-arrow-left me-2"></i>Go Back
                            </button>
                            <button onclick="location.reload()" class="btn btn-primary">
                                <i class="bi bi-arrow-clockwise me-2"></i>Try Again
                            </button>
                        </div>
                    </div>
                </div>
                
                <p class="text-muted small mt-3">Error 410 - Link Gone</p>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>