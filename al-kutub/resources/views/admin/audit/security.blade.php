@extends('Template')

@section('isi')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-danger text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-shield-alt"></i> Security Audit Logs
                    </h5>
                </div>
                <div class="card-body">
                    <!-- Filters -->
                    <form method="GET" class="mb-4">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label for="date_from" class="form-label">From</label>
                                <input type="date" class="form-control" id="date_from" name="date_from" value="{{ request('date_from') }}">
                            </div>
                            <div class="col-md-3">
                                <label for="date_to" class="form-label">To</label>
                                <input type="date" class="form-control" id="date_to" name="date_to" value="{{ request('date_to') }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label d-block">&nbsp;</label>
                                <div class="btn-group w-100">
                                    <button type="submit" class="btn btn-danger">
                                        <i class="fas fa-filter"></i> Filter
                                    </button>
                                    <a href="{{ route('admin.audit.security') }}" class="btn btn-secondary">
                                        <i class="fas fa-times"></i> Clear
                                    </a>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label d-block">&nbsp;</label>
                                <a href="{{ route('admin.audit.export') }}" class="btn btn-outline-success w-100">
                                    <i class="fas fa-download"></i> Export CSV
                                </a>
                            </div>
                        </div>
                    </form>

                    <!-- Security Stats -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="card bg-danger text-white">
                                <div class="card-body text-center">
                                    <h4>{{ \App\Models\AuditLog::where('action', 'login_failed')->count() }}</h4>
                                    <small>Failed Logins</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-success text-white">
                                <div class="card-body text-center">
                                    <h4>{{ \App\Models\AuditLog::where('action', 'login')->count() }}</h4>
                                    <small>Successful Logins</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-warning text-white">
                                <div class="card-body text-center">
                                    <h4>{{ \App\Models\AuditLog::where('action', '2fa_enabled')->count() }}</h4>
                                    <small>2FA Enabled</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-info text-white">
                                <div class="card-body text-center">
                                    <h4>{{ \App\Models\AuditLog::where('action', 'password_changed')->count() }}</h4>
                                    <small>Password Changes</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Security Logs Table -->
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>User</th>
                                    <th>Action</th>
                                    <th>IP Address</th>
                                    <th>Date/Time</th>
                                    <th>Details</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($securityLogs as $log)
                                    <tr>
                                        <td>{{ $log->id }}</td>
                                        <td>
                                            @if($log->user)
                                                <span class="badge bg-{{ $log->user->role == 'admin' ? 'danger' : 'primary' }}">
                                                    {{ $log->user->username }}
                                                </span>
                                            @else
                                                <span class="text-muted">System</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge bg-danger">{{ $log->action }}</span>
                                        </td>
                                        <td>
                                            <small>{{ $log->ip_address }}</small>
                                        </td>
                                        <td>
                                            <small>{{ $log->created_at->format('M d, Y H:i:s') }}</small>
                                        </td>
                                        <td>
                                            @if($log->new_values)
                                                <button type="button" class="btn btn-sm btn-outline-info" data-bs-toggle="modal" data-bs-target="#detailModal{{ $log->id }}">
                                                    <i class="fas fa-eye"></i> Details
                                                </button>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <div>
                            Showing {{ $securityLogs->firstItem() }} to {{ $securityLogs->lastItem() }} 
                            of {{ $securityLogs->total() }} entries
                        </div>
                        {{ $securityLogs->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Detail Modals -->
@foreach($securityLogs as $log)
    @if($log->new_values)
    <div class="modal fade" id="detailModal{{ $log->id }}" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Security Event Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <h6>Event Details:</h6>
                    <pre>{{ json_encode($log->new_values, JSON_PRETTY_PRINT) }}</pre>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
    @endif
@endforeach
@endsection
