@extends('layouts.admin')

@section('title', 'Siswa')
@section('header', 'Data Siswa')

@section('content')
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2 py-3">
        <h5 class="card-title mb-0">Daftar Siswa</h5>
        <a href="{{ route('admin.students.create') }}" class="btn btn-primary btn-sm">
            + Tambah Siswa
        </a>
    </div>
    
    <!-- Search & Filter -->
    <div class="card-body bg-light border-bottom">
        <form action="{{ route('admin.students.index') }}" method="GET">
            <div class="row g-2">
                <div class="col-12 col-md-5">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama atau NIS..." class="form-control form-control">
                </div>
                <div class="col-12 col-md-4">
                    <select name="class_id" class="form-select form-select">
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
                        <a href="{{ route('admin.students.index') }}" class="btn btn-outline-secondary flex-grow-1">Reset</a>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <div class="card-body p-0 p-md-3">
        <!-- Desktop Table -->
        <div class="table-responsive d-none d-md-block">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>No</th>
                        <th>Kode Siswa</th>
                        <th>Nama</th>
                        <th>Kelas</th>
                        <th>Telepon</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($students ?? [] as $index => $student)
                    <tr>
                        <td>{{ $students->firstItem() + $index }}</td>
                        <td>{{ $student->student_code }}</td>
                        <td>{{ $student->user->name ?? '-' }}</td>
                        <td>{{ $student->class->name ?? '-' }}</td>
                        <td>{{ $student->phone ?? '-' }}</td>
                        <td>
                            @if($student->status === 'active')
                                <span class="badge bg-success">Aktif</span>
                            @else
                                <span class="badge bg-danger">Tidak Aktif</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('admin.students.edit', $student) }}" class="btn btn-outline-primary btn-sm">Edit</a>
                                <form action="{{ route('admin.students.destroy', $student) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin hapus siswa ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger btn-sm">Hapus</button>
                                </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">Belum ada data siswa</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Mobile Cards -->
        <div class="d-md-none">
            @forelse($students ?? [] as $index => $student)
            <div class="border-bottom p-3">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div>
                        <h6 class="mb-1">{{ $student->user->name ?? '-' }}</h6>
                        <small class="text-muted">{{ $student->student_code }}</small>
                    </div>
                    @if($student->status === 'active')
                        <span class="badge bg-success">Aktif</span>
                    @else
                        <span class="badge bg-danger">Tidak Aktif</span>
                    @endif
                </div>
                <div class="row g-2 small text-muted mb-2">
                    <div class="col-6">
                        <strong>Kelas:</strong> {{ $student->class->name ?? '-' }}
                    </div>
                    <div class="col-6">
                        <strong>Telepon:</strong> {{ $student->phone ?? '-' }}
                    </div>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.students.edit', $student) }}" class="btn btn-outline-primary btn-sm">Edit</a>
                    <form action="{{ route('admin.students.destroy', $student) }}" method="POST" onsubmit="return confirm('Yakin hapus siswa ini?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger btn-sm">Hapus</button>
                    </form>
                </div>
            </div>
            @empty
            <div class="text-center text-muted py-4">Belum ada data siswa</div>
            @endforelse
        </div>

        @if($students instanceof \Illuminate\Pagination\LengthAwarePaginator && $students->hasPages())
        <div class="p-3">
            {{ $students->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
