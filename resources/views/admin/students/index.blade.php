@extends('layouts.admin')

@section('title', 'Siswa')
@section('header', 'Data Siswa')

@section('content')
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
        <h5 class="card-title mb-0">Daftar Siswa</h5>
        <a href="{{ route('admin.students.create') }}" class="btn btn-primary">
            + Tambah Siswa
        </a>
    </div>
    
    <!-- Search & Filter -->
    <div class="card-body bg-light border-bottom">
        <form action="{{ route('admin.students.index') }}" method="GET" class="row g-3">
            <div class="col-md-5">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama atau NIS..." class="form-control">
            </div>
            <div class="col-md-4">
                <select name="class_id" class="form-select">
                    <option value="">Semua Kelas</option>
                    @foreach($classes ?? [] as $class)
                        <option value="{{ $class->id }}" {{ request('class_id') == $class->id ? 'selected' : '' }}>
                            {{ $class->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-secondary">Cari</button>
                <a href="{{ route('admin.students.index') }}" class="btn btn-outline-secondary">Reset</a>
            </div>
        </form>
    </div>

    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
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
                            <a href="{{ route('admin.students.edit', $student) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                            <form action="{{ route('admin.students.destroy', $student) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin hapus siswa ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger">Hapus</button>
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

        @if($students instanceof \Illuminate\Pagination\LengthAwarePaginator && $students->hasPages())
        <div class="mt-3">
            {{ $students->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
