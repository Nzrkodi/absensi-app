@extends('layouts.admin')

@section('title', 'Jenis Pelanggaran')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">Daftar Jenis Pelanggaran</h3>
                    <a href="{{ route('admin.violation-types.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Tambah Jenis Pelanggaran
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

                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>No</th>
                                    <th>Nama Pelanggaran</th>
                                    <th>Kategori</th>
                                    <th>Poin</th>
                                    <th>Status</th>
                                    <th>Deskripsi</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($violationTypes as $index => $violationType)
                                    <tr>
                                        <td>{{ $violationTypes->firstItem() + $index }}</td>
                                        <td>
                                            <strong>{{ $violationType->name }}</strong>
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ $violationType->badge_color }}">
                                                {{ ucfirst($violationType->category) }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-info">{{ $violationType->points }} poin</span>
                                        </td>
                                        <td>
                                            <form action="{{ route('admin.violation-types.toggle-status', $violationType) }}" 
                                                  method="POST" class="d-inline">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" 
                                                        class="btn btn-sm btn-{{ $violationType->status === 'active' ? 'success' : 'secondary' }}">
                                                    {{ $violationType->status === 'active' ? 'Aktif' : 'Nonaktif' }}
                                                </button>
                                            </form>
                                        </td>
                                        <td>
                                            <small class="text-muted">
                                                {{ Str::limit($violationType->description, 50) }}
                                            </small>
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('admin.violation-types.edit', $violationType) }}" 
                                                   class="btn btn-sm btn-warning">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <form action="{{ route('admin.violation-types.destroy', $violationType) }}" 
                                                      method="POST" class="d-inline"
                                                      onsubmit="return confirm('Yakin ingin menghapus jenis pelanggaran ini?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center">
                                            <div class="py-4">
                                                <i class="fas fa-exclamation-triangle fa-3x text-muted mb-3"></i>
                                                <p class="text-muted">Belum ada jenis pelanggaran yang ditambahkan.</p>
                                                <a href="{{ route('admin.violation-types.create') }}" class="btn btn-primary">
                                                    Tambah Jenis Pelanggaran Pertama
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if($violationTypes->hasPages())
                        <div class="d-flex justify-content-center">
                            {{ $violationTypes->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection