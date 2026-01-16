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
                    <a href="{{ route('admin.attendance.index', ['date' => $date]) }}" class="btn btn-outline-secondary w-100">
                        <i class="fas fa-undo me-1"></i> Reset Filter
                    </a>
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
                                    @php
                                        $isAbsentOrBolos = $attendance && in_array($attendance->status, ['absent', 'bolos']);
                                        $isSickOrPermission = $attendance && in_array($attendance->status, ['sick', 'permission']);
                                    @endphp
                                    
                                    @php
                                        $isDisabledStatus = $attendance && in_array($attendance->status, ['sick', 'permission', 'absent', 'bolos']);
                                    @endphp
                                    
                                    @if(!$attendance || (!$attendance->clock_in && !$isDisabledStatus))
                                        <button type="button" class="btn btn-success btn-sm btn-clock-in" data-student-id="{{ $student->id }}" data-bs-toggle="modal" data-bs-target="#manualClockInModal">
                                            <i class="fas fa-sign-in-alt"></i> Clock In
                                        </button>
                                    @elseif($attendance && $attendance->clock_in && !$attendance->clock_out && !$isDisabledStatus)
                                        <button type="button" class="btn btn-warning btn-sm btn-clock-out" data-student-id="{{ $student->id }}">
                                            <i class="fas fa-sign-out-alt"></i> Clock Out
                                        </button>
                                    @endif
                                    
                                    @if(!$attendance || (!$attendance->clock_out && !$isDisabledStatus))
                                        <button type="button" class="btn btn-info btn-sm btn-note" data-student-id="{{ $student->id }}" data-bs-toggle="modal" data-bs-target="#noteModal">
                                            <i class="fas fa-sticky-note"></i> Note
                                        </button>
                                    @else
                                        @php
                                            $disabledReason = '';
                                            if ($attendance && in_array($attendance->status, ['sick', 'permission'])) {
                                                $disabledReason = 'Note sudah diisi';
                                            } elseif ($attendance && in_array($attendance->status, ['absent', 'bolos'])) {
                                                $disabledReason = 'Tidak dapat mengubah status ' . $attendance->status;
                                            } else {
                                                $disabledReason = 'Absensi sudah lengkap';
                                            }
                                        @endphp
                                        <button type="button" class="btn btn-secondary btn-sm" disabled title="{{ $disabledReason }}">
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
                        @php
                            $isAbsentOrBolos = $attendance && in_array($attendance->status, ['absent', 'bolos']);
                            $isSickOrPermission = $attendance && in_array($attendance->status, ['sick', 'permission']);
                        @endphp
                        
                        @php
                            $isDisabledStatus = $attendance && in_array($attendance->status, ['sick', 'permission', 'absent', 'bolos']);
                        @endphp
                        
                        @if(!$attendance || (!$attendance->clock_in && !$isDisabledStatus))
                            <button type="button" class="btn btn-success btn-sm btn-clock-in" data-student-id="{{ $student->id }}" data-bs-toggle="modal" data-bs-target="#manualClockInModal">
                                <i class="fas fa-sign-in-alt"></i> Clock In
                            </button>
                        @elseif($attendance && $attendance->clock_in && !$attendance->clock_out && !$isDisabledStatus)
                            <button type="button" class="btn btn-warning btn-sm btn-clock-out" data-student-id="{{ $student->id }}">
                                <i class="fas fa-sign-out-alt"></i> Clock Out
                            </button>
                        @endif
                        
                        @if(!$attendance || (!$attendance->clock_out && !$isDisabledStatus))
                            <button type="button" class="btn btn-info btn-sm btn-note" data-student-id="{{ $student->id }}" data-bs-toggle="modal" data-bs-target="#noteModal">
                                <i class="fas fa-sticky-note"></i> Note
                            </button>
                        @else
                            @php
                                $disabledReason = '';
                                if ($attendance && in_array($attendance->status, ['sick', 'permission'])) {
                                    $disabledReason = 'Note sudah diisi';
                                } elseif ($attendance && in_array($attendance->status, ['absent', 'bolos'])) {
                                    $disabledReason = 'Tidak dapat mengubah status ' . $attendance->status;
                                } else {
                                    $disabledReason = 'Absensi sudah lengkap';
                                }
                            @endphp
                            <button type="button" class="btn btn-secondary btn-sm" disabled title="{{ $disabledReason }}">
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

