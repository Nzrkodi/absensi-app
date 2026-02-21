@extends('layouts.admin')

@section('title', 'Laporan Absensi Guru')
@section('header', 'Laporan Absensi Guru')

@section('content')
<!-- Filter Section -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white">
        <h6 class="card-title mb-0">Filter Laporan</h6>
    </div>
    <div class="card-body">
        <form action="{{ route('admin.teacher-reports.index') }}" method="GET">
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
                    <label for="teacher_id" class="form-label">Guru</label>
                    <select class="form-select" id="teacher_id" name="teacher_id">
                        <option value="">Semua Guru</option>
                        @foreach($teachers as $teacher)
                            <option value="{{ $teacher->id }}" {{ $teacherId == $teacher->id ? 'selected' : '' }}>
                                {{ $teacher->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12">
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search me-1"></i> Filter
                        </button>
                        <a href="{{ route('admin.teacher-reports.index') }}" class="btn btn-outline-danger">
                            <i class="fas fa-refresh me-1"></i> Reset
                        </a>
                        <a href="{{ route('admin.teacher-reports.preview', request()->query()) }}" class="btn btn-success">
                            <i class="fas fa-eye me-1"></i> Preview & Export
                        </a>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Summary Statistics -->
<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm text-center">
            <div class="card-body">
                <div class="h4 fw-bold text-primary">{{ $summary['total'] }}</div>
                <div class="small text-muted">Total Record</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm text-center">
            <div class="card-body">
                <div class="h4 fw-bold text-success">{{ $summary['present'] }}</div>
                <div class="small text-muted">Hadir</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm text-center">
            <div class="card-body">
                <div class="h4 fw-bold text-danger">{{ $summary['absent'] }}</div>
                <div class="small text-muted">Tidak Hadir</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm text-center">
            <div class="card-body">
                <div class="h4 fw-bold text-info">{{ $summary['permission'] + $summary['sick'] }}</div>
                <div class="small text-muted">Izin/Sakit</div>
            </div>
        </div>
    </div>
</div>

<!-- Attendance Table -->
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white">
        <h6 class="card-title mb-0">Data Absensi Guru</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Tanggal</th>
                        <th>Nama Guru</th>
                        <th>Email</th>
                        <th>Jabatan</th>
                        <th>Clock In</th>
                        <th>Clock Out</th>
                        <th>Durasi</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($attendances as $index => $attendance)
                    <tr>
                        <td>{{ $attendances->firstItem() + $index }}</td>
                        <td>{{ \Carbon\Carbon::parse($attendance->date)->format('d/m/Y') }}</td>
                        <td>{{ $attendance->teacher->name }}</td>
                        <td>{{ $attendance->teacher->email }}</td>
                        <td>{{ $attendance->teacher->jabatan ?? '-' }}</td>
                        <td>
                            @if($attendance->clock_in)
                                <span class="badge bg-success">
                                    {{ \Carbon\Carbon::parse($attendance->clock_in)->format('H:i') }}
                                </span>
                            @else
                                <span class="badge bg-secondary">-</span>
                            @endif
                        </td>
                        <td>
                            @if($attendance->clock_out)
                                <span class="badge bg-info">
                                    {{ \Carbon\Carbon::parse($attendance->clock_out)->format('H:i') }}
                                </span>
                            @else
                                <span class="badge bg-secondary">-</span>
                            @endif
                        </td>
                        <td>
                            @if($attendance->clock_in && $attendance->clock_out)
                                {{ $attendance->getWorkDurationFormatted() }}
                            @else
                                -
                            @endif
                        </td>
                        <td>
                            @if($attendance->status == 'hadir')
                                <span class="badge bg-success">Hadir</span>
                            @elseif($attendance->status == 'izin')
                                <span class="badge bg-warning">Izin</span>
                            @elseif($attendance->status == 'sakit')
                                <span class="badge bg-info">Sakit</span>
                            @else
                                <span class="badge bg-danger">Tidak Hadir</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center text-muted">Tidak ada data</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <div class="mt-3">
            {{ $attendances->links() }}
        </div>
    </div>
</div>
@endsection
