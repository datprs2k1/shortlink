@props(['title', 'value', 'icon', 'color' => 'primary', 'change' => null])

<div class="card stats-card {{ $color }}">
    <div class="card-body">
        <div class="row align-items-center">
            <div class="col">
                <h6 class="card-title text-uppercase text-muted mb-2">{{ $title }}</h6>
                <span class="h2 font-weight-bold mb-0">{{ $value }}</span>
                @if($change)
                    <div class="mt-2">
                        <span class="badge bg-{{ $change > 0 ? 'success' : 'danger' }}">
                            <i class="bi bi-{{ $change > 0 ? 'arrow-up' : 'arrow-down' }}"></i>
                            {{ abs($change) }}%
                        </span>
                        <small class="text-muted ms-2">from last month</small>
                    </div>
                @endif
            </div>
            <div class="col-auto">
                <i class="bi bi-{{ $icon }} text-{{ $color }}" style="font-size: 2.5rem;"></i>
            </div>
        </div>
    </div>
</div>