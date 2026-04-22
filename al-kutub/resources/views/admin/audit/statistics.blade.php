@extends('Template')

@section('isi')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-chart-bar"></i> Audit Statistics
                    </h5>
                </div>
                <div class="card-body">
                    <!-- Statistics Cards -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="card bg-primary text-white">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h4 class="mb-0">{{ number_format($stats['total_logs']) }}</h4>
                                            <small>Total Logs</small>
                                        </div>
                                        <div class="align-self-center">
                                            <i class="fas fa-list fa-2x opacity-75"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-success text-white">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h4 class="mb-0">{{ number_format($stats['today_logs']) }}</h4>
                                            <small>Today's Logs</small>
                                        </div>
                                        <div class="align-self-center">
                                            <i class="fas fa-calendar-day fa-2x opacity-75"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-danger text-white">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h4 class="mb-0">{{ number_format($stats['security_events']) }}</h4>
                                            <small>Security Events</small>
                                        </div>
                                        <div class="align-self-center">
                                            <i class="fas fa-shield-alt fa-2x opacity-75"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-warning text-white">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h4 class="mb-0">{{ number_format($stats['admin_actions']) }}</h4>
                                            <small>Admin Actions</small>
                                        </div>
                                        <div class="align-self-center">
                                            <i class="fas fa-user-shield fa-2x opacity-75"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Security Metrics -->
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <div class="card border-info">
                                <div class="card-header bg-info text-white">
                                    <h6 class="mb-0">Authentication Metrics</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row text-center">
                                        <div class="col-6">
                                            <h5 class="text-success">{{ number_format($stats['successful_logins']) }}</h5>
                                            <small>Successful Logins</small>
                                        </div>
                                        <div class="col-6">
                                            <h5 class="text-danger">{{ number_format($stats['failed_logins']) }}</h5>
                                            <small>Failed Logins</small>
                                        </div>
                                    </div>
                                    @if($stats['successful_logins'] + $stats['failed_logins'] > 0)
                                        <div class="mt-2">
                                            <small class="text-muted">Success Rate:</small>
                                            <div class="progress" style="height: 10px;">
                                                <div class="progress-bar bg-success" style="width: {{ round(($stats['successful_logins'] / ($stats['successful_logins'] + $stats['failed_logins'])) * 100) }}%"></div>
                                            </div>
                                            <small>{{ round(($stats['successful_logins'] / ($stats['successful_logins'] + $stats['failed_logins'])) * 100) }}%</small>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card border-success">
                                <div class="card-header bg-success text-white">
                                    <h6 class="mb-0">2FA Statistics</h6>
                                </div>
                                <div class="card-body text-center">
                                    <h4 class="text-success">{{ number_format($stats['users_with_2fa']) }}</h4>
                                    <small>Users with 2FA Enabled</small>
                                    <div class="mt-2">
                                        <small class="text-muted">Out of {{ \App\Models\User::count() }} total users</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card border-warning">
                                <div class="card-header bg-warning text-white">
                                    <h6 class="mb-0">Recent Activity</h6>
                                </div>
                                <div class="card-body">
                                    <small class="text-muted">Last 24 hours:</small>
                                    <h5 class="text-info">{{ \App\Models\AuditLog::where('created_at', '>=', now()->subDay())->count() }}</h5>
                                    <small>audit events recorded</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Activity Chart -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0">Activity Trend (Last 7 Days)</h6>
                                </div>
                                <div class="card-body">
                                    <canvas id="activityChart" height="100"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Activity -->
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0">Recent Activity</h6>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-sm">
                                            <thead>
                                                <tr>
                                                    <th>Time</th>
                                                    <th>User</th>
                                                    <th>Action</th>
                                                    <th>IP Address</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($recentActivity as $activity)
                                                    <tr>
                                                        <td>
                                                            <small>{{ $activity->created_at->format('H:i:s') }}</small>
                                                        </td>
                                                        <td>
                                                            @if($activity->user)
                                                                <span class="badge bg-{{ $activity->user->role == 'admin' ? 'danger' : 'primary' }}">
                                                                    {{ $activity->user->username }}
                                                                </span>
                                                            @else
                                                                <span class="text-muted">System</span>
                                                            @endif
                                                        </td>
                                                        <td>
                                                            <span class="badge bg-{{ 
                                                                $activity->isSecurityAction() ? 'danger' : 
                                                                ($activity->isAdminAction() ? 'warning' : 'info') 
                                                            }}">
                                                                {{ $activity->action }}
                                                            </span>
                                                        </td>
                                                        <td>
                                                            <small>{{ $activity->ip_address }}</small>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Activity Chart
const ctx = document.getElementById('activityChart').getContext('2d');
const activityChart = new Chart(ctx, {
    type: 'line',
    data: {
        labels: [
            @foreach($activityByDay as $date => $count)
                '{{ \Carbon\Carbon::parse($date)->format('M d') }}',
            @endforeach
        ],
        datasets: [{
            label: 'Activity Count',
            data: [
                @foreach($activityByDay as $date => $count)
                    {{ $count }},
                @endforeach
            ],
            borderColor: 'rgb(75, 192, 192)',
            backgroundColor: 'rgba(75, 192, 192, 0.2)',
            tension: 0.1
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});
</script>
@endsection
