@extends('Template')

@php
use Illuminate\Support\Str;
@endphp

@section('isi')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-eye"></i> Audit Log Details
                    </h5>
                </div>
                <div class="card-body">
                    <!-- Back Button -->
                    <div class="mb-3">
                        <a href="{{ route('admin.audit.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back to Audit Logs
                        </a>
                    </div>

                    <!-- Log Details -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0">Basic Information</h6>
                                </div>
                                <div class="card-body">
                                    <table class="table table-sm">
                                        <tr>
                                            <td><strong>ID:</strong></td>
                                            <td>{{ $auditLog->id }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Action:</strong></td>
                                            <td>
                                                <span class="badge bg-{{ 
                                                    $auditLog->isSecurityAction() ? 'danger' : 
                                                    ($auditLog->isAdminAction() ? 'warning' : 'info') 
                                                }}">
                                                    {{ $auditLog->action }}
                                                </span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><strong>User:</strong></td>
                                            <td>
                                                @if($auditLog->user)
                                                    <span class="badge bg-{{ $auditLog->user->role == 'admin' ? 'danger' : 'primary' }}">
                                                        {{ $auditLog->user->username }}
                                                    </span>
                                                    <small class="text-muted">({{ $auditLog->user->email }})</small>
                                                @else
                                                    <span class="text-muted">System</span>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><strong>IP Address:</strong></td>
                                            <td>{{ $auditLog->ip_address }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Date/Time:</strong></td>
                                            <td>{{ $auditLog->created_at->format('M d, Y H:i:s') }}</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0">Model Information</h6>
                                </div>
                                <div class="card-body">
                                    <table class="table table-sm">
                                        <tr>
                                            <td><strong>Model Type:</strong></td>
                                            <td>
                                                @if($auditLog->model_type)
                                                    {{ class_basename($auditLog->model_type) }}
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><strong>Model ID:</strong></td>
                                            <td>
                                                @if($auditLog->model_id)
                                                    {{ $auditLog->model_id }}
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><strong>User Agent:</strong></td>
                                            <td>
                                                @if($auditLog->user_agent)
                                                    <small>{{ Str::limit($auditLog->user_agent, 100) }}</small>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Old Values -->
                    @if($auditLog->old_values)
                    <div class="row mt-3">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0">Old Values</h6>
                                </div>
                                <div class="card-body">
                                    <pre>{{ json_encode($auditLog->old_values, JSON_PRETTY_PRINT) }}</pre>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- New Values -->
                    @if($auditLog->new_values)
                    <div class="row mt-3">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0">New Values</h6>
                                </div>
                                <div class="card-body">
                                    <pre>{{ json_encode($auditLog->new_values, JSON_PRETTY_PRINT) }}</pre>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Action Description -->
                    <div class="row mt-3">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0">Action Description</h6>
                                </div>
                                <div class="card-body">
                                    <p>{{ $auditLog->getActionDescriptionAttribute() }}</p>
                                    
                                    @if($auditLog->isSecurityAction())
                                        <div class="alert alert-danger">
                                            <i class="fas fa-exclamation-triangle"></i>
                                            This is a security-related action that should be monitored.
                                        </div>
                                    @endif
                                    
                                    @if($auditLog->isAdminAction())
                                        <div class="alert alert-warning">
                                            <i class="fas fa-user-shield"></i>
                                            This is an administrative action performed by a privileged user.
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
