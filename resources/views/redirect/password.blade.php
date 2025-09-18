<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Password Required' }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow-sm">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="bi bi-shield-lock me-2"></i>Password Required</h5>
                    </div>
                    <div class="card-body">
                        <p class="text-muted">This shortlink is protected. Please enter the password to proceed.</p>
                        
                        <form method="POST" action="@if(isset($isDirect) && $isDirect){{ route('direct.redirect.verify', ['shortCode' => $shortCode]) }}@else{{ route('redirect.verify', ['domain' => $domain, 'shortCode' => $shortCode]) }}@endif">
                            @csrf
                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password" required autofocus>
                                @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-unlock me-2"></i>Unlock
                                </button>
                            </div>
                        </form>
                    </div>
                    <div class="card-footer text-muted small">
                        <i class="bi bi-link-45deg me-1"></i>@if(isset($isDirect) && $isDirect){{ $shortCode }}@else{{ $domain }}/{{ $shortCode }}@endif
                    </div>
                </div>
                <p class="text-center text-muted small mt-3">If you believe this is an error, contact the link owner.</p>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>