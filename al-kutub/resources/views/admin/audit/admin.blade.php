@extends('Template')

@section('isi')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-warning text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-user-shield"></i> Admin Action Logs
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
                                    <button type="submit" class="btn btn-warning">
                                        <i class="fas fa-filter"></i> Filter
                                    </button>
                                    <a href="{{ route('admin.audit.admin') }}" class="btn btn-secondary">
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

                    <!-- Admin Action Stats -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="card bg-primary text-white">
                                <div class="card-body text-center">
                                    <h4>{{ \App\Models\AuditLog::where('action', 'kitab_created')->count() }}</h4>
                                    <small>Kitabs Created</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-danger text-white">
                                <div class="card-body text-center">
                                    <h4>{{ \App\Models\AuditLog::where('action', 'kitab_deleted')->count() }}</h4>
                                    <small>Kitabs Deleted</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-warning text-white">
                                <div class="card-body text-center">
                                    <h4>{{ \App\Models\AuditLog::where('action', 'user_deleted')->count() }}</h4>
                                    <small>Users Deleted</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-info text-white">
                                <div class="card-body text-center">
                                    <h4>{{ \App\Models\AuditLog::where('action', 'role_updated')->count() }}</h4>
                                    <small>Role Changes</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Admin Action Logs Table -->
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Admin User</th>
                                    <th>Action</th>
                                    <th>Target</th>
                                    <th>IP Address</th>
                                    <th>Date/Time</th>
                                    <th>Details</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($adminLogs as $log)
                                    <tr>
                                        <td>{{ $log->id }}</td>
                                        <td>
                                            @if($log->user)
                                                <span class="badge bg-danger">
                                                    {{ $log->user->username }}
                                                </span>
                                            @else
                                                <span class="text-muted">System</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge bg-warning">{{ $log->action }}</span>
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
                                        <td>
                                            <small>{{ $log->ip_address }}</small>
                                        </td>
                                        <td>
                                            <small>{{ $log->created_at->format('M d, Y H:i:s') }}</small>
                                        </td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-outline-info" data-bs-toggle="modal" data-bs-target="#detailModal{{ $log->id }}">
                                                <i class="fas fa-eye"></i> Details
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <div>
                            Showing {{ $adminLogs->firstItem() }} to {{ $adminLogs->lastItem() }} 
                            of {{ $adminLogs->total() }} entries
                        </div>
                        {{ $adminLogs->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Detail Modals -->
@foreach($adminLogs as $log)
    <div class="modal fade" id="detailModal{{ $log->id }}" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Admin Action Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <strong>Action:</strong> {{ $log->action }}<br>
                        <strong>Admin:</strong> {{ $log->user ? $log->user->username : 'System' }}<br>
                        <strong>IP Address:</strong> {{ $log->ip_address }}<br>
                        <strong>Date/Time:</strong> {{ $log->created_at->format('M d, Y H:i:s') }}
                    </div>
                    
                    @if($log->old_values)
                        <h6>Old Values:</h6>
                        <pre>{{ json_encode($log->old_values, JSON_PRETTY_PRINT) }}</pre>
                    @endif
                    
                    @if($log->new_values)
                        <h6>New Values:</h6>
                        <pre>{{ json_encode($log->new_values, JSON_PRETTY_PRINT) }}</pre>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
@endforeach
@endsection
