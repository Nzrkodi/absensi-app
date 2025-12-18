@extends('layouts.admin')

@section('title', 'Profil Saya')
@section('header', 'Profil Saya')

@section('content')
<!-- Alert Messages -->
@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<div class="row">
    <!-- Profile Card -->
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <div class="mb-3">
                    <div class="avatar-lg mx-auto mb-3">
                        <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 100px; height: 100px;">
                            <i class="fas fa-user fa-3x text-white"></i>
                        </div>
                    </div>
                    <h5 class="mb-1">{{ $user->name }}</h5>
                    <p class="text-muted mb-0">{{ $user->position ?? 'Guru' }}</p>
                    @if($user->subject)
                        <small class="text-muted">{{ $user->subject }}</small>
                    @endif
                </div>
                
                <div class="d-grid gap-2">
                    <a href="{{ route('admin.profile.edit') }}" class="btn btn-primary">
                        <i class="fas fa-edit me-2"></i>Edit Profil
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Quick Stats -->
        <div class="card border-0 shadow-sm mt-4">
            <div class="card-header bg-white">
                <h6 class="card-title mb-0">
                    <i class="fas fa-chart-bar text-primary me-2"></i>
                    Statistik Akun
                </h6>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-6">
                        <div class="border-end">
                            <h4 class="text-primary mb-1">{{ $user->role }}</h4>
                            <small class="text-muted">Role</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <h4 class="text-success mb-1">{{ $user->created_at->diffForHumans() }}</h4>
                        <small class="text-muted">Bergabung</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Profile Details -->
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h5 class="card-title mb-0">
                    <i class="fas fa-user-circle text-primary me-2"></i>
                    Informasi Profil
                </h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label text-muted">Nama Lengkap</label>
                        <div class="form-control-plaintext border rounded p-2 bg-light">
                            {{ $user->name }}
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <label class="form-label text-muted">Email</label>
                        <div class="form-control-plaintext border rounded p-2 bg-light">
                            {{ $user->email }}
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <label class="form-label text-muted">Jabatan</label>
                        <div class="form-control-plaintext border rounded p-2 bg-light">
                            {{ $user->position ?? '-' }}
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <label class="form-label text-muted">Mata Pelajaran</label>
                        <div class="form-control-plaintext border rounded p-2 bg-light">
                            {{ $user->subject ?? '-' }}
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <label class="form-label text-muted">Role</label>
                        <div class="form-control-plaintext border rounded p-2 bg-light">
                            <span class="badge bg-{{ $user->role === 'admin' ? 'danger' : 'primary' }}">
                                {{ ucfirst($user->role) }}
                            </span>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <label class="form-label text-muted">Bergabung Sejak</label>
                        <div class="form-control-plaintext border rounded p-2 bg-light">
                            {{ $user->created_at->format('d F Y') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Activity Log (Optional) -->
        <div class="card border-0 shadow-sm mt-4">
            <div class="card-header bg-white">
                <h6 class="card-title mb-0">
                    <i class="fas fa-history text-primary me-2"></i>
                    Aktivitas Terakhir
                </h6>
            </div>
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <div class="bg-success rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                            <i class="fas fa-sign-in-alt text-white"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h6 class="mb-1">Login Terakhir</h6>
                        <p class="text-muted mb-0">{{ now()->format('d F Y, H:i') }} WIB</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection