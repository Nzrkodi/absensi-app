@extends('layouts.admin')

@section('title', 'Absensi Siswa')
@section('header', 'Absensi Siswa')

@section('content')
<style>
/* Custom styles for improved UI */
.attendance-card {
    transition: all 0.3s ease;
    border: none !important;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.attendance-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 20px rgba(0,0,0,0.15);
}

.btn-clock-in {
    background: linear-gradient(45deg, #28a745, #20c997);
    border: none;
    transition: all 0.3s ease;
}

.btn-clock-in:hover {
    background: linear-gradient(45deg, #218838, #1ea080);
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(40, 167, 69, 0.3);
}

.btn-clock-out {
    background: linear-gradient(45deg, #ffc107, #fd7e14);
    border: none;
    color: #212529 !important;
    transition: all 0.3s ease;
}

.btn-clock-out:hover {
    background: linear-gradient(45deg, #e0a800, #e8590c);
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(255, 193, 7, 0.3);
    color: #212529 !important;
}

.btn-note {
    background: linear-gradient(45deg, #17a2b8, #6f42c1);
    border: none;
    transition: all 0.3s ease;
}

.btn-note:hover {
    background: linear-gradient(45deg, #138496, #5a32a3);
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(23, 162, 184, 0.3);
}

.status-badge .badge {
    font-size: 0.75rem;
    padding: 0.5em 0.75em;
    border-radius: 0.5rem;
}

.table-hover tbody tr:hover {
    background-color: rgba(0,123,255,0.05);
    transform: scale(1.01);
    transition: all 0.2s ease;
}

.modal-content {
    border: none;
    border-radius: 1rem;
    box-shadow: 0 10px 40px rgba(0,0,0,0.2);
}

.modal-header {
    border-bottom: 1px solid rgba(0,0,0,0.1);
    border-radius: 1rem 1rem 0 0;
}

.form-check-input:checked {
    background-color: #0d6efd;
    border-color: #0d6efd;
}

.alert {
    border: none;
    border-radius: 0.75rem;
}

.card-header {
    border-radius: 0.5rem 0.5rem 0 0 !important;
}

@media (max-width: 768px) {
    .attendance-card {
        margin-bottom: 0.5rem;
    }
    
    .btn-sm {
        font-size: 0.75rem;
        padding: 0.25rem 0.5rem;
    }
}
</style>

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

<div class="card attendance-card border-0 shadow-sm">
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
                        <th>Detail</th>
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
                            @if($attendance && ($attendance->clock_in_photo || $attendance->clock_in_latitude))
                                <button type="button" class="btn btn-outline-info btn-sm" 
                                        onclick="showAttendanceDetail({{ $attendance->id }})"
                                        title="Lihat foto dan lokasi">
                                    <i class="fas fa-eye"></i> Detail
                                </button>
                            @else
                                <span class="text-muted small">-</span>
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
                        <td colspan="9" class="text-center text-muted py-4">Belum ada data siswa</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Mobile Cards -->
        <div class="d-md-none">
            <!-- Mobile Status Bar -->
            <div class="card border-0 shadow-sm mb-3 bg-light">
                <div class="card-body p-3">
                    <div class="row text-center">
                        <div class="col-6">
                            <div id="locationStatus" class="small">
                                <i class="fas fa-spinner fa-spin text-muted"></i> Mengecek lokasi...
                            </div>
                        </div>
                        <div class="col-6">
                            <div id="photoStatus" class="small">
                                <i class="fas fa-camera text-muted"></i> Belum ada foto
                            </div>
                        </div>
                    </div>
                    <div class="row mt-2">
                        <div class="col-6">
                            <button class="btn btn-info btn-sm w-100" onclick="attendanceMobile.initializeGeolocation()">
                                <i class="fas fa-map-marker-alt me-1"></i> Cek Lokasi
                            </button>
                        </div>
                        <div class="col-6">
                            <button class="btn btn-primary btn-sm w-100" onclick="attendanceMobile.capturePhoto()">
                                <i class="fas fa-camera me-1"></i> Ambil Foto
                            </button>
                        </div>
                    </div>
                </div>
            </div>
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
                    @if($attendance && ($attendance->clock_in_photo || $attendance->clock_in_latitude))
                        <button type="button" class="btn btn-outline-info btn-sm" 
                                onclick="showAttendanceDetail({{ $attendance->id }})"
                                title="Lihat foto dan lokasi">
                            <i class="fas fa-eye"></i> Detail
                        </button>
                    @endif
                    
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
                            <button type="button" class="btn btn-warning btn-sm btn-clock-out" data-student-id="{{ $student->id }}" data-bs-toggle="modal" data-bs-target="#manualClockOutModal">
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

<!-- Manual Clock Out Modal -->
<div class="modal fade" id="manualClockOutModal" tabindex="-1" aria-labelledby="manualClockOutModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="manualClockOutModalLabel">Clock Out Manual</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="manualClockOutForm">
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Fitur Clock Out Manual:</strong> Gunakan untuk mencatat waktu kepulangan siswa yang sebenarnya, terutama ketika ada keperluan khusus atau penyesuaian waktu.
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Pilih Mode Clock Out</label>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="clock_mode" id="currentTimeOut" value="current" checked>
                            <label class="form-check-label" for="currentTimeOut">
                                <strong>Waktu Sekarang</strong> - <span id="currentTimeOutDisplay">{{ \Carbon\Carbon::now('Asia/Makassar')->format('H:i') }}</span>
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="clock_mode" id="manualTimeOut" value="manual">
                            <label class="form-check-label" for="manualTimeOut">
                                <strong>Waktu Manual</strong> - Input waktu kepulangan yang sebenarnya
                            </label>
                        </div>
                    </div>
                    
                    <div class="mb-3" id="manualTimeOutInput" style="display: none;">
                        <label for="manual_time_out" class="form-label">Waktu Kepulangan <span class="text-danger">*</span></label>
                        <input type="time" class="form-control" id="manual_time_out" name="manual_time">
                        <div class="form-text">
                            <i class="fas fa-lightbulb text-warning me-1"></i>
                            Masukkan waktu kepulangan siswa yang sebenarnya. Waktu harus setelah waktu clock in.
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="manual_notes_out" class="form-label">Keterangan (Opsional)</label>
                        <textarea class="form-control" id="manual_notes_out" name="notes" rows="2" placeholder="Contoh: Pulang lebih awal karena sakit..."></textarea>
                    </div>
                    
                    <div class="alert alert-warning" id="clockOutWarning" style="display: none;">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Perhatian:</strong> <span id="clockOutWarningText"></span>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning">
                        <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                        <i class="fas fa-sign-out-alt me-1"></i> Clock Out
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
<script src="{{ asset('js/attendance-mobile.js') }}"></script>
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
        
        // Update current time display in clock out modal
        const currentTimeOutDisplay = document.getElementById('currentTimeOutDisplay');
        if (currentTimeOutDisplay) {
            currentTimeOutDisplay.textContent = currentTimeStr.substring(0, 5); // HH:MM format
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
    
    // Manual Clock Out Modal handlers
    let currentClockOutStudentId = null;
    
    // Use event delegation for dynamically created clock-out buttons
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('btn-clock-out') || e.target.closest('.btn-clock-out')) {
            const button = e.target.classList.contains('btn-clock-out') ? e.target : e.target.closest('.btn-clock-out');
            currentClockOutStudentId = button.getAttribute('data-student-id');
            
            // Check if button has modal attributes (new modal system)
            if (button.hasAttribute('data-bs-toggle') && button.getAttribute('data-bs-target') === '#manualClockOutModal') {
                // Reset form for modal
                document.getElementById('manualClockOutForm').reset();
                document.getElementById('currentTimeOut').checked = true;
                document.getElementById('manualTimeOutInput').style.display = 'none';
                document.getElementById('clockOutWarning').style.display = 'none';
                
                // Get student's clock in time for validation
                const row = document.querySelector(`[data-student-id="${currentClockOutStudentId}"]`);
                const clockInTime = row.querySelector('.clock-in-time').textContent.trim();
                
                // Store clock in time for validation
                document.getElementById('manualClockOutForm').setAttribute('data-clock-in-time', clockInTime);
            } else {
                // Legacy direct clock-out (fallback for existing buttons)
                e.preventDefault();
                performDirectClockOut(currentClockOutStudentId, button);
            }
        }
    });
    
    // Function for direct clock-out (without modal)
    function performDirectClockOut(studentId, button) {
        const originalText = button.innerHTML;
        
        // Show loading
        button.innerHTML = '<span class="spinner-border spinner-border-sm" role="status"></span> Loading...';
        button.disabled = true;
        
        const formData = new FormData();
        formData.append('clock_mode', 'current');
        
        fetch(`/admin/attendance/${studentId}/clock-out`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            },
            body: formData,
            signal: AbortSignal.timeout(30000) // 30 second timeout
        })
        .then(response => {
            console.log('Clock-out response status:', response.status);
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            console.log('Clock-out response data:', data);
            if (data.success) {
                // Update UI
                const row = document.querySelector(`[data-student-id="${studentId}"]`);
                row.querySelector('.clock-out-time').textContent = data.data.clock_out;
                
                // Remove clock out button
                button.remove();
                
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
                showToast('error', data.message || 'Clock out gagal');
                button.innerHTML = originalText;
                button.disabled = false;
            }
        })
        .catch(error => {
            console.error('Clock-out error:', error);
            showToast('error', 'Terjadi kesalahan saat clock out: ' + error.message);
            button.innerHTML = originalText;
            button.disabled = false;
        });
    }
    
    // Clock Out mode radio button handlers
    document.querySelectorAll('#manualClockOutModal input[name="clock_mode"]').forEach(radio => {
        radio.addEventListener('change', function() {
            const manualTimeOutInput = document.getElementById('manualTimeOutInput');
            const manualTimeOutField = document.getElementById('manual_time_out');
            
            if (this.value === 'manual') {
                manualTimeOutInput.style.display = 'block';
                manualTimeOutField.required = true;
                checkClockOutTime();
            } else {
                manualTimeOutInput.style.display = 'none';
                manualTimeOutField.required = false;
                document.getElementById('clockOutWarning').style.display = 'none';
            }
        });
    });
    
    // Manual clock out time input change handler
    document.getElementById('manual_time_out').addEventListener('change', checkClockOutTime);
    
    function checkClockOutTime() {
        const manualTimeOutValue = document.getElementById('manual_time_out').value;
        const clockOutWarning = document.getElementById('clockOutWarning');
        const clockOutWarningText = document.getElementById('clockOutWarningText');
        const clockInTime = document.getElementById('manualClockOutForm').getAttribute('data-clock-in-time');
        
        if (manualTimeOutValue && clockInTime && clockInTime !== '-') {
            const [clockInHour, clockInMinute] = clockInTime.split(':').map(Number);
            const [clockOutHour, clockOutMinute] = manualTimeOutValue.split(':').map(Number);
            
            const clockInMinutes = clockInHour * 60 + clockInMinute;
            const clockOutMinutes = clockOutHour * 60 + clockOutMinute;
            
            if (clockOutMinutes <= clockInMinutes) {
                clockOutWarningText.textContent = `Waktu clock out harus setelah waktu clock in (${clockInTime})`;
                clockOutWarning.style.display = 'block';
            } else {
                clockOutWarning.style.display = 'none';
            }
        } else {
            clockOutWarning.style.display = 'none';
        }
    }
    
    // Manual Clock Out form submission
    document.getElementById('manualClockOutForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        if (!currentClockOutStudentId) return;
        
        const formData = new FormData(this);
        const submitBtn = this.querySelector('button[type="submit"]');
        const spinner = submitBtn.querySelector('.spinner-border');
        
        // Show loading
        submitBtn.disabled = true;
        spinner.classList.remove('d-none');
        
        fetch(`/admin/attendance/${currentClockOutStudentId}/clock-out`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            },
            body: formData,
            signal: AbortSignal.timeout(30000) // 30 second timeout
        })
        .then(response => {
            console.log('Modal clock-out response status:', response.status);
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            console.log('Modal clock-out response data:', data);
            if (data.success) {
                // Update UI
                const row = document.querySelector(`[data-student-id="${currentClockOutStudentId}"]`);
                row.querySelector('.clock-out-time').textContent = data.data.clock_out;
                
                // Remove clock out button
                const clockOutBtn = row.querySelector('.btn-clock-out');
                if (clockOutBtn) {
                    clockOutBtn.remove();
                }
                
                // Disable note button
                const noteButton = row.querySelector('.btn-note');
                if (noteButton) {
                    noteButton.className = 'btn btn-secondary btn-sm';
                    noteButton.disabled = true;
                    noteButton.title = 'Absensi sudah lengkap';
                    noteButton.removeAttribute('data-bs-toggle');
                    noteButton.removeAttribute('data-bs-target');
                }
                
                // Hide modal
                const modal = bootstrap.Modal.getInstance(document.getElementById('manualClockOutModal'));
                modal.hide();
                
                showToast('success', data.message);
            } else {
                showToast('error', data.message || 'Clock out gagal');
            }
        })
        .catch(error => {
            console.error('Modal clock-out error:', error);
            showToast('error', 'Terjadi kesalahan saat clock out: ' + error.message);
        })
        .finally(() => {
            submitBtn.disabled = false;
            spinner.classList.add('d-none');
        });
    });
    
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
                
                // Change button to clock out (with modal)
                const clockInBtn = row.querySelector('.btn-clock-in');
                clockInBtn.className = 'btn btn-warning btn-sm btn-clock-out';
                clockInBtn.innerHTML = '<i class="fas fa-sign-out-alt"></i> Clock Out';
                clockInBtn.setAttribute('data-student-id', currentClockInStudentId);
                clockInBtn.setAttribute('data-bs-toggle', 'modal');
                clockInBtn.setAttribute('data-bs-target', '#manualClockOutModal');
                
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

    document.getElementById('manualClockOutModal').addEventListener('hidden.bs.modal', function() {
        document.getElementById('manualClockOutForm').reset();
        document.getElementById('manualTimeOutInput').style.display = 'none';
        document.getElementById('clockOutWarning').style.display = 'none';
        currentClockOutStudentId = null;
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

// Function to show attendance detail
function showAttendanceDetail(attendanceId) {
    const modal = new bootstrap.Modal(document.getElementById('attendanceDetailModal'));
    const content = document.getElementById('attendanceDetailContent');
    
    // Show loading
    content.innerHTML = `
        <div class="text-center">
            <div class="spinner-border" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-2">Memuat detail absensi...</p>
        </div>
    `;
    
    modal.show();
    
    // Fetch attendance detail
    fetch(`/admin/attendance/${attendanceId}/detail`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const attendance = data.attendance;
                const student = attendance.student;
                
                let photosHtml = '';
                let locationHtml = '';
                
                // Clock In Photo and Location
                if (attendance.clock_in_photo || attendance.clock_in_latitude) {
                    photosHtml += `
                        <div class="col-md-6">
                            <div class="card border-success">
                                <div class="card-header bg-success text-white">
                                    <h6 class="mb-0"><i class="fas fa-sign-in-alt me-2"></i>Clock In</h6>
                                </div>
                                <div class="card-body">
                    `;
                    
                    if (attendance.clock_in_photo) {
                        photosHtml += `
                            <div class="mb-3">
                                <label class="form-label fw-bold">Foto:</label>
                                <div class="text-center">
                                    <img src="/storage/${attendance.clock_in_photo}" 
                                         class="img-fluid rounded shadow-sm" 
                                         style="max-height: 200px; cursor: pointer;"
                                         onclick="window.open('/storage/${attendance.clock_in_photo}', '_blank')"
                                         alt="Foto Clock In">
                                    <div class="mt-2">
                                        <small class="text-muted">Klik untuk memperbesar</small>
                                    </div>
                                </div>
                            </div>
                        `;
                    }
                    
                    if (attendance.clock_in_latitude && attendance.clock_in_longitude) {
                        photosHtml += `
                            <div class="mb-3">
                                <label class="form-label fw-bold">Lokasi:</label>
                                <div class="bg-light p-2 rounded">
                                    <small class="d-block"><strong>Latitude:</strong> ${attendance.clock_in_latitude}</small>
                                    <small class="d-block"><strong>Longitude:</strong> ${attendance.clock_in_longitude}</small>
                                    ${attendance.distance_from_school ? `<small class="d-block"><strong>Jarak dari sekolah:</strong> ${attendance.distance_from_school}m</small>` : ''}
                                </div>
                                <div class="mt-2">
                                    <a href="https://www.google.com/maps?q=${attendance.clock_in_latitude},${attendance.clock_in_longitude}" 
                                       target="_blank" class="btn btn-outline-primary btn-sm">
                                        <i class="fas fa-map-marker-alt me-1"></i>Lihat di Google Maps
                                    </a>
                                </div>
                            </div>
                        `;
                    }
                    
                    photosHtml += `
                                </div>
                            </div>
                        </div>
                    `;
                }
                
                // Clock Out Photo and Location
                if (attendance.clock_out_photo || attendance.clock_out_latitude) {
                    photosHtml += `
                        <div class="col-md-6">
                            <div class="card border-warning">
                                <div class="card-header bg-warning text-dark">
                                    <h6 class="mb-0"><i class="fas fa-sign-out-alt me-2"></i>Clock Out</h6>
                                </div>
                                <div class="card-body">
                    `;
                    
                    if (attendance.clock_out_photo) {
                        photosHtml += `
                            <div class="mb-3">
                                <label class="form-label fw-bold">Foto:</label>
                                <div class="text-center">
                                    <img src="/storage/${attendance.clock_out_photo}" 
                                         class="img-fluid rounded shadow-sm" 
                                         style="max-height: 200px; cursor: pointer;"
                                         onclick="window.open('/storage/${attendance.clock_out_photo}', '_blank')"
                                         alt="Foto Clock Out">
                                    <div class="mt-2">
                                        <small class="text-muted">Klik untuk memperbesar</small>
                                    </div>
                                </div>
                            </div>
                        `;
                    }
                    
                    if (attendance.clock_out_latitude && attendance.clock_out_longitude) {
                        photosHtml += `
                            <div class="mb-3">
                                <label class="form-label fw-bold">Lokasi:</label>
                                <div class="bg-light p-2 rounded">
                                    <small class="d-block"><strong>Latitude:</strong> ${attendance.clock_out_latitude}</small>
                                    <small class="d-block"><strong>Longitude:</strong> ${attendance.clock_out_longitude}</small>
                                </div>
                                <div class="mt-2">
                                    <a href="https://www.google.com/maps?q=${attendance.clock_out_latitude},${attendance.clock_out_longitude}" 
                                       target="_blank" class="btn btn-outline-primary btn-sm">
                                        <i class="fas fa-map-marker-alt me-1"></i>Lihat di Google Maps
                                    </a>
                                </div>
                            </div>
                        `;
                    }
                    
                    photosHtml += `
                                </div>
                            </div>
                        </div>
                    `;
                }
                
                content.innerHTML = `
                    <!-- Student Info -->
                    <div class="card border-primary mb-3">
                        <div class="card-header bg-primary text-white">
                            <h6 class="mb-0"><i class="fas fa-user me-2"></i>Informasi Siswa</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4">
                                    <strong>Nama:</strong><br>
                                    <span class="text-muted">${student.name}</span>
                                </div>
                                <div class="col-md-4">
                                    <strong>NISN:</strong><br>
                                    <span class="text-muted">${student.nisn}</span>
                                </div>
                                <div class="col-md-4">
                                    <strong>Kelas:</strong><br>
                                    <span class="text-muted">${student.kelas || '-'}</span>
                                </div>
                            </div>
                            <div class="row mt-2">
                                <div class="col-md-4">
                                    <strong>Tanggal:</strong><br>
                                    <span class="text-muted">${new Date(attendance.date).toLocaleDateString('id-ID')}</span>
                                </div>
                                <div class="col-md-4">
                                    <strong>Clock In:</strong><br>
                                    <span class="text-muted">${attendance.clock_in || '-'}</span>
                                </div>
                                <div class="col-md-4">
                                    <strong>Clock Out:</strong><br>
                                    <span class="text-muted">${attendance.clock_out || '-'}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Photos and Location -->
                    <div class="row">
                        ${photosHtml}
                    </div>
                    
                    ${attendance.notes ? `
                        <div class="card border-info mt-3">
                            <div class="card-header bg-info text-white">
                                <h6 class="mb-0"><i class="fas fa-sticky-note me-2"></i>Catatan</h6>
                            </div>
                            <div class="card-body">
                                <p class="mb-0">${attendance.notes}</p>
                            </div>
                        </div>
                    ` : ''}
                `;
            } else {
                content.innerHTML = `
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        Gagal memuat detail absensi: ${data.message}
                    </div>
                `;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            content.innerHTML = `
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    Terjadi kesalahan saat memuat detail absensi.
                </div>
            `;
        });
}
</script>
@endpush

<!-- Attendance Detail Modal -->
<div class="modal fade" id="attendanceDetailModal" tabindex="-1" aria-labelledby="attendanceDetailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="attendanceDetailModalLabel">
                    <i class="fas fa-info-circle me-2"></i>Detail Absensi
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="attendanceDetailContent">
                    <div class="text-center">
                        <div class="spinner-border" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-2">Memuat detail absensi...</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

@endsection