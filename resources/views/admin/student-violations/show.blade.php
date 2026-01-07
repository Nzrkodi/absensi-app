@extends('layouts.admin')

@section('title', 'Detail Pelanggaran Siswa')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Detail Pelanggaran Siswa</h3>
                    <div class="card-tools">
                        <a href="{{ route('admin.student-violations.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Kembali
                        </a>
                        <a href="{{ route('admin.student-violations.edit', $studentViolation) }}" class="btn btn-warning">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <!-- Student Information -->
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header bg-primary text-white">
                                    <h5 class="card-title mb-0">
                                        <i class="fas fa-user"></i> Informasi Siswa
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <table class="table table-borderless">
                                        <tr>
                                            <td width="40%"><strong>Nama:</strong></td>
                                            <td>{{ $studentViolation->student->name }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>NISN:</strong></td>
                                            <td>{{ $studentViolation->student->nisn }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Kelas:</strong></td>
                                            <td>{{ $studentViolation->student->kelas }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Status:</strong></td>
                                            <td>
                                                <span class="badge bg-{{ $studentViolation->student->status === 'active' ? 'success' : 'secondary' }}">
                                                    {{ ucfirst($studentViolation->student->status) }}
                                                </span>
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Violation Information -->
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header bg-danger text-white">
                                    <h5 class="card-title mb-0">
                                        <i class="fas fa-exclamation-triangle"></i> Informasi Pelanggaran
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <table class="table table-borderless">
                                        <tr>
                                            <td width="40%"><strong>Jenis:</strong></td>
                                            <td>{{ $studentViolation->violationType->name }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Kategori:</strong></td>
                                            <td>
                                                <span class="badge bg-{{ $studentViolation->violationType->badge_color }}">
                                                    {{ ucfirst($studentViolation->violationType->category) }}
                                                </span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><strong>Poin:</strong></td>
                                            <td>
                                                <span class="badge bg-info">{{ $studentViolation->violationType->points }} poin</span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><strong>Status:</strong></td>
                                            <td>
                                                <span class="badge bg-{{ $studentViolation->status_badge_color }}">
                                                    {{ ucfirst($studentViolation->status) }}
                                                </span>
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-4">
                        <!-- Violation Details -->
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header bg-info text-white">
                                    <h5 class="card-title mb-0">
                                        <i class="fas fa-clipboard-list"></i> Detail Pelanggaran
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-3">
                                            <strong>Tanggal:</strong><br>
                                            <span class="text-muted">{{ $studentViolation->violation_date->format('d F Y') }}</span>
                                        </div>
                                        <div class="col-md-3">
                                            <strong>Waktu:</strong><br>
                                            <span class="text-muted">
                                                {{ $studentViolation->violation_time ? $studentViolation->violation_time->format('H:i') : '-' }}
                                            </span>
                                        </div>
                                        <div class="col-md-3">
                                            <strong>Lokasi:</strong><br>
                                            <span class="text-muted">{{ $studentViolation->location ?: '-' }}</span>
                                        </div>
                                        <div class="col-md-3">
                                            <strong>Dilaporkan Oleh:</strong><br>
                                            <span class="text-muted">{{ $studentViolation->reported_by ?: '-' }}</span>
                                        </div>
                                    </div>

                                    @if($studentViolation->description)
                                        <hr>
                                        <div class="row">
                                            <div class="col-12">
                                                <strong>Deskripsi Pelanggaran:</strong><br>
                                                <div class="mt-2 p-3 bg-light rounded">
                                                    {{ $studentViolation->description }}
                                                </div>
                                            </div>
                                        </div>
                                    @endif

                                    @if($studentViolation->resolution_notes)
                                        <hr>
                                        <div class="row">
                                            <div class="col-12">
                                                <strong>Catatan Penyelesaian:</strong><br>
                                                <div class="mt-2 p-3 bg-success bg-opacity-10 rounded border border-success">
                                                    {{ $studentViolation->resolution_notes }}
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Student Violation Summary -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header bg-warning text-dark">
                                    <h5 class="card-title mb-0">
                                        <i class="fas fa-chart-bar"></i> Ringkasan Pelanggaran Siswa
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="row text-center">
                                        <div class="col-md-3">
                                            <div class="border rounded p-3">
                                                <h4 class="text-primary">{{ $studentViolation->student->violations()->count() }}</h4>
                                                <small class="text-muted">Total Pelanggaran</small>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="border rounded p-3">
                                                <h4 class="text-info">{{ $studentViolation->student->total_violation_points }}</h4>
                                                <small class="text-muted">Total Poin</small>
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="border rounded p-3">
                                                <h4 class="text-success">{{ $studentViolation->student->getViolationsByCategory('ringan') }}</h4>
                                                <small class="text-muted">Ringan</small>
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="border rounded p-3">
                                                <h4 class="text-warning">{{ $studentViolation->student->getViolationsByCategory('sedang') }}</h4>
                                                <small class="text-muted">Sedang</small>
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="border rounded p-3">
                                                <h4 class="text-danger">{{ $studentViolation->student->getViolationsByCategory('berat') }}</h4>
                                                <small class="text-muted">Berat</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <a href="{{ route('admin.student-violations.index') }}" class="btn btn-secondary">
                                        <i class="fas fa-arrow-left"></i> Kembali ke Daftar
                                    </a>
                                </div>
                                <div>
                                    <a href="{{ route('admin.student-violations.edit', $studentViolation) }}" class="btn btn-warning">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                    <form action="{{ route('admin.student-violations.destroy', $studentViolation) }}" 
                                          method="POST" class="d-inline"
                                          onsubmit="return confirm('Yakin ingin menghapus data pelanggaran ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger">
                                            <i class="fas fa-trash"></i> Hapus
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection