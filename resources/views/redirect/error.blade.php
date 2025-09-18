<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Server Error' }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-6 text-center">
                <div class="card shadow-sm">
                    <div class="card-body py-5">
                        <i class="bi bi-exclamation-triangle text-warning" style="font-size: 4rem;"></i>
                        <h2 class="mt-3 text-warning">Server Error</h2>
                        <p class="text-muted mb-4">{{ $message ?? 'An error occurred while processing your request.' }}</p>
                        
                        @if(isset($domain) && isset($shortCode))
                            <div class="alert alert-light border">
                                <code>{{ $domain }}/{{ $shortCode }}</code>
                            </div>
                        @endif
                        
                        <p class="text-muted small">What you can try:</p>
                        <ul class="list-unstyled text-muted small text-start">
                            <li><i class="bi bi-dot"></i> Refresh the page and try again</li>
                            <li><i class="bi bi-dot"></i> Check if the link was typed correctly</li>
                            <li><i class="bi bi-dot"></i> Try again later</li>
                            <li><i class="bi bi-dot"></i> Contact support if the problem persists</li>
                        </ul>
                        
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
                
                <p class="text-muted small mt-3">Error 500 - Server Error</p>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>