@extends('layouts.admin')

@section('title', 'Dashboard')
@section('header', 'Dashboard')

@section('content')
<!-- Date Selector & Holiday Status -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3">
                    <div>
                        <h6 class="mb-1">Absensi Tanggal: {{ \Carbon\Carbon::parse($selectedDate)->format('d F Y') }}</h6>
                        @if($isHoliday)
                            <div class="d-flex align-items-center text-danger">
                                <i class="fas fa-calendar-times me-2"></i>
                                <span><strong>Hari Libur:</strong> {{ $holiday->name }}</span>
                            </div>
                        @else
                            <div class="d-flex align-items-center text-success">
                                <i class="fas fa-calendar-check me-2"></i>
                                <span>Hari Sekolah</span>
                            </div>
                        @endif
                    </div>
                    <div class="d-flex gap-2">
                        <input type="date" id="dateSelector" value="{{ $selectedDate }}" class="form-control" style="width: auto;">
                        <a href="{{ route('admin.attendance.index', ['date' => $selectedDate]) }}" class="btn btn-primary">
                            <i class="fas fa-clipboard-list me-1"></i> Kelola Absensi
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <!-- Card Total Siswa -->
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="p-3 bg-primary bg-opacity-10 rounded">
                        <svg class="text-primary" width="32" height="32" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                        </svg>
                    </div>
                    <div class="ms-3">
                        <p class="text-muted small mb-0">Total Siswa</p>
                        <p class="h4 fw-bold text-dark mb-0">{{ $totalStudents ?? 0 }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Card Hadir Hari Ini -->
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="p-3 bg-success bg-opacity-10 rounded">
                        <svg class="text-success" width="32" height="32" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div class="ms-3">
                        <p class="text-muted small mb-0">Hadir Hari Ini</p>
                        <p class="h4 fw-bold text-dark mb-0">{{ $presentToday ?? 0 }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Card Tidak Hadir -->
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="p-3 bg-danger bg-opacity-10 rounded">
                        <svg class="text-danger" width="32" height="32" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div class="ms-3">
                        <p class="text-muted small mb-0">Tidak Hadir</p>
                        <p class="h4 fw-bold text-dark mb-0">{{ $absentToday ?? 0 }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Card Sakit/Izin -->
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="p-3 bg-warning bg-opacity-10 rounded">
                        <svg class="text-warning" width="32" height="32" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                        </svg>
                    </div>
                    <div class="ms-3">
                        <p class="text-muted small mb-0">Sakit/Izin</p>
                        <p class="h4 fw-bold text-dark mb-0">{{ ($sickToday ?? 0) + ($permissionToday ?? 0) }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Weekly Overview -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h6 class="card-title mb-0">Ringkasan 7 Hari Terakhir</h6>
            </div>
            <div class="card-body">
                <div class="row g-2">
                    @foreach($weeklyStats as $day)
                    <div class="col">
                        <div class="text-center p-2 rounded {{ $day['is_selected'] ? 'bg-primary text-white' : ($day['is_holiday'] ? 'bg-light text-muted' : 'bg-light') }}">
                            <div class="small fw-bold">{{ $day['day'] }}</div>
                            <div class="small">{{ $day['day_name'] }}</div>
                            @if($day['is_holiday'])
                                <div class="small">
                                    <i class="fas fa-calendar-times"></i>
                                </div>
                            @else
                                <div class="small">
                                    <span class="text-success">{{ $day['present'] }}</span> /
                                    <span class="text-danger">{{ $day['absent'] }}</span>
                                </div>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Main Content Row -->
<div class="row g-4">
    <!-- Recent Attendance -->
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom">
                <h6 class="card-title mb-0">Aktivitas Absensi Hari Ini</h6>
                <small class="text-muted">Diurutkan dari yang paling awal absen</small>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Nama Siswa</th>
                                <th>Kelas</th>
                                <th>Clock In</th>
                                <th>Clock Out</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentAttendances ?? [] as $attendance)
                            <tr>
                                <td>{{ $attendance->student->name ?? '-' }}</td>
                                <td>{{ $attendance->student->class->name ?? '-' }}</td>
                                <td>
                                    @if($attendance->clock_in)
                                        <span class="text-success">{{ \Carbon\Carbon::parse($attendance->clock_in)->format('H:i') }}</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if($attendance->clock_out)
                                        <span class="text-info">{{ \Carbon\Carbon::parse($attendance->clock_out)->format('H:i') }}</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>{!! $attendance->status_badge !!}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">
                                    @if($isHoliday)
                                        <i class="fas fa-calendar-times me-2"></i>Hari libur - tidak ada aktivitas absensi
                                    @else
                                        Belum ada aktivitas absensi hari ini
                                    @endif
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Sidebar Info -->
    <div class="col-lg-4">
        <!-- Quick Stats -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white">
                <h6 class="card-title mb-0">Statistik Cepat</h6>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted">Belum Tercatat:</span>
                    <span class="fw-bold {{ $notRecordedToday > 0 ? 'text-warning' : 'text-success' }}">
                        {{ $notRecordedToday }}
                    </span>
                </div>
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted">Sakit:</span>
                    <span class="fw-bold text-info">{{ $sickToday }}</span>
                </div>
                <div class="d-flex justify-content-between align-items-center">
                    <span class="text-muted">Izin:</span>
                    <span class="fw-bold text-secondary">{{ $permissionToday }}</span>
                </div>
            </div>
        </div>

        <!-- Unrecorded Students (if any) -->
        @if(!$isHoliday && count($unrecordedStudents) > 0)
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h6 class="card-title mb-0">Siswa Belum Absen</h6>
            </div>
            <div class="card-body">
                @foreach($unrecordedStudents as $student)
                <div class="d-flex justify-content-between align-items-center {{ !$loop->last ? 'mb-2' : '' }}">
                    <div>
                        <div class="fw-medium">{{ $student->name }}</div>
                        <small class="text-muted">{{ $student->class->name ?? '-' }}</small>
                    </div>
                    <span class="badge bg-light text-dark">Belum</span>
                </div>
                @endforeach
                
                @if($notRecordedToday > 5)
                <div class="text-center mt-2">
                    <small class="text-muted">dan {{ $notRecordedToday - 5 }} siswa lainnya</small>
                </div>
                @endif
            </div>
        </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Date selector change
    document.getElementById('dateSelector').addEventListener('change', function() {
        const selectedDate = this.value;
        const url = new URL(window.location);
        url.searchParams.set('date', selectedDate);
        window.location.href = url.toString();
    });
});
</script>
@endpush
@endsection
