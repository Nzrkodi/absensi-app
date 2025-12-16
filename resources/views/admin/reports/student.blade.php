@extends('layouts.admin')

@section('title', 'Laporan Siswa')
@section('header', 'Laporan Absensi Siswa')

@section('content')
<!-- Student Info & Period -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3">
                    <div>
                        <h5 class="mb-1">{{ $student->user->name }}</h5>
                        <div class="text-muted">
                            <span class="me-3"><i class="fas fa-id-card me-1"></i> NIS: {{ $student->student_code }}</span>
                            <span class="me-3"><i class="fas fa-school me-1"></i> Kelas: {{ $student->class->name ?? '-' }}</span>
                            <span><i class="fas fa-calendar me-1"></i> Periode: {{ \Carbon\Carbon::parse($startDate)->format('d M Y') }} - {{ \Carbon\Carbon::parse($endDate)->format('d M Y') }}</span>
                        </div>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="{{ route('admin.reports.index', ['student_id' => $student->id, 'start_date' => $startDate, 'end_date' => $endDate]) }}" 
                           class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-1"></i> Kembali
                        </a>
                        <a href="{{ route('admin.reports.export', ['student_id' => $student->id, 'start_date' => $startDate, 'end_date' => $endDate]) }}" 
                           class="btn btn-success">
                            <i class="fas fa-download me-1"></i> Export
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Summary Statistics -->
<div class="row g-4 mb-4">
    <div class="col-md-2">
        <div class="card border-0 shadow-sm text-center">
            <div class="card-body">
                <div class="h4 fw-bold text-primary">{{ $summary->total ?? 0 }}</div>
                <div class="small text-muted">Total Hari</div>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card border-0 shadow-sm text-center">
            <div class="card-body">
                <div class="h4 fw-bold text-success">{{ $summary->present ?? 0 }}</div>
                <div class="small text-muted">Hadir</div>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card border-0 shadow-sm text-center">
            <div class="card-body">
                <div class="h4 fw-bold text-warning">{{ $summary->late ?? 0 }}</div>
                <div class="small text-muted">Terlambat</div>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card border-0 shadow-sm text-center">
            <div class="card-body">
                <div class="h4 fw-bold text-danger">{{ $summary->absent ?? 0 }}</div>
                <div class="small text-muted">Tidak Hadir</div>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card border-0 shadow-sm text-center">
            <div class="card-body">
                <div class="h4 fw-bold text-info">{{ ($summary->sick ?? 0) + ($summary->permission ?? 0) }}</div>
                <div class="small text-muted">Sakit/Izin</div>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card border-0 shadow-sm text-center">
            <div class="card-body">
                <div class="h4 fw-bold text-{{ $attendanceRate >= 80 ? 'success' : ($attendanceRate >= 60 ? 'warning' : 'danger') }}">
                    {{ $attendanceRate }}%
                </div>
                <div class="small text-muted">Kehadiran</div>
            </div>
        </div>
    </div>
</div>

<!-- Attendance Progress -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h6 class="card-title mb-0">Progress Kehadiran</h6>
            </div>
            <div class="card-body">
                <div class="progress mb-2" style="height: 20px;">
                    @php
                        $presentPercentage = $summary->total > 0 ? ($summary->present / $summary->total) * 100 : 0;
                        $latePercentage = $summary->total > 0 ? ($summary->late / $summary->total) * 100 : 0;
                        $absentPercentage = $summary->total > 0 ? ($summary->absent / $summary->total) * 100 : 0;
                        $excusedPercentage = $summary->total > 0 ? (($summary->sick + $summary->permission) / $summary->total) * 100 : 0;
                    @endphp
                    
                    @if($presentPercentage > 0)
                        <div class="progress-bar bg-success" style="width: {{ $presentPercentage }}%" title="Hadir: {{ $summary->present }} hari"></div>
                    @endif
                    @if($latePercentage > 0)
                        <div class="progress-bar bg-warning" style="width: {{ $latePercentage }}%" title="Terlambat: {{ $summary->late }} hari"></div>
                    @endif
                    @if($excusedPercentage > 0)
                        <div class="progress-bar bg-info" style="width: {{ $excusedPercentage }}%" title="Sakit/Izin: {{ $summary->sick + $summary->permission }} hari"></div>
                    @endif
                    @if($absentPercentage > 0)
                        <div class="progress-bar bg-danger" style="width: {{ $absentPercentage }}%" title="Tidak Hadir: {{ $summary->absent }} hari"></div>
                    @endif
                </div>
                <div class="d-flex justify-content-between small text-muted">
                    <span><i class="fas fa-square text-success me-1"></i> Hadir ({{ $summary->present }})</span>
                    <span><i class="fas fa-square text-warning me-1"></i> Terlambat ({{ $summary->late }})</span>
                    <span><i class="fas fa-square text-info me-1"></i> Sakit/Izin ({{ $summary->sick + $summary->permission }})</span>
                    <span><i class="fas fa-square text-danger me-1"></i> Tidak Hadir ({{ $summary->absent }})</span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Detailed Attendance Records -->
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white">
        <h6 class="card-title mb-0">Riwayat Absensi Detail</h6>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Tanggal</th>
                        <th>Hari</th>
                        <th>Clock In</th>
                        <th>Clock Out</th>
                        <th>Durasi</th>
                        <th>Status</th>
                        <th>Keterangan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($attendances as $attendance)
                    <tr>
                        <td>{{ $attendance->date->format('d M Y') }}</td>
                        <td>{{ $attendance->date->format('l') }}</td>
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
                        <td>
                            @if($attendance->clock_in && $attendance->clock_out)
                                @php
                                    $clockIn = \Carbon\Carbon::parse($attendance->clock_in);
                                    $clockOut = \Carbon\Carbon::parse($attendance->clock_out);
                                    $duration = $clockIn->diff($clockOut);
                                @endphp
                                <span class="text-primary">{{ $duration->format('%h jam %i menit') }}</span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>{!! $attendance->status_badge !!}</td>
                        <td>
                            @if($attendance->notes)
                                <span class="text-truncate d-inline-block" style="max-width: 200px;" title="{{ $attendance->notes }}">
                                    {{ $attendance->notes }}
                                </span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">
                            Tidak ada data absensi untuk periode yang dipilih
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($attendances->hasPages())
        <div class="p-3">
            {{ $attendances->appends(request()->query())->links() }}
        </div>
        @endif
    </div>
</div>

<!-- Period Selector -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h6 class="card-title mb-0">Ubah Periode</h6>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.reports.student', $student) }}" method="GET">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label for="start_date" class="form-label">Tanggal Mulai</label>
                            <input type="date" class="form-control" id="start_date" name="start_date" value="{{ $startDate }}">
                        </div>
                        <div class="col-md-4">
                            <label for="end_date" class="form-label">Tanggal Akhir</label>
                            <input type="date" class="form-control" id="end_date" name="end_date" value="{{ $endDate }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">&nbsp;</label>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search me-1"></i> Update Periode
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection