@extends('layouts.admin')

@section('title', 'Absensi Siswa')
@section('header', 'Absensi Siswa')

@section('content')
<!-- Alert Messages -->
@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2 py-3">
        <h5 class="card-title mb-0">Absensi Siswa - {{ \Carbon\Carbon::parse($date)->format('d F Y') }}</h5>
        <div class="d-flex gap-2">
            <input type="date" id="dateFilter" value="{{ $date }}" class="form-control form-control-sm" style="width: auto;">
        </div>
    </div>
    
    <!-- Search & Filter -->
    <div class="card-body bg-light border-bottom">
        <form action="{{ route('admin.attendance.index') }}" method="GET" id="filterForm">
            <input type="hidden" name="date" value="{{ $date }}">
            <div class="row g-2">
                <div class="col-12 col-md-5">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama atau NIS..." class="form-control">
                </div>
                <div class="col-12 col-md-4">
                    <select name="class_id" class="form-select">
                        <option value="">Semua Kelas</option>
                        @foreach($classes ?? [] as $class)
                            <option value="{{ $class->id }}" {{ request('class_id') == $class->id ? 'selected' : '' }}>
                                {{ $class->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-3">
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-secondary flex-grow-1">Cari</button>
                        <a href="{{ route('admin.attendance.index', ['date' => $date]) }}" class="btn btn-outline-secondary flex-grow-1">Reset</a>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <div class="card-body p-0 p-md-3">
        <!-- Desktop Table -->
        <div class="table-responsive d-none d-md-block">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>No</th>
                        <th>NIS</th>
                        <th>Nama</th>
                        <th>Kelas</th>
                        <th>Clock In</th>
                        <th>Clock Out</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($students ?? [] as $index => $student)
                    @php
                        $attendance = $student->attendances->first();
                    @endphp
                    <tr data-student-id="{{ $student->id }}">
                        <td>{{ $students->firstItem() + $index }}</td>
                        <td>{{ $student->student_code }}</td>
                        <td>{{ $student->user->name ?? '-' }}</td>
                        <td>{{ $student->class->name ?? '-' }}</td>
                        <td class="clock-in-time">
                            {{ $attendance && $attendance->clock_in ? \Carbon\Carbon::parse($attendance->clock_in)->format('H:i') : '-' }}
                        </td>
                        <td class="clock-out-time">
                            {{ $attendance && $attendance->clock_out ? \Carbon\Carbon::parse($attendance->clock_out)->format('H:i') : '-' }}
                        </td>
                        <td class="status-badge">
                            @if($attendance)
                                {!! $attendance->status_badge !!}
                            @else
                                <span class="badge bg-light text-dark">Belum Absen</span>
                            @endif
                        </td>
                        <td>
                            <div class="d-flex gap-1 flex-wrap">
                                @if(!$attendance || !$attendance->clock_in)
                                    <button type="button" class="btn btn-success btn-sm btn-clock-in" data-student-id="{{ $student->id }}">
                                        <i class="fas fa-sign-in-alt"></i> Clock In
                                    </button>
                                @elseif($attendance && $attendance->clock_in && !$attendance->clock_out)
                                    <button type="button" class="btn btn-warning btn-sm btn-clock-out" data-student-id="{{ $student->id }}">
                                        <i class="fas fa-sign-out-alt"></i> Clock Out
                                    </button>
                                @endif
                                
                                @if(!$attendance || !$attendance->clock_out)
                                    <button type="button" class="btn btn-info btn-sm btn-note" data-student-id="{{ $student->id }}" data-bs-toggle="modal" data-bs-target="#noteModal">
                                        <i class="fas fa-sticky-note"></i> Note
                                    </button>
                                @else
                                    <button type="button" class="btn btn-secondary btn-sm" disabled title="Absensi sudah lengkap">
                                        <i class="fas fa-sticky-note"></i> Note
                                    </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted py-4">Belum ada data siswa</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Mobile Cards -->
        <div class="d-md-none">
            @forelse($students ?? [] as $index => $student)
            @php
                $attendance = $student->attendances->first();
            @endphp
            <div class="border-bottom p-3" data-student-id="{{ $student->id }}">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div>
                        <h6 class="mb-1">{{ $student->user->name ?? '-' }}</h6>
                        <small class="text-muted">{{ $student->student_code }} - {{ $student->class->name ?? '-' }}</small>
                    </div>
                    <div class="status-badge">
                        @if($attendance)
                            {!! $attendance->status_badge !!}
                        @else
                            <span class="badge bg-light text-dark">Belum Absen</span>
                        @endif
                    </div>
                </div>
                
                <div class="row g-2 small text-muted mb-2">
                    <div class="col-6">
                        <strong>Clock In:</strong> 
                        <span class="clock-in-time">
                            {{ $attendance && $attendance->clock_in ? \Carbon\Carbon::parse($attendance->clock_in)->format('H:i') : '-' }}
                        </span>
                    </div>
                    <div class="col-6">
                        <strong>Clock Out:</strong> 
                        <span class="clock-out-time">
                            {{ $attendance && $attendance->clock_out ? \Carbon\Carbon::parse($attendance->clock_out)->format('H:i') : '-' }}
                        </span>
                    </div>
                </div>
                
                <div class="d-flex gap-2 flex-wrap">
                    @if(!$attendance || !$attendance->clock_in)
                        <button type="button" class="btn btn-success btn-sm btn-clock-in" data-student-id="{{ $student->id }}">
                            <i class="fas fa-sign-in-alt"></i> Clock In
                        </button>
                    @elseif($attendance && $attendance->clock_in && !$attendance->clock_out)
                        <button type="button" class="btn btn-warning btn-sm btn-clock-out" data-student-id="{{ $student->id }}">
                            <i class="fas fa-sign-out-alt"></i> Clock Out
                        </button>
                    @endif
                    
                    @if(!$attendance || !$attendance->clock_out)
                        <button type="button" class="btn btn-info btn-sm btn-note" data-student-id="{{ $student->id }}" data-bs-toggle="modal" data-bs-target="#noteModal">
                            <i class="fas fa-sticky-note"></i> Note
                        </button>
                    @else
                        <button type="button" class="btn btn-secondary btn-sm" disabled title="Absensi sudah lengkap">
                            <i class="fas fa-sticky-note"></i> Note
                        </button>
                    @endif
                </div>
            </div>
            @empty
            <div class="text-center text-muted py-4">Belum ada data siswa</div>
            @endforelse
        </div>

        @if($students instanceof \Illuminate\Pagination\LengthAwarePaginator && $students->hasPages())
        <div class="p-3">
            {{ $students->appends(request()->query())->links() }}
        </div>
        @endif
    </div>
</div>

<!-- Note Modal -->
<div class="modal fade" id="noteModal" tabindex="-1" aria-labelledby="noteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="noteModalLabel">Update Status & Keterangan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="noteForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                        <select class="form-select" id="status" name="status" required>
                            <option value="">Pilih Status</option>
                            <option value="sick">Sakit</option>
                            <option value="permission">Izin</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="notes" class="form-label">Keterangan <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="notes" name="notes" rows="3" placeholder="Masukkan keterangan..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">
                        <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                        Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    let currentStudentId = null;
    
    // Date filter change
    document.getElementById('dateFilter').addEventListener('change', function() {
        const date = this.value;
        const url = new URL(window.location);
        url.searchParams.set('date', date);
        window.location.href = url.toString();
    });
    
    // Clock In buttons
    document.querySelectorAll('.btn-clock-in').forEach(button => {
        button.addEventListener('click', function() {
            const studentId = this.getAttribute('data-student-id');
            const originalText = this.innerHTML;
            
            // Show loading
            this.innerHTML = '<span class="spinner-border spinner-border-sm" role="status"></span> Loading...';
            this.disabled = true;
            
            fetch(`/admin/attendance/${studentId}/clock-in`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update UI
                    const row = document.querySelector(`[data-student-id="${studentId}"]`);
                    row.querySelector('.clock-in-time').textContent = data.data.clock_in;
                    row.querySelector('.status-badge').innerHTML = data.data.status_badge;
                    
                    // Change button to clock out
                    this.className = 'btn btn-warning btn-sm btn-clock-out';
                    this.innerHTML = '<i class="fas fa-sign-out-alt"></i> Clock Out';
                    this.setAttribute('data-student-id', studentId);
                    
                    // Add new event listener for clock out
                    this.removeEventListener('click', arguments.callee);
                    addClockOutListener(this);
                    
                    showToast('success', data.message);
                } else {
                    showToast('error', data.message);
                    this.innerHTML = originalText;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('error', 'Terjadi kesalahan saat clock in');
                this.innerHTML = originalText;
            })
            .finally(() => {
                this.disabled = false;
            });
        });
    });
    
    // Clock Out buttons
    function addClockOutListener(button) {
        button.addEventListener('click', function() {
            const studentId = this.getAttribute('data-student-id');
            const originalText = this.innerHTML;
            
            // Show loading
            this.innerHTML = '<span class="spinner-border spinner-border-sm" role="status"></span> Loading...';
            this.disabled = true;
            
            fetch(`/admin/attendance/${studentId}/clock-out`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update UI
                    const row = document.querySelector(`[data-student-id="${studentId}"]`);
                    row.querySelector('.clock-out-time').textContent = data.data.clock_out;
                    
                    // Remove clock out button
                    this.remove();
                    
                    // Disable note button
                    const noteButton = row.querySelector('.btn-note');
                    if (noteButton) {
                        noteButton.className = 'btn btn-secondary btn-sm';
                        noteButton.disabled = true;
                        noteButton.title = 'Absensi sudah lengkap';
                        noteButton.removeAttribute('data-bs-toggle');
                        noteButton.removeAttribute('data-bs-target');
                    }
                    
                    showToast('success', data.message);
                } else {
                    showToast('error', data.message);
                    this.innerHTML = originalText;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('error', 'Terjadi kesalahan saat clock out');
                this.innerHTML = originalText;
            })
            .finally(() => {
                this.disabled = false;
            });
        });
    }
    
    // Initialize existing clock out buttons
    document.querySelectorAll('.btn-clock-out').forEach(addClockOutListener);
    
    // Note buttons
    document.querySelectorAll('.btn-note').forEach(button => {
        button.addEventListener('click', function() {
            currentStudentId = this.getAttribute('data-student-id');
        });
    });
    
    // Note form submission
    document.getElementById('noteForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        if (!currentStudentId) return;
        
        const formData = new FormData(this);
        const submitBtn = this.querySelector('button[type="submit"]');
        const spinner = submitBtn.querySelector('.spinner-border');
        
        // Show loading
        submitBtn.disabled = true;
        spinner.classList.remove('d-none');
        
        fetch(`/admin/attendance/${currentStudentId}/note`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update UI
                const row = document.querySelector(`[data-student-id="${currentStudentId}"]`);
                row.querySelector('.status-badge').innerHTML = data.data.status_badge;
                
                // Ensure clock in button is still visible if no clock_in yet
                const clockInBtn = row.querySelector('.btn-clock-in');
                const clockOutBtn = row.querySelector('.btn-clock-out');
                
                // If no clock in button exists but should exist (status is sick/permission but no clock_in)
                if (!clockInBtn && !clockOutBtn && (data.data.status === 'sick' || data.data.status === 'permission')) {
                    const actionDiv = row.querySelector('.d-flex.gap-1, .d-flex.gap-2');
                    if (actionDiv) {
                        const newClockInBtn = document.createElement('button');
                        newClockInBtn.type = 'button';
                        newClockInBtn.className = 'btn btn-success btn-sm btn-clock-in';
                        newClockInBtn.setAttribute('data-student-id', currentStudentId);
                        newClockInBtn.innerHTML = '<i class="fas fa-sign-in-alt"></i> Clock In';
                        
                        // Insert before note button
                        const noteBtn = actionDiv.querySelector('.btn-note');
                        if (noteBtn) {
                            actionDiv.insertBefore(newClockInBtn, noteBtn);
                            
                            // Add event listener to new button
                            newClockInBtn.addEventListener('click', function() {
                                // Add clock in functionality (reuse existing logic)
                                const studentId = this.getAttribute('data-student-id');
                                const originalText = this.innerHTML;
                                
                                // Show loading
                                this.innerHTML = '<span class="spinner-border spinner-border-sm" role="status"></span> Loading...';
                                this.disabled = true;
                                
                                fetch(`/admin/attendance/${studentId}/clock-in`, {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                                    }
                                })
                                .then(response => response.json())
                                .then(data => {
                                    if (data.success) {
                                        // Update UI
                                        const row = document.querySelector(`[data-student-id="${studentId}"]`);
                                        row.querySelector('.clock-in-time').textContent = data.data.clock_in;
                                        row.querySelector('.status-badge').innerHTML = data.data.status_badge;
                                        
                                        // Change button to clock out
                                        this.className = 'btn btn-warning btn-sm btn-clock-out';
                                        this.innerHTML = '<i class="fas fa-sign-out-alt"></i> Clock Out';
                                        
                                        showToast('success', data.message);
                                    } else {
                                        showToast('error', data.message);
                                        this.innerHTML = originalText;
                                    }
                                })
                                .catch(error => {
                                    console.error('Error:', error);
                                    showToast('error', 'Terjadi kesalahan saat clock in');
                                    this.innerHTML = originalText;
                                })
                                .finally(() => {
                                    this.disabled = false;
                                });
                            });
                        }
                    }
                }
                
                // Hide modal
                const modal = bootstrap.Modal.getInstance(document.getElementById('noteModal'));
                modal.hide();
                
                showToast('success', data.message);
            } else {
                showToast('error', data.message || 'Terjadi kesalahan');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('error', 'Terjadi kesalahan saat menyimpan');
        })
        .finally(() => {
            submitBtn.disabled = false;
            spinner.classList.add('d-none');
        });
    });
    
    // Reset form when modal is closed
    document.getElementById('noteModal').addEventListener('hidden.bs.modal', function() {
        document.getElementById('noteForm').reset();
        currentStudentId = null;
    });
    
    // Toast notification function
    function showToast(type, message) {
        // Create toast element
        const toastHtml = `
            <div class="toast align-items-center text-white bg-${type === 'success' ? 'success' : 'danger'} border-0" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="d-flex">
                    <div class="toast-body">
                        <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'} me-2"></i>
                        ${message}
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
            </div>
        `;
        
        // Add to toast container (create if doesn't exist)
        let toastContainer = document.querySelector('.toast-container');
        if (!toastContainer) {
            toastContainer = document.createElement('div');
            toastContainer.className = 'toast-container position-fixed top-0 end-0 p-3';
            document.body.appendChild(toastContainer);
        }
        
        toastContainer.insertAdjacentHTML('beforeend', toastHtml);
        
        // Show toast
        const toastElement = toastContainer.lastElementChild;
        const toast = new bootstrap.Toast(toastElement);
        toast.show();
        
        // Remove from DOM after hidden
        toastElement.addEventListener('hidden.bs.toast', function() {
            this.remove();
        });
    }
});
</script>
@endpush
@endsection