@extends('layouts.admin')

@section('title', 'Management Lokasi Sekolah')
@section('header', 'Management Lokasi Sekolah')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1">Lokasi Absensi Sekolah</h4>
        <p class="text-muted mb-0">Kelola titik lokasi yang diizinkan untuk absensi siswa</p>
    </div>
    <a href="{{ route('admin.school-locations.create') }}" class="btn btn-primary">
        <i class="fas fa-plus me-2"></i>Tambah Lokasi
    </a>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>No</th>
                        <th>Nama Lokasi</th>
                        <th>Koordinat</th>
                        <th>Radius</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($locations as $index => $location)
                    <tr>
                        <td>{{ $locations->firstItem() + $index }}</td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="rounded-circle me-3" 
                                     style="width: 20px; height: 20px; background-color: {{ $location->color }};">
                                </div>
                                <div>
                                    <div class="fw-medium">{{ $location->name }}</div>
                                    @if($location->description)
                                    <small class="text-muted">{{ Str::limit($location->description, 50) }}</small>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td>
                            <small class="font-monospace">
                                {{ $location->latitude }}, {{ $location->longitude }}
                            </small>
                            <br>
                            <a href="https://maps.google.com/?q={{ $location->latitude }},{{ $location->longitude }}" 
                               target="_blank" class="text-decoration-none small">
                                <i class="fas fa-external-link-alt me-1"></i>Lihat di Maps
                            </a>
                        </td>
                        <td>
                            <span class="badge bg-info">{{ $location->radius_meters }}m</span>
                        </td>
                        <td>
                            @if($location->is_active)
                                <span class="badge bg-success">Aktif</span>
                            @else
                                <span class="badge bg-secondary">Nonaktif</span>
                            @endif
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('admin.school-locations.show', $location) }}" 
                                   class="btn btn-outline-info" title="Detail">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('admin.school-locations.edit', $location) }}" 
                                   class="btn btn-outline-warning" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('admin.school-locations.destroy', $location) }}" 
                                      method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger btn-sm btn-delete" 
                                            title="Hapus">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-4">
                            <div class="text-muted">
                                <i class="fas fa-map-marker-alt fa-3x mb-3 d-block"></i>
                                <p>Belum ada lokasi yang ditambahkan</p>
                                <a href="{{ route('admin.school-locations.create') }}" class="btn btn-primary">
                                    <i class="fas fa-plus me-2"></i>Tambah Lokasi Pertama
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    
    @if($locations->hasPages())
    <div class="card-footer bg-white">
        {{ $locations->links() }}
    </div>
    @endif
</div>

<!-- Info Card -->
<div class="row mt-4">
    <div class="col-md-8">
        <div class="card border-0 bg-light">
            <div class="card-body">
                <h6 class="card-title">
                    <i class="fas fa-info-circle text-info me-2"></i>Informasi Lokasi Absensi
                </h6>
                <ul class="mb-0 small">
                    <li>Siswa hanya bisa absen jika berada dalam radius lokasi yang aktif</li>
                    <li>Radius minimum 10 meter, maksimum 1000 meter</li>
                    <li>Bisa menambahkan multiple lokasi (gedung utama, lapangan, lab, dll)</li>
                    <li>Warna marker membantu membedakan lokasi di peta</li>
                    <li>Lokasi nonaktif tidak akan digunakan untuk validasi absensi</li>
                </ul>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 bg-primary text-white">
            <div class="card-body text-center">
                <h3 class="mb-1">{{ $locations->total() }}</h3>
                <p class="mb-0">Total Lokasi</p>
                <small class="opacity-75">
                    {{ \App\Models\SchoolLocation::where('is_active', true)->count() }} Aktif
                </small>
            </div>
        </div>
    </div>
</div>
@endsection