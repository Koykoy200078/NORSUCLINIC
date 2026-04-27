@extends('layouts.app')
@section('title')
    Backup & Restore
@endsection
@section('content')
    <div class="container-fluid">
        <div class="d-flex flex-column">
            @include('flash::message')
            <div class="row">
                <div class="col-12">
                    <div class="card shadow-sm border-0">
                        <div class="card-header d-flex justify-content-between align-items-center bg-white py-3">
                            <h3 class="card-title fw-bolder text-gray-800">
                                <i class="fas fa-database me-2 text-primary"></i>Database Backups
                            </h3>
                            <form action="{{ route('backups.create') }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-plus me-1"></i>Create New Backup
                                </button>
                            </form>
                        </div>
                        <div class="card-body">
                            <div class="row mb-10">
                                <div class="col-md-6">
                                    <div class="p-5 border rounded bg-light">
                                        <h4 class="fw-bold mb-4">Import / Restore Data</h4>
                                        <p class="text-muted small mb-4">
                                            <i class="fas fa-exclamation-triangle text-warning me-1"></i>
                                            <strong>Warning:</strong> Importing a backup will overwrite all current data. 
                                            This action cannot be undone. Please ensure you have a backup of your current state.
                                        </p>
                                        <form action="{{ route('backups.import') }}" method="POST" enctype="multipart/form-data" id="importForm">
                                            @csrf
                                            <div class="mb-3">
                                                <label for="backup_file" class="form-label">Select SQL File</label>
                                                <input class="form-control" type="file" id="backup_file" name="backup_file" accept=".sql,.txt" required>
                                            </div>
                                            <button type="button" class="btn btn-warning w-100" onclick="confirmImport()">
                                                <i class="fas fa-upload me-1"></i>Upload & Restore
                                            </button>
                                        </form>
                                    </div>
                                </div>
                                <div class="col-md-6 d-flex align-items-center justify-content-center">
                                    <div class="text-center p-5">
                                        <i class="fas fa-shield-alt fa-4x text-success mb-4 opacity-50"></i>
                                        <h5 class="text-gray-600">Secure Your Clinic Data</h5>
                                        <p class="text-muted small">We recommend creating a backup before performing major system updates or data imports.</p>
                                    </div>
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-hover align-middle table-row-dashed fs-6 gy-5">
                                    <thead>
                                        <tr class="text-start text-muted fw-bolder fs-7 text-uppercase gs-0">
                                            <th>File Name</th>
                                            <th>Size</th>
                                            <th>Created At</th>
                                            <th class="text-end">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody class="text-gray-600 fw-bold">
                                        @forelse($backups as $backup)
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <i class="far fa-file-alt fs-2 text-primary me-3"></i>
                                                        <span class="text-gray-800">{{ $backup['name'] }}</span>
                                                    </div>
                                                </td>
                                                <td>{{ $backup['size'] }}</td>
                                                <td>{{ $backup['created_at'] }}</td>
                                                <td class="text-end">
                                                    <div class="d-flex justify-content-end gap-2">
                                                        <a href="{{ route('backups.download', $backup['name']) }}" class="btn btn-sm btn-icon btn-light-primary" title="Download">
                                                            <i class="fas fa-download"></i>
                                                        </a>
                                                        <form action="{{ route('backups.destroy', $backup['name']) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this backup?');">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-sm btn-icon btn-light-danger" title="Delete">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </form>
                                                    </div>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="4" class="text-center py-10">
                                                    <img src="{{ asset('assets/image/no_record_found.png') }}" alt="No backups" class="mb-4" style="height: 100px;">
                                                    <p class="text-muted">No backups found. Click "Create New Backup" to get started.</p>
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function confirmImport() {
            const fileInput = document.getElementById('backup_file');
            if (!fileInput.value) {
                alert('Please select a file first.');
                return;
            }

            if (confirm('CRITICAL WARNING: This will OVERWRITE your current database with the data from the selected file. This action IS IRREVERSIBLE. Are you absolutely sure you want to proceed?')) {
                const btn = event.target;
                btn.disabled = true;
                btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Restoring...';
                document.getElementById('importForm').submit();
            }
        }
    </script>
@endsection
