@extends('Template')

@section('isi')
<div class="page-heading mb-4">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <h3 class="fw-bold text-dark">Audit Logs</h3>
            <p class="text-subtitle text-muted mb-0">Monitoring & Security tracking.</p>
        </div>
        <div class="d-flex gap-2">
            <button onclick="refreshAuditLogs()" class="btn btn-outline-primary shadow-sm btn-sm px-3">
                <i class="bi bi-arrow-clockwise"></i> Refresh
            </button>
            <a href="{{ route('admin.audit.export') }}" class="btn btn-success shadow-sm btn-sm px-3">
                <i class="bi bi-download"></i> Export CSV
            </a>
        </div>
    </div>
</div>

<section class="row">
    <!-- Main Content Column -->
    <div class="col-12">
        <!-- Stats Cards Row -->
        <div class="row">
            <!-- Total Logs -->
            <div class="col-6 col-lg-3 col-md-6 mb-4">
                <div class="card animate-card border-0 text-white shadow-lg" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                    <div class="card-body p-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-white-50 small mb-1">Total Logs</h6>
                                <h3 class="fw-bold mb-0 count-up" data-count="{{ $totalLogs ?? 0 }}">{{ number_format($totalLogs ?? 0) }}</h3>
                                <small class="opacity-75" style="font-size: 0.7rem;">All activities</small>
                            </div>
                            <div class="stats-icon bg-white bg-opacity-25 text-white" style="width: 45px; height: 45px;">
                                <i class="fas fa-history"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Today's Logs -->
            <div class="col-6 col-lg-3 col-md-6 mb-4">
                <div class="card animate-card border-0 text-white shadow-lg" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                    <div class="card-body p-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-white-50 small mb-1">Today</h6>
                                <h3 class="fw-bold mb-0 count-up" data-count="{{ $todayLogs ?? 0 }}">{{ number_format($todayLogs ?? 0) }}</h3>
                                <small class="opacity-75" style="font-size: 0.7rem;">24 hours</small>
                            </div>
                            <div class="stats-icon bg-white bg-opacity-25 text-white" style="width: 45px; height: 45px;">
                                <i class="fas fa-calendar-day"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Security Events -->
            <div class="col-6 col-lg-3 col-md-6 mb-4">
                <div class="card animate-card border-0 text-white shadow-lg" style="background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);">
                    <div class="card-body p-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-white-50 small mb-1">Security</h6>
                                <h3 class="fw-bold mb-0 count-up" data-count="{{ $securityLogs ?? 0 }}">{{ number_format($securityLogs ?? 0) }}</h3>
                                <small class="opacity-75" style="font-size: 0.7rem;">Security events</small>
                            </div>
                            <div class="stats-icon bg-white bg-opacity-25 text-white" style="width: 45px; height: 45px;">
                                <i class="fas fa-shield-alt"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Admin Actions -->
            <div class="col-6 col-lg-3 col-md-6 mb-4">
                <div class="card animate-card border-0 text-white shadow-lg" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                    <div class="card-body p-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-white-50 small mb-1">Admin</h6>
                                <h3 class="fw-bold mb-0 count-up" data-count="{{ $adminLogs ?? 0 }}">{{ number_format($adminLogs ?? 0) }}</h3>
                                <small class="opacity-75" style="font-size: 0.7rem;">Admin actions</small>
                            </div>
                            <div class="stats-icon bg-white bg-opacity-25 text-white" style="width: 45px; height: 45px;">
                                <i class="fas fa-user-shield"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex gap-2 flex-wrap">
                            <a href="{{ route('admin.audit.index') }}" class="btn btn-primary btn-sm">
                                <i class="fas fa-list"></i> All Logs
                            </a>
                            <a href="{{ route('admin.audit.security') }}" class="btn btn-danger btn-sm">
                                <i class="fas fa-shield-alt"></i> Security Events
                            </a>
                            <a href="{{ route('admin.audit.admin') }}" class="btn btn-warning btn-sm">
                                <i class="fas fa-user-shield"></i> Admin Actions
                            </a>
                            <a href="{{ route('admin.audit.statistics') }}" class="btn btn-info btn-sm">
                                <i class="fas fa-chart-bar"></i> Statistics
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters Section -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-0">
                <h6 class="mb-0"><i class="fas fa-filter me-2"></i>Filter Logs</h6>
            </div>
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-3">
                        <label for="action" class="form-label">Action</label>
                        <select class="form-select" id="action" name="action">
                            <option value="">All Actions</option>
                            <option value="login" {{ request('action') == 'login' ? 'selected' : '' }}>Login</option>
                            <option value="login_failed" {{ request('action') == 'login_failed' ? 'selected' : '' }}>Failed Login</option>
                            <option value="2fa_enabled" {{ request('action') == '2fa_enabled' ? 'selected' : '' }}>2FA Enabled</option>
                            <option value="2fa_disabled" {{ request('action') == '2fa_disabled' ? 'selected' : '' }}>2FA Disabled</option>
                            <option value="kitab_created" {{ request('action') == 'kitab_created' ? 'selected' : '' }}>Kitab Created</option>
                            <option value="kitab_deleted" {{ request('action') == 'kitab_deleted' ? 'selected' : '' }}>Kitab Deleted</option>
                            <option value="user_deleted" {{ request('action') == 'user_deleted' ? 'selected' : '' }}>User Deleted</option>
                            <option value="role_updated" {{ request('action') == 'role_updated' ? 'selected' : '' }}>Role Updated</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="user_id" class="form-label">User</label>
                        <select class="form-select" id="user_id" name="user_id">
                            <option value="">All Users</option>
                            @foreach($users ?? [] as $user)
                                <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                                    {{ $user->username }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="date_from" class="form-label">From</label>
                        <input type="date" class="form-control" id="date_from" name="date_from" value="{{ request('date_from') }}">
                    </div>
                    <div class="col-md-2">
                        <label for="date_to" class="form-label">To</label>
                        <input type="date" class="form-control" id="date_to" name="date_to" value="{{ request('date_to') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label d-block">&nbsp;</label>
                        <div class="btn-group w-100">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-filter"></i> Filter
                            </button>
                            <a href="{{ route('admin.audit.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Clear
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Audit Logs Table -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0">
                <h6 class="mb-0"><i class="fas fa-history me-2"></i>Audit Logs</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>User</th>
                                <th>Action</th>
                                <th>Model</th>
                                <th>IP Address</th>
                                <th>Date/Time</th>
                                <th>Details</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($auditLogs ?? [] as $log)
                                <tr>
                                    <td><span class="badge bg-secondary">#{{ $log->id }}</span></td>
                                    <td>
                                        @if($log->user)
                                            <div>
                                                <strong>{{ $log->user->username }}</strong>
                                                @if($log->user->role == 'admin')
                                                    <span class="badge bg-danger ms-1">Admin</span>
                                                @else
                                                    <span class="badge bg-primary ms-1">User</span>
                                                @endif
                                            </div>
                                            <small class="text-muted">{{ $log->user->email }}</small>
                                        @else
                                            <span class="text-muted">System</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($log->isSecurityAction())
                                            <span class="badge bg-danger">{{ $log->action }}</span>
                                        @elseif($log->isAdminAction())
                                            <span class="badge bg-warning">{{ $log->action }}</span>
                                        @else
                                            <span class="badge bg-info">{{ $log->action }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($log->model_type)
                                            <small>{{ class_basename($log->model_type) }}</small>
                                            @if($log->model_id)
                                                <br><small class="text-muted">#{{ $log->model_id }}</small>
                                            @endif
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td><small>{{ $log->ip_address }}</small></td>
                                    <td><small>{{ optional($log->created_at)->format('M d, Y H:i:s') ?? '-' }}</small></td>
                                    <td>
                                        <a href="{{ route('admin.audit.show', $log->id) }}" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-eye"></i> View
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if(!($auditLogs ?? false) || $auditLogs->isEmpty())
                    <div class="text-center py-4">
                        <i class="fas fa-history fa-3x text-muted mb-3"></i>
                        <h6 class="text-muted">No audit logs found</h6>
                        <p class="text-muted">No activities match your filter criteria.</p>
                    </div>
                @endif

                <!-- Pagination -->
                @if(isset($auditLogs) && $auditLogs->hasPages())
                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <div>
                            Showing {{ $auditLogs->firstItem() }} to {{ $auditLogs->lastItem() }} 
                            of {{ $auditLogs->total() }} entries
                        </div>
                        {{ $auditLogs->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</section>

<style>
.animate-card {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.animate-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.15) !important;
}

.stats-icon {
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
}

.count-up {
    font-size: 1.5rem;
    line-height: 1.2;
}

.badge {
    font-size: 0.75rem;
    padding: 0.25rem 0.5rem;
}

.table th {
    border-bottom: 2px solid #dee2e6;
    font-weight: 600;
    font-size: 0.875rem;
}

.table td {
    vertical-align: middle;
    font-size: 0.875rem;
}

.table-hover tbody tr:hover {
    background-color: rgba(0,0,0,.025);
}
</style>

<script>
function refreshAuditLogs() {
    location.reload();
}

// Animate numbers on page load
document.addEventListener('DOMContentLoaded', function() {
    const counters = document.querySelectorAll('.count-up');
    const speed = 200;

    counters.forEach(counter => {
        const animate = () => {
            const target = +counter.getAttribute('data-count');
            const count = +counter.innerText.replace(/,/g, '');
            const increment = target / speed;

            if (count < target) {
                counter.innerText = Math.ceil(count + increment).toLocaleString();
                setTimeout(animate, 1);
            } else {
                counter.innerText = target.toLocaleString();
            }
        };

        animate();
    });
});
</script>
@endsection
