@extends('layouts.admin')

@section('title', 'Security Management')

@section('content')
<div class="container-fluid">
    <div class="row">
        <!-- Security Navigation -->
        <div class="col-md-3 col-lg-2">
            <div class="card shadow-sm">
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        <a href="{{ route('admin.security.teams.index') }}" 
                           class="list-group-item list-group-item-action {{ request()->routeIs('admin.security.teams.*') ? 'active' : '' }}">
                            <i class="fas fa-users me-2"></i> Teams
                        </a>
                        <a href="{{ route('admin.security.zones.index') }}"
                           class="list-group-item list-group-item-action {{ request()->routeIs('admin.security.zones.*') ? 'active' : '' }}">
                            <i class="fas fa-map-marked-alt me-2"></i> Zones
                        </a>
                        <a href="{{ route('admin.security.shifts.index') }}"
                           class="list-group-item list-group-item-action {{ request()->routeIs('admin.security.shifts.*') ? 'active' : '' }}">
                            <i class="fas fa-clock me-2"></i> Shifts
                        </a>
                        <a href="{{ route('admin.security.checkpoints.index') }}"
                           class="list-group-item list-group-item-action {{ request()->routeIs('admin.security.checkpoints.*') ? 'active' : '' }}">
                            <i class="fas fa-map-marker-alt me-2"></i> Checkpoints
                        </a>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card shadow-sm mt-4">
                <div class="card-header bg-light">
                    <h5 class="card-title mb-0">Quick Actions</h5>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        <a href="{{ route('admin.security.teams.create') }}" class="list-group-item list-group-item-action">
                            <i class="fas fa-plus-circle me-2"></i> New Team
                        </a>
                        <a href="{{ route('admin.security.zones.create') }}" class="list-group-item list-group-item-action">
                            <i class="fas fa-plus-circle me-2"></i> New Zone
                        </a>
                        <a href="{{ route('admin.security.shifts.create') }}" class="list-group-item list-group-item-action">
                            <i class="fas fa-plus-circle me-2"></i> New Shift
                        </a>
                        <a href="{{ route('admin.security.checkpoints.create') }}" class="list-group-item list-group-item-action">
                            <i class="fas fa-plus-circle me-2"></i> New Checkpoint
                        </a>
                    </div>
                </div>
            </div>

            <!-- Active Shifts -->
            <div class="card shadow-sm mt-4">
                <div class="card-header bg-light">
                    <h5 class="card-title mb-0">Active Shifts</h5>
                </div>
                <div class="card-body p-0">
                    @php
                        $activeShifts = \App\Models\SecurityShift::where('status', 'in_progress')
                            ->with(['team', 'zone'])
                            ->latest()
                            ->take(5)
                            ->get();
                    @endphp

                    @if($activeShifts->count() > 0)
                        <div class="list-group list-group-flush">
                            @foreach($activeShifts as $shift)
                                <a href="{{ route('admin.security.shifts.show', $shift) }}" 
                                   class="list-group-item list-group-item-action">
                                    <div class="d-flex w-100 justify-content-between">
                                        <h6 class="mb-1">{{ $shift->team->name }}</h6>
                                        <small>{{ $shift->started_at->diffForHumans() }}</small>
                                    </div>
                                    <p class="mb-1">{{ $shift->zone->name }}</p>
                                </a>
                            @endforeach
                        </div>
                    @else
                        <div class="p-3 text-center text-muted">
                            No active shifts
                        </div>
                    @endif
                </div>
            </div>

            <!-- Recent Incidents -->
            <div class="card shadow-sm mt-4">
                <div class="card-header bg-light">
                    <h5 class="card-title mb-0">Recent Incidents</h5>
                </div>
                <div class="card-body p-0">
                    @php
                        $recentIncidents = \App\Models\ShiftIncident::with(['shift.team', 'shift.zone'])
                            ->latest()
                            ->take(5)
                            ->get();
                    @endphp

                    @if($recentIncidents->count() > 0)
                        <div class="list-group list-group-flush">
                            @foreach($recentIncidents as $incident)
                                <a href="{{ route('admin.security.shifts.show', $incident->shift) }}" 
                                   class="list-group-item list-group-item-action">
                                    <div class="d-flex w-100 justify-content-between">
                                        <h6 class="mb-1">{{ $incident->title }}</h6>
                                        <small class="text-{{ $incident->severity === 'high' ? 'danger' : ($incident->severity === 'medium' ? 'warning' : 'info') }}">
                                            {{ ucfirst($incident->severity) }}
                                        </small>
                                    </div>
                                    <p class="mb-1">{{ $incident->shift->team->name }} - {{ $incident->shift->zone->name }}</p>
                                    <small>{{ $incident->created_at->diffForHumans() }}</small>
                                </a>
                            @endforeach
                        </div>
                    @else
                        <div class="p-3 text-center text-muted">
                            No recent incidents
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="col-md-9 col-lg-10">
            @yield('security_content')
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .list-group-item-action:hover {
        background-color: #f8f9fa;
    }
    .list-group-item-action.active {
        background-color: #0d6efd;
        border-color: #0d6efd;
    }
    .card {
        border: none;
        border-radius: 0.5rem;
    }
    .card-header {
        border-bottom: none;
        background-color: transparent;
        padding: 1rem;
    }
    .card-title {
        color: #495057;
        font-size: 1rem;
        font-weight: 600;
    }
</style>
@endpush

@push('scripts')
<script>
    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl)
    })

    // Handle active link highlighting
    document.addEventListener('DOMContentLoaded', function() {
        const currentPath = window.location.pathname
        const navLinks = document.querySelectorAll('.list-group-item-action')
        
        navLinks.forEach(link => {
            if (currentPath.includes(link.getAttribute('href'))) {
                link.classList.add('active')
            }
        })
    })
</script>
@endpush 