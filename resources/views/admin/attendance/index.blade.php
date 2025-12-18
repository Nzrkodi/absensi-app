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

<!-- Early Clock In Warning -->
@if(!$isHoliday && !$allowEarlyClockIn && $isBeforeSchoolStart)
    <div class="alert alert-warning alert-dismissible fade show" role="alert">
        <div class="d-flex align-items-center">
            <i class="fas fa-exclamation-triangle fa-2x me-3"></i>
            <div>
                <h6 class="alert-heading mb-1">Absensi Belum Diizinkan</h6>
                <p class="mb-0">
                    Siswa belum bisa melakukan absensi (Clock In dan Input Note) karena pengaturan "Clock In Sebelum Jam Mulai Sekolah" dimatikan. 
                    Fitur absensi akan tersedia mulai jam <strong>{{ $settings['school_start_time'] }}</strong>.
                </p>
                <small class="text-muted">
                    Waktu sekarang: <span id="currentTime">{{ \Carbon\Carbon::now('Asia/Makassar')->format('H:i:s') }}</span> | 
                    Sisa waktu: <span id="timeRemaining"></span>
                </small>
            </div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2 py-3">
        <div>
            <h5 class="card-title mb-0">Absensi Siswa - {{ \Carbon\Carbon::parse($date)->format('d F Y') }}</h5>
            @if($isHoliday)
                <div class="d-flex align-items-center text-danger mt-1">
                    <i class="fas fa-calendar-times me-2"></i>
                    <small><strong>Hari Libur:</strong> {{ $holiday->name ?? 'Libur' }}</small>
                </div>
            @elseif(!$allowEarlyClockIn && $isBeforeSchoolStart)
                <div class="d-flex align-items-center text-warning mt-1">
                    <i class="fas fa-clock me-2"></i>
                    <small><strong>Clock In Belum Diizinkan:</strong> Tunggu hingga jam {{ $settings['school_start_time'] }}</small>
                </div>
            @endif
        </div>
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
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama atau NISN..." class="form-control">
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

    @if($isHoliday)
    <div class="card-body bg-warning bg-opacity-10 border-bottom">
        <div class="d-flex align-items-center">
            <i class="fas fa-calendar-times text-warning me-3 fs-4"></i>
            <div>
                <h6 class="mb-1 text-warning">Hari Libur: {{ $holiday->name ?? 'Libur' }}</h6>
                <small class="text-muted">Tombol absensi dinonaktifkan pada hari libur. Siswa tidak perlu melakukan absensi.</small>
            </div>
        </div>
    </div>
    @endif

    <div class="card-body p-0 p-md-3">
        <!-- Desktop Table -->
        <div class="table-responsive d-none d-md-block">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>No</th>
                        <th>NISN</th>
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
                        <td>{{ $student->nisn ?? '-' }}</td>
                        <td>{{ $student->name ?? '-' }}</td>
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
                                @if($isHoliday)
                                    <span class="badge bg-warning text-dark">
                                        <i class="fas fa-calendar-times me-1"></i> Hari Libur
                                    </span>
                                @else
                                    @if(!$attendance || (!$attendance->clock_in && !in_array($attendance->status ?? '', ['sick', 'permission'])))
                                        <button type="button" class="btn btn-success btn-sm btn-clock-in" data-student-id="{{ $student->id }}">
                                            <i class="fas fa-sign-in-alt"></i> Clock In
                                        </button>
                                    @elseif($attendance && $attendance->clock_in && !$attendance->clock_out)
                                        <button type="button" class="btn btn-warning btn-sm btn-clock-out" data-student-id="{{ $student->id }}">
                                            <i class="fas fa-sign-out-alt"></i> Clock Out
                                        </button>
                                    @endif
                                    
                                    @if(!$attendance || (!$attendance->clock_out && !in_array($attendance->status ?? '', ['sick', 'permission'])))
                                        <button type="button" class="btn btn-info btn-sm btn-note" data-student-id="{{ $student->id }}" data-bs-toggle="modal" data-bs-target="#noteModal">
                                            <i class="fas fa-sticky-note"></i> Note
                                        </button>
                                    @else
                                        <button type="button" class="btn btn-secondary btn-sm" disabled title="{{ $attendance && in_array($attendance->status ?? '', ['sick', 'permission']) ? 'Note sudah diisi' : 'Absensi sudah lengkap' }}">
                                            <i class="fas fa-sticky-note"></i> Note
                                        </button>
                                    @endif
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
                        <h6 class="mb-1">{{ $student->name ?? '-' }}</h6>
                        <small class="text-muted">{{ $student->nisn ?? '-' }} - {{ $student->class->name ?? '-' }}</small>
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
                    @if($isHoliday)
                        <span class="badge bg-warning text-dark">
                            <i class="fas fa-calendar-times me-1"></i> Hari Libur
                        </span>
                    @else
                        @if(!$attendance || (!$attendance->clock_in && !in_array($attendance->status ?? '', ['sick', 'permission'])))
                            <button type="button" class="btn btn-success btn-sm btn-clock-in" data-student-id="{{ $student->id }}">
                                <i class="fas fa-sign-in-alt"></i> Clock In
                            </button>
                        @elseif($attendance && $attendance->clock_in && !$attendance->clock_out)
                            <button type="button" class="btn btn-warning btn-sm btn-clock-out" data-student-id="{{ $student->id }}">
                                <i class="fas fa-sign-out-alt"></i> Clock Out
                            </button>
                        @endif
                        
                        @if(!$attendance || (!$attendance->clock_out && !in_array($attendance->status ?? '', ['sick', 'permission'])))
                            <button type="button" class="btn btn-info btn-sm btn-note" data-student-id="{{ $student->id }}" data-bs-toggle="modal" data-bs-target="#noteModal">
                                <i class="fas fa-sticky-note"></i> Note
                            </button>
                        @else
                            <button type="button" class="btn btn-secondary btn-sm" disabled title="{{ $attendance && in_array($attendance->status ?? '', ['sick', 'permission']) ? 'Note sudah diisi' : 'Absensi sudah lengkap' }}">
                                <i class="fas fa-sticky-note"></i> Note
                            </button>
                        @endif
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
    
    // Early clock in settings
    const allowEarlyClockIn = {{ $allowEarlyClockIn ? 'true' : 'false' }};
    const schoolStartTime = '{{ $settings["school_start_time"] }}';
    const isHoliday = {{ $isHoliday ? 'true' : 'false' }};
    
    // Update current time and check early clock in status
    function updateTimeAndStatus() {
        const now = new Date();
        const currentTimeStr = now.toLocaleTimeString('id-ID', { 
            timeZone: 'Asia/Makassar',
            hour12: false 
        });
        
        // Update current time display
        const currentTimeElement = document.getElementById('currentTime');
        if (currentTimeElement) {
            currentTimeElement.textContent = currentTimeStr;
        }
        
        // Check if we can enable clock in buttons
        if (!isHoliday && !allowEarlyClockIn) {
            const [currentHour, currentMinute] = currentTimeStr.split(':').map(Number);
            const [schoolHour, schoolMinute] = schoolStartTime.split(':').map(Number);
            
            const currentMinutes = currentHour * 60 + currentMinute;
            const schoolMinutes = schoolHour * 60 + schoolMinute;
            
            const timeRemainingElement = document.getElementById('timeRemaining');
            const clockInButtons = document.querySelectorAll('.btn-clock-in');
            const noteButtons = document.querySelectorAll('.btn-note');
            
            if (currentMinutes < schoolMinutes) {
                // Still before school start time
                const remainingMinutes = schoolMinutes - currentMinutes;
                const remainingHours = Math.floor(remainingMinutes / 60);
                const remainingMins = remainingMinutes % 60;
                
                if (timeRemainingElement) {
                    timeRemainingElement.textContent = `${remainingHours}j ${remainingMins}m`;
                }
                
                // Disable clock in buttons
                clockInButtons.forEach(button => {
                    button.disabled = true;
                    button.title = `Clock in akan tersedia jam ${schoolStartTime}`;
                    button.classList.add('opacity-50');
                });
                
                // Disable note buttons
                noteButtons.forEach(button => {
                    button.disabled = true;
                    button.title = `Input note akan tersedia jam ${schoolStartTime}`;
                    button.classList.add('opacity-50');
                    button.removeAttribute('data-bs-toggle');
                    button.removeAttribute('data-bs-target');
                });
            } else {
                // School time has started, enable buttons
                if (timeRemainingElement) {
                    timeRemainingElement.textContent = 'Sudah bisa clock in';
                    timeRemainingElement.parentElement.parentElement.style.display = 'none';
                }
                
                clockInButtons.forEach(button => {
                    button.disabled = false;
                    button.title = '';
                    button.classList.remove('opacity-50');
                });
                
                // Enable note buttons
                noteButtons.forEach(button => {
                    button.disabled = false;
                    button.title = '';
                    button.classList.remove('opacity-50');
                    button.setAttribute('data-bs-toggle', 'modal');
                    button.setAttribute('data-bs-target', '#noteModal');
                });
            }
        }
    }
    
    // Update time every second
    updateTimeAndStatus();
    setInterval(updateTimeAndStatus, 1000);
    
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
                    // Check if it's early clock in warning
                    if (data.early_clockin_disabled) {
                        showToast('warning', data.message);
                    } else {
                        showToast('error', data.message);
                    }
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
                
                // Hide clock in/out buttons if status is sick or permission
                if (data.data.status === 'sick' || data.data.status === 'permission') {
                    const clockInBtn = row.querySelector('.btn-clock-in');
                    const clockOutBtn = row.querySelector('.btn-clock-out');
                    
                    if (clockInBtn) {
                        clockInBtn.remove();
                    }
                    if (clockOutBtn) {
                        clockOutBtn.remove();
                    }
                }
                
                // Hide note button after successful note input
                const noteButton = row.querySelector('.btn-note');
                if (noteButton) {
                    noteButton.className = 'btn btn-secondary btn-sm';
                    noteButton.disabled = true;
                    noteButton.title = 'Note sudah diisi';
                    noteButton.removeAttribute('data-bs-toggle');
                    noteButton.removeAttribute('data-bs-target');
                    noteButton.innerHTML = '<i class="fas fa-sticky-note"></i> Note';
                }
                
                // Hide modal
                const modal = bootstrap.Modal.getInstance(document.getElementById('noteModal'));
                modal.hide();
                
                showToast('success', data.message);
            } else {
                // Check if it's early note warning
                if (data.early_note_disabled) {
                    showToast('warning', data.message);
                } else {
                    showToast('error', data.message || 'Terjadi kesalahan');
                }
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