<!-- Manual Clock In Modal -->
<div class="modal fade" id="manualClockInModal" tabindex="-1" aria-labelledby="manualClockInModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="manualClockInModalLabel">Clock In Manual</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="manualClockInForm">
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Fitur Clock In Manual:</strong> Gunakan untuk mencatat waktu kedatangan siswa yang sebenarnya, terutama ketika Anda terlambat datang ke sekolah.
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Pilih Mode Clock In</label>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="clock_mode" id="currentTime" value="current" checked>
                            <label class="form-check-label" for="currentTime">
                                <strong>Waktu Sekarang</strong> - <span id="currentTimeDisplay">{{ \Carbon\Carbon::now('Asia/Makassar')->format('H:i') }}</span>
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="clock_mode" id="manualTime" value="manual">
                            <label class="form-check-label" for="manualTime">
                                <strong>Waktu Manual</strong> - Input waktu kedatangan yang sebenarnya
                            </label>
                        </div>
                    </div>
                    
                    <div class="mb-3" id="manualTimeInput" style="display: none;">
                        <label for="manual_time" class="form-label">Waktu Kedatangan <span class="text-danger">*</span></label>
                        <input type="time" class="form-control" id="manual_time" name="manual_time">
                        <div class="form-text">
                            <i class="fas fa-lightbulb text-warning me-1"></i>
                            Masukkan waktu kedatangan siswa yang sebenarnya. Status akan otomatis ditentukan berdasarkan waktu ini.
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="manual_notes" class="form-label">Keterangan (Opsional)</label>
                        <textarea class="form-control" id="manual_notes" name="notes" rows="2" placeholder="Contoh: Siswa datang terlambat karena macet..."></textarea>
                    </div>
                    
                    <div class="alert alert-warning" id="lateWarning" style="display: none;">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Perhatian:</strong> Waktu yang dimasukkan akan menghasilkan status <strong>TERLAMBAT</strong> karena melewati batas toleransi keterlambatan ({{ $settings['school_start_time'] }} + {{ $settings['late_tolerance_minutes'] }} menit).
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success">
                        <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                        <i class="fas fa-sign-in-alt me-1"></i> Clock In
                    </button>
                </div>
            </form>
        </div>
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
                    
                    <!-- Multi-day options (hidden by default) -->
                    <div id="multiDayOptions" class="d-none mb-3">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Opsi Durasi Izin:</strong> Pilih apakah izin/sakit hanya untuk hari ini atau untuk beberapa hari.
                        </div>
                        
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="duration_type" id="singleDay" value="single" checked>
                                <label class="form-check-label" for="singleDay">
                                    <strong>Hari ini saja</strong>
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="duration_type" id="multiDay" value="multi">
                                <label class="form-check-label" for="multiDay">
                                    <strong>Beberapa hari</strong> (termasuk hari ini)
                                </label>
                            </div>
                        </div>
                        
                        <!-- Date range for multi-day (hidden by default) -->
                        <div id="dateRangeSection" class="d-none">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="noteStartDate" class="form-label">Tanggal Mulai <span class="text-danger">*</span></label>
                                        <input type="date" class="form-control" id="noteStartDate" name="start_date">
                                        <small class="text-muted">Bisa diubah sesuai kebutuhan</small>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="noteEndDate" class="form-label">Tanggal Akhir <span class="text-danger">*</span></label>
                                        <input type="date" class="form-control" id="noteEndDate" name="end_date">
                                    </div>
                                </div>
                            </div>
                            
                            <div id="noteDateRangeInfo" class="alert alert-secondary d-none">
                                <i class="fas fa-calendar me-2"></i>
                                <span id="noteDateRangeText"></span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="notes" class="form-label">Keterangan <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="notes" name="notes" rows="3" placeholder="Masukkan keterangan..." required></textarea>
                        <div class="form-text">Contoh: Demam tinggi, Keperluan keluarga, dll.</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">
                        <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                        <span id="submitButtonText">Simpan</span>
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
    let currentClockInStudentId = null;
    
    // Early clock in settings
    const allowEarlyClockIn = {{ $allowEarlyClockIn ? 'true' : 'false' }};
    const schoolStartTime = '{{ $settings["school_start_time"] }}';
    const lateToleranceMinutes = {{ $settings['late_tolerance_minutes'] }};
    const isHoliday = {{ $isHoliday ? 'true' : 'false' }};
    
    // Update current time and check early clock in status
    function updateTimeAndStatus() {
        const now = new Date();
        const currentTimeStr = now.toLocaleTimeString('id-ID', { 
            timeZone: 'Asia/Makassar',
            hour12: false 
        });
        
        // Update current time display in modal
        const currentTimeDisplay = document.getElementById('currentTimeDisplay');
        if (currentTimeDisplay) {
            currentTimeDisplay.textContent = currentTimeStr.substring(0, 5); // HH:MM format
        }
        
        // Update current time display in warning
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
                    button.removeAttribute('data-bs-toggle');
                    button.removeAttribute('data-bs-target');
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
                    button.setAttribute('data-bs-toggle', 'modal');
                    button.setAttribute('data-bs-target', '#manualClockInModal');
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
    
    // Auto-submit form when class filter changes
    const classFilter = document.querySelector('select[name="class_id"]');
    if (classFilter) {
        classFilter.addEventListener('change', function() {
            // Get the filter form and submit it
            const filterForm = document.getElementById('filterForm');
            if (filterForm) {
                filterForm.submit();
            }
        });
    }
    
    // Auto-submit form when search input changes (with debounce)
    const searchInput = document.querySelector('input[name="search"]');
    if (searchInput) {
        let searchTimeout;
        searchInput.addEventListener('input', function() {
            // Clear previous timeout
            clearTimeout(searchTimeout);
            
            // Set new timeout for 500ms delay
            searchTimeout = setTimeout(() => {
                const filterForm = document.getElementById('filterForm');
                if (filterForm) {
                    filterForm.submit();
                }
            }, 500);
        });
    }
    
    // Manual Clock In Modal handlers
    document.querySelectorAll('.btn-clock-in').forEach(button => {
        button.addEventListener('click', function() {
            currentClockInStudentId = this.getAttribute('data-student-id');
            
            // Reset form
            document.getElementById('manualClockInForm').reset();
            document.getElementById('currentTime').checked = true;
            document.getElementById('manualTimeInput').style.display = 'none';
            document.getElementById('lateWarning').style.display = 'none';
        });
    });
    
    // Clock mode radio button handlers
    document.querySelectorAll('input[name="clock_mode"]').forEach(radio => {
        radio.addEventListener('change', function() {
            const manualTimeInput = document.getElementById('manualTimeInput');
            const manualTimeField = document.getElementById('manual_time');
            
            if (this.value === 'manual') {
                manualTimeInput.style.display = 'block';
                manualTimeField.required = true;
                checkLateStatus();
            } else {
                manualTimeInput.style.display = 'none';
                manualTimeField.required = false;
                document.getElementById('lateWarning').style.display = 'none';
            }
        });
    });
    
    // Manual time input change handler
    document.getElementById('manual_time').addEventListener('change', checkLateStatus);
    
    function checkLateStatus() {
        const manualTimeValue = document.getElementById('manual_time').value;
        const lateWarning = document.getElementById('lateWarning');
        
        if (manualTimeValue) {
            // Calculate late threshold
            const [schoolHour, schoolMinute] = schoolStartTime.split(':').map(Number);
            const schoolMinutes = schoolHour * 60 + schoolMinute;
            const lateThresholdMinutes = schoolMinutes + lateToleranceMinutes;
            
            const [inputHour, inputMinute] = manualTimeValue.split(':').map(Number);
            const inputMinutes = inputHour * 60 + inputMinute;
            
            if (inputMinutes > lateThresholdMinutes) {
                lateWarning.style.display = 'block';
            } else {
                lateWarning.style.display = 'none';
            }
        } else {
            lateWarning.style.display = 'none';
        }
    }
    
    // Manual Clock In form submission
    document.getElementById('manualClockInForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        if (!currentClockInStudentId) return;
        
        const formData = new FormData(this);
        const submitBtn = this.querySelector('button[type="submit"]');
        const spinner = submitBtn.querySelector('.spinner-border');
        
        // Show loading
        submitBtn.disabled = true;
        spinner.classList.remove('d-none');
        
        fetch(`/admin/attendance/${currentClockInStudentId}/clock-in`, {
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
                const row = document.querySelector(`[data-student-id="${currentClockInStudentId}"]`);
                row.querySelector('.clock-in-time').textContent = data.data.clock_in;
                row.querySelector('.status-badge').innerHTML = data.data.status_badge;
                
                // Change button to clock out
                const clockInBtn = row.querySelector('.btn-clock-in');
                clockInBtn.className = 'btn btn-warning btn-sm btn-clock-out';
                clockInBtn.innerHTML = '<i class="fas fa-sign-out-alt"></i> Clock Out';
                clockInBtn.setAttribute('data-student-id', currentClockInStudentId);
                clockInBtn.removeAttribute('data-bs-toggle');
                clockInBtn.removeAttribute('data-bs-target');
                
                // Add new event listener for clock out
                addClockOutListener(clockInBtn);
                
                // Hide modal
                const modal = bootstrap.Modal.getInstance(document.getElementById('manualClockInModal'));
                modal.hide();
                
                showToast('success', data.message);
            } else {
                // Check if it's early clock in warning
                if (data.early_clockin_disabled) {
                    showToast('warning', data.message);
                } else {
                    showToast('error', data.message);
                }
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('error', 'Terjadi kesalahan saat clock in');
        })
        .finally(() => {
            submitBtn.disabled = false;
            spinner.classList.add('d-none');
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
            
            // Set default dates using server date
            const today = '{{ $date }}'; // Use server date from controller
            document.getElementById('noteStartDate').value = today;
            document.getElementById('noteEndDate').value = today;
            document.getElementById('noteEndDate').setAttribute('min', today);
            document.getElementById('noteStartDate').setAttribute('min', today);
            
            // Reset form
            document.getElementById('status').value = '';
            document.getElementById('multiDayOptions').classList.add('d-none');
            document.getElementById('dateRangeSection').classList.add('d-none');
            document.getElementById('singleDay').checked = true;
            document.getElementById('submitButtonText').textContent = 'Simpan';
        });
    });
    
    // Status change handler
    document.getElementById('status').addEventListener('change', function() {
        const multiDayOptions = document.getElementById('multiDayOptions');
        
        if (this.value === 'sick' || this.value === 'permission') {
            multiDayOptions.classList.remove('d-none');
        } else {
            multiDayOptions.classList.add('d-none');
            document.getElementById('dateRangeSection').classList.add('d-none');
            document.getElementById('singleDay').checked = true;
        }
        
        updateSubmitButtonText();
    });
    
    // Duration type change handlers
    document.querySelectorAll('input[name="duration_type"]').forEach(radio => {
        radio.addEventListener('change', function() {
            const dateRangeSection = document.getElementById('dateRangeSection');
            
            if (this.value === 'multi') {
                dateRangeSection.classList.remove('d-none');
                updateNoteDateRangeInfo();
            } else {
                dateRangeSection.classList.add('d-none');
                document.getElementById('noteDateRangeInfo').classList.add('d-none');
            }
            
            updateSubmitButtonText();
        });
    });
    
    // Date range change handler
    document.getElementById('noteStartDate').addEventListener('change', updateNoteDateRangeInfo);
    document.getElementById('noteEndDate').addEventListener('change', updateNoteDateRangeInfo);
    
    function updateNoteDateRangeInfo() {
        const startDate = document.getElementById('noteStartDate').value;
        const endDate = document.getElementById('noteEndDate').value;
        const infoDiv = document.getElementById('noteDateRangeInfo');
        const infoText = document.getElementById('noteDateRangeText');
        
        if (startDate && endDate) {
            const start = new Date(startDate);
            const end = new Date(endDate);
            const diffTime = Math.abs(end - start);
            const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;
            
            if (start <= end) {
                infoText.textContent = `Periode: ${diffDays} hari (${formatDate(start)} - ${formatDate(end)})`;
                infoDiv.classList.remove('d-none', 'alert-danger');
                infoDiv.classList.add('alert-secondary');
            } else {
                infoText.textContent = 'Tanggal akhir harus setelah atau sama dengan tanggal mulai';
                infoDiv.classList.remove('d-none', 'alert-secondary');
                infoDiv.classList.add('alert-danger');
            }
        } else {
            infoDiv.classList.add('d-none');
        }
    }
    
    function updateSubmitButtonText() {
        const status = document.getElementById('status').value;
        const durationType = document.querySelector('input[name="duration_type"]:checked')?.value;
        const submitBtn = document.getElementById('submitButtonText');
        
        if ((status === 'sick' || status === 'permission') && durationType === 'multi') {
            submitBtn.textContent = 'Berikan Izin Multi-Hari';
        } else {
            submitBtn.textContent = 'Simpan';
        }
    }
    
    function formatDate(date) {
        const options = { day: 'numeric', month: 'short', year: 'numeric' };
        return date.toLocaleDateString('id-ID', options);
    }
    
    // Note form submission
    document.getElementById('noteForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        if (!currentStudentId) return;
        
        const formData = new FormData(this);
        const durationType = document.querySelector('input[name="duration_type"]:checked')?.value;
        const status = document.getElementById('status').value;
        
        // Determine which endpoint to use
        let endpoint;
        
        if ((status === 'sick' || status === 'permission') && durationType === 'multi') {
            // Use bulk permission endpoint
            endpoint = `/admin/attendance/${currentStudentId}/bulk-permission`;
            
            // Prepare data for bulk permission
            formData.set('permission_type', status);
            
            // Validate end date for multi-day
            const endDate = document.getElementById('noteEndDate').value;
            if (!endDate) {
                showToast('error', 'Harap pilih tanggal akhir untuk izin multi-hari');
                return;
            }
        } else {
            // Use regular note endpoint
            endpoint = `/admin/attendance/${currentStudentId}/note`;
        }
        
        const submitBtn = this.querySelector('button[type="submit"]');
        const spinner = submitBtn.querySelector('.spinner-border');
        
        // Show loading
        submitBtn.disabled = true;
        spinner.classList.remove('d-none');
        
        fetch(endpoint, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Hide modal
                const modal = bootstrap.Modal.getInstance(document.getElementById('noteModal'));
                modal.hide();
                
                showToast('success', data.message);
                
                // Reload page after short delay to show updated data
                setTimeout(() => {
                    window.location.reload();
                }, 1500);
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
    
    // Reset forms when modals are closed
    document.getElementById('manualClockInModal').addEventListener('hidden.bs.modal', function() {
        document.getElementById('manualClockInForm').reset();
        currentClockInStudentId = null;
    });
    
    document.getElementById('noteModal').addEventListener('hidden.bs.modal', function() {
        document.getElementById('noteForm').reset();
        document.getElementById('multiDayOptions').classList.add('d-none');
        document.getElementById('dateRangeSection').classList.add('d-none');
        document.getElementById('noteDateRangeInfo').classList.add('d-none');
        currentStudentId = null;
    });
    
    // Toast notification function
    function showToast(type, message) {
        // Create toast element
        const toastHtml = `
            <div class="toast align-items-center text-white bg-${type === 'success' ? 'success' : type === 'warning' ? 'warning' : 'danger'} border-0" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="d-flex">
                    <div class="toast-body">
                        <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'warning' ? 'exclamation-triangle' : 'exclamation-circle'} me-2"></i>
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