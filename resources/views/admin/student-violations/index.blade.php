@extends('layouts.admin')

@section('title', 'Data Pelanggaran Siswa')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">Data Pelanggaran Siswa</h3>
                    <a href="{{ route('admin.student-violations.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Tambah Pelanggaran
                    </a>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <!-- Filter Form -->
                    <div class="card mb-3">
                        <div class="card-body">
                            <form method="GET" action="{{ route('admin.student-violations.index') }}">
                                <div class="row g-3">
                                    <div class="col-md-3">
                                        <label for="start_date" class="form-label">Tanggal Mulai</label>
                                        <input type="date" 
                                               class="form-control" 
                                               id="start_date" 
                                               name="start_date" 
                                               value="{{ request('start_date') }}">
                                    </div>
                                    <div class="col-md-3">
                                        <label for="end_date" class="form-label">Tanggal Akhir</label>
                                        <input type="date" 
                                               class="form-control" 
                                               id="end_date" 
                                               name="end_date" 
                                               value="{{ request('end_date') }}">
                                    </div>
                                    <div class="col-md-3">
                                        <label for="student_id" class="form-label">Siswa</label>
                                        <select class="form-select" id="student_id" name="student_id">
                                            <option value="">Semua Siswa</option>
                                            @foreach($students as $student)
                                                <option value="{{ $student->id }}" 
                                                        {{ request('student_id') == $student->id ? 'selected' : '' }}>
                                                    {{ $student->name }} ({{ $student->nisn }})
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label for="violation_type_id" class="form-label">Jenis Pelanggaran</label>
                                        <select class="form-select" id="violation_type_id" name="violation_type_id">
                                            <option value="">Semua Jenis</option>
                                            @foreach($violationTypes->groupBy('category') as $category => $types)
                                                <optgroup label="{{ ucfirst($category) }}">
                                                    @foreach($types as $type)
                                                        <option value="{{ $type->id }}" 
                                                                {{ request('violation_type_id') == $type->id ? 'selected' : '' }}>
                                                            {{ $type->name }}
                                                        </option>
                                                    @endforeach
                                                </optgroup>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="row g-3 mt-2">
                                    <div class="col-md-3">
                                        <label for="status" class="form-label">Status</label>
                                        <select class="form-select" id="status" name="status">
                                            <option value="">Semua Status</option>
                                            <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>
                                                Pending
                                            </option>
                                            <option value="confirmed" {{ request('status') === 'confirmed' ? 'selected' : '' }}>
                                                Dikonfirmasi
                                            </option>
                                            <option value="resolved" {{ request('status') === 'resolved' ? 'selected' : '' }}>
                                                Diselesaikan
                                            </option>
                                        </select>
                                    </div>
                                    <div class="col-md-9 d-flex align-items-end">
                                        <button type="submit" class="btn btn-primary me-2">
                                            <i class="fas fa-search"></i> Filter
                                        </button>
                                        <a href="{{ route('admin.student-violations.index') }}" class="btn btn-secondary">
                                            <i class="fas fa-refresh"></i> Reset
                                        </a>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>No</th>
                                    <th>Tanggal/Waktu</th>
                                    <th>Siswa</th>
                                    <th>Jenis Pelanggaran</th>
                                    <th>Lokasi</th>
                                    <th>Dilaporkan Oleh</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($violations as $index => $violation)
                                    <tr>
                                        <td>{{ $violations->firstItem() + $index }}</td>
                                        <td>
                                            <div>
                                                <strong>{{ $violation->violation_date->format('d/m/Y') }}</strong>
                                                @if($violation->violation_time)
                                                    <br><small class="text-muted">{{ $violation->violation_time->format('H:i') }}</small>
                                                @endif
                                            </div>
                                        </td>
                                        <td>
                                            <div>
                                                <strong>{{ $violation->student->name }}</strong>
                                                <br><small class="text-muted">{{ $violation->student->nisn }}</small>
                                                <br><small class="text-muted">Kelas {{ $violation->student->kelas }}</small>
                                            </div>
                                        </td>
                                        <td>
                                            <div>
                                                <strong>{{ $violation->violationType->name }}</strong>
                                                <br>
                                                <span class="badge bg-{{ $violation->violationType->badge_color }}">
                                                    {{ ucfirst($violation->violationType->category) }}
                                                </span>
                                                <span class="badge bg-info">{{ $violation->violationType->points }} poin</span>
                                            </div>
                                        </td>
                                        <td>
                                            <small class="text-muted">
                                                {{ $violation->location ?: '-' }}
                                            </small>
                                        </td>
                                        <td>
                                            <small class="text-muted">
                                                {{ $violation->reported_by ?: '-' }}
                                            </small>
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ $violation->status_badge_color }}">
                                                {{ ucfirst($violation->status) }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('admin.student-violations.show', $violation) }}" 
                                                   class="btn btn-sm btn-info" title="Detail">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="{{ route('admin.student-violations.edit', $violation) }}" 
                                                   class="btn btn-sm btn-warning" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <form action="{{ route('admin.student-violations.destroy', $violation) }}" 
                                                      method="POST" class="d-inline"
                                                      onsubmit="return confirm('Yakin ingin menghapus data pelanggaran ini?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger" title="Hapus">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center">
                                            <div class="py-4">
                                                <i class="fas fa-exclamation-triangle fa-3x text-muted mb-3"></i>
                                                <p class="text-muted">Belum ada data pelanggaran siswa.</p>
                                                <a href="{{ route('admin.student-violations.create') }}" class="btn btn-primary">
                                                    Tambah Data Pelanggaran Pertama
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if($violations->hasPages())
                        <div class="d-flex justify-content-center">
                            {{ $violations->appends(request()->query())->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection