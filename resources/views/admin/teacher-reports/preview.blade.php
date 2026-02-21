@extends('layouts.admin')

@section('title', 'Preview Laporan Absensi Guru')
@section('header', 'Preview Laporan Absensi Guru')

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <!-- Export Options -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3">
                    <div>
                        <h6 class="mb-1">Pilihan Export</h6>
                        <small class="text-muted">Pilih format file yang diinginkan untuk mengunduh laporan</small>
                    </div>
                    <div class="d-flex flex-wrap gap-2">
                        <a href="{{ route('admin.teacher-reports.export', array_merge(request()->query(), ['format' => 'pdf'])) }}" 
                           class="btn btn-danger btn-sm" target="_blank">
                            <i class="fas fa-file-pdf me-1"></i> Export PDF
                        </a>
                        <a href="{{ route('admin.teacher-reports.export', array_merge(request()->query(), ['format' => 'excel'])) }}" 
                           class="btn btn-success btn-sm">
                            <i class="fas fa-file-excel me-1"></i> Export Excel
                        </a>
                        <a href="{{ route('admin.teacher-reports.export', array_merge(request()->query(), ['format' => 'word'])) }}" 
                           class="btn btn-primary btn-sm">
                            <i class="fas fa-file-word me-1"></i> Export Word
                        </a>
                        <a href="{{ route('admin.teacher-reports.export', array_merge(request()->query(), ['format' => 'csv'])) }}" 
                           class="btn btn-secondary btn-sm">
                            <i class="fas fa-file-csv me-1"></i> Export CSV
                        </a>
                        <a href="{{ route('admin.teacher-reports.index') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-arrow-left me-1"></i> Kembali
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Report Preview -->
<div class="card border-0 shadow-sm" id="reportPreview">
    <div class="card-body p-4">
        <!-- Report Header -->
        <div class="text-center mb-4 pb-3 border-bottom">
            <h3 class="fw-bold text-primary mb-2">LAPORAN ABSENSI GURU</h3>
            <h5 class="text-muted mb-3">Sistem Absensi Sekolah</h5>
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <div class="row g-2 small">
                        <div class="col-md-6">
                            <strong>Periode:</strong> {{ \Carbon\Carbon::parse($startDate)->format('d F Y') }} - {{ \Carbon\Carbon::parse($endDate)->format('d F Y') }}
                        </div>
                        <div class="col-md-6">
                            <strong>Guru:</strong> {{ $filterInfo['teacher_name'] }}
                        </div>
                        <div class="col-md-6">
                            <strong>Tanggal Cetak:</strong> {{ \Carbon\Carbon::now()->format('d F Y H:i') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Summary Statistics -->
        <div class="row mb-4">
            <div class="col-12">
                <h6 class="fw-bold mb-3">Ringkasan Statistik</h6>
                <div class="row g-3">
                    <div class="col-md-3">
                        <div class="text-center p-3 bg-light rounded">
                            <div class="h5 fw-bold text-primary mb-1">{{ $summary['total'] }}</div>
                            <small class="text-muted">Total</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-center p-3 bg-success bg-opacity-10 rounded">
                            <div class="h5 fw-bold text-success mb-1">{{ $summary['present'] }}</div>
                            <small class="text-muted">Hadir</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-center p-3 bg-danger bg-opacity-10 rounded">
                            <div class="h5 fw-bold text-danger mb-1">{{ $summary['absent'] }}</div>
                            <small class="text-muted">Tidak Hadir</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-center p-3 bg-info bg-opacity-10 rounded">
                            <div class="h5 fw-bold text-info mb-1">{{ $summary['permission'] + $summary['sick'] }}</div>
                            <small class="text-muted">Izin/Sakit</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Data Table -->
        <div class="table-responsive">
            <table class="table table-bordered table-sm">
                <thead class="table-light">
                    <tr>
                        <th class="text-center" width="5%">No</th>
                        <th>Tanggal</th>
                        <th>Nama Guru</th>
                        <th>Email</th>
                        <th>Jabatan</th>
                        <th class="text-center">Clock In</th>
                        <th class="text-center">Clock Out</th>
                        <th class="text-center">Durasi</th>
                        <th class="text-center">Status</th>
                        <th>Keterangan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($attendances as $index => $attendance)
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td>{{ \Carbon\Carbon::parse($attendance->date)->format('d/m/Y') }}</td>
                        <td>{{ $attendance->teacher->name }}</td>
                        <td>{{ $attendance->teacher->email }}</td>
                        <td>{{ $attendance->teacher->jabatan ?? '-' }}</td>
                        <td class="text-center">
                            {{ $attendance->clock_in ? \Carbon\Carbon::parse($attendance->clock_in)->format('H:i') : '-' }}
                        </td>
                        <td class="text-center">
                            {{ $attendance->clock_out ? \Carbon\Carbon::parse($attendance->clock_out)->format('H:i') : '-' }}
                        </td>
                        <td class="text-center">
                            {{ $attendance->getWorkDurationFormatted() }}
                        </td>
                        <td class="text-center">
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
                        <td>{{ $attendance->notes ?? '-' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="10" class="text-center text-muted">Tidak ada data</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
