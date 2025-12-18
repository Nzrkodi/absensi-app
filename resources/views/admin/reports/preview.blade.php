@extends('layouts.admin')

@section('title', 'Preview Laporan Absensi')
@section('header', 'Preview Laporan Absensi')

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
                        <a href="{{ route('admin.reports.export', array_merge(request()->query(), ['format' => 'pdf'])) }}" 
                           class="btn btn-danger btn-sm" target="_blank">
                            <i class="fas fa-file-pdf me-1"></i> Export PDF
                        </a>
                        <a href="{{ route('admin.reports.export', array_merge(request()->query(), ['format' => 'excel'])) }}" 
                           class="btn btn-success btn-sm">
                            <i class="fas fa-file-excel me-1"></i> Export Excel
                        </a>
                        <a href="{{ route('admin.reports.export', array_merge(request()->query(), ['format' => 'word'])) }}" 
                           class="btn btn-primary btn-sm">
                            <i class="fas fa-file-word me-1"></i> Export Word
                        </a>
                        <a href="{{ route('admin.reports.export', array_merge(request()->query(), ['format' => 'csv'])) }}" 
                           class="btn btn-secondary btn-sm">
                            <i class="fas fa-file-csv me-1"></i> Export CSV
                        </a>
                        <a href="{{ route('admin.reports.index') }}" class="btn btn-outline-secondary btn-sm">
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
            <h3 class="fw-bold text-primary mb-2">LAPORAN ABSENSI SISWA</h3>
            <h5 class="text-muted mb-3">Sistem Absensi Sekolah</h5>
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <div class="row g-2 small">
                        <div class="col-md-6">
                            <strong>Periode:</strong> {{ \Carbon\Carbon::parse($startDate)->format('d F Y') }} - {{ \Carbon\Carbon::parse($endDate)->format('d F Y') }}
                        </div>
                        <div class="col-md-6">
                            <strong>Kelas:</strong> {{ $filterInfo['class_name'] }}
                        </div>
                        <div class="col-md-6">
                            <strong>Siswa:</strong> {{ $filterInfo['student_name'] }}
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
                    <div class="col-md-2">
                        <div class="text-center p-3 bg-light rounded">
                            <div class="h5 fw-bold text-primary mb-1">{{ $summary->total ?? 0 }}</div>
                            <small class="text-muted">Total</small>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="text-center p-3 bg-success bg-opacity-10 rounded">
                            <div class="h5 fw-bold text-success mb-1">{{ $summary->present ?? 0 }}</div>
                            <small class="text-muted">Hadir</small>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="text-center p-3 bg-warning bg-opacity-10 rounded">
                            <div class="h5 fw-bold text-warning mb-1">{{ $summary->late ?? 0 }}</div>
                            <small class="text-muted">Terlambat</small>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="text-center p-3 bg-danger bg-opacity-10 rounded">
                            <div class="h5 fw-bold text-danger mb-1">{{ $summary->absent ?? 0 }}</div>
                            <small class="text-muted">Tidak Hadir</small>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="text-center p-3 bg-info bg-opacity-10 rounded">
                            <div class="h5 fw-bold text-info mb-1">{{ $summary->sick ?? 0 }}</div>
                            <small class="text-muted">Sakit</small>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="text-center p-3 bg-secondary bg-opacity-10 rounded">
                            <div class="h5 fw-bold text-secondary mb-1">{{ $summary->permission ?? 0 }}</div>
                            <small class="text-muted">Izin</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Data Table -->
        <div class="mb-4">
            <h6 class="fw-bold mb-3">Detail Absensi</h6>
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="table-light">
                        <tr>
                            <th class="text-center" style="width: 5%">No</th>
                            <th style="width: 12%">Tanggal</th>
                            <th style="width: 15%">NISN</th>
                            <th style="width: 25%">Nama Siswa</th>
                            <th style="width: 12%">Kelas</th>
                            <th class="text-center" style="width: 8%">Clock In</th>
                            <th class="text-center" style="width: 8%">Clock Out</th>
                            <th class="text-center" style="width: 10%">Status</th>
                            <th style="width: 15%">Keterangan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($attendances as $index => $attendance)
                        <tr>
                            <td class="text-center">{{ $index + 1 }}</td>
                            <td>{{ $attendance->date->format('d/m/Y') }}</td>
                            <td>{{ $attendance->student->nisn ?? '-' }}</td>
                            <td>{{ $attendance->student->name ?? '-' }}</td>
                            <td>{{ $attendance->student->class->name ?? '-' }}</td>
                            <td class="text-center">
                                {{ $attendance->clock_in ? \Carbon\Carbon::parse($attendance->clock_in)->format('H:i') : '-' }}
                            </td>
                            <td class="text-center">
                                {{ $attendance->clock_out ? \Carbon\Carbon::parse($attendance->clock_out)->format('H:i') : '-' }}
                            </td>
                            <td class="text-center">
                                @if($attendance->status === 'present')
                                    <span class="badge bg-success">Hadir</span>
                                @elseif($attendance->status === 'late')
                                    <span class="badge bg-warning">Terlambat</span>
                                @elseif($attendance->status === 'absent')
                                    <span class="badge bg-danger">Tidak Hadir</span>
                                @elseif($attendance->status === 'sick')
                                    <span class="badge bg-info">Sakit</span>
                                @elseif($attendance->status === 'permission')
                                    <span class="badge bg-secondary">Izin</span>
                                @else
                                    <span class="badge bg-light text-dark">{{ ucfirst($attendance->status) }}</span>
                                @endif
                            </td>
                            <td>{{ $attendance->notes ?? '-' }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted py-4">
                                Tidak ada data absensi untuk periode yang dipilih
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Footer -->
        <div class="row mt-4 pt-3 border-top">
            <div class="col-md-6">
                <small class="text-muted">
                    <i class="fas fa-info-circle me-1"></i>
                    Laporan ini dibuat secara otomatis oleh Sistem Absensi Sekolah
                </small>
            </div>
            <div class="col-md-6 text-end">
                <small class="text-muted">
                    Total {{ $attendances->count() }} record ditemukan
                </small>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
@media print {
    .card {
        border: none !important;
        box-shadow: none !important;
    }
    
    .btn, .card:first-child {
        display: none !important;
    }
    
    #reportPreview {
        margin: 0 !important;
        padding: 0 !important;
    }
}

.table th {
    background-color: #f8f9fa !important;
    font-weight: 600;
    font-size: 0.875rem;
}

.table td {
    font-size: 0.875rem;
    vertical-align: middle;
}

.badge {
    font-size: 0.75rem;
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Add print functionality
    document.addEventListener('keydown', function(e) {
        if (e.ctrlKey && e.key === 'p') {
            e.preventDefault();
            window.print();
        }
    });
});
</script>
@endpush
@endsection