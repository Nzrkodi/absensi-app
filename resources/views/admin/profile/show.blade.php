@extends('layouts.admin')

@section('title', 'Profil Saya')
@section('header', 'Profil Saya')

@section('content')
<style>
    /* Responsive Avatar */
    .profile-avatar {
        width: 100px;
        height: 100px;
    }
    
    @media (max-width: 576px) {
        .profile-avatar {
            width: 80px;
            height: 80px;
        }
        .profile-avatar .fa-user {
            font-size: 2rem !important;
        }
    }
    
    /* Responsive Stats */
    .stats-value {
        font-size: 1.25rem;
        word-break: break-word;
    }
    
    @media (max-width: 576px) {
        .stats-value {
            font-size: 1rem;
        }
    }
    
    /* Activity icon responsive */
    .activity-icon {
        width: 40px;
        height: 40px;
        min-width: 40px;
    }
    
    @media (max-width: 576px) {
        .activity-icon {
            width: 35px;
            height: 35px;
            min-width: 35px;
        }
    }
</style>

<!-- Alert Messages -->
@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<div class="row g-3 g-lg-4">
    <!-- Profile Card -->
    <div class="col-12 col-md-6 col-lg-4 order-1 order-lg-1">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body text-center p-3 p-md-4">
                <div class="mb-3">
                    <div class="avatar-lg mx-auto mb-3">
                        @if($user->hasAvatar())
                            <img src="{{ $user->avatar_url }}" 
                                 alt="Avatar {{ $user->name }}" 
                                 class="rounded-circle profile-avatar" 
                                 style="object-fit: cover;">
                        @else
                            <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center profile-avatar mx-auto">
                                <i class="fas fa-user fa-3x text-white"></i>
                            </div>
                        @endif
                    </div>
                </div>
                <h5 class="mb-1 text-truncate">{{ $user->name }}</h5>
                <p class="text-muted mb-0">{{ $user->position ?? 'Guru' }}</p>
                @if($user->subject)
                    <small class="text-muted d-block text-truncate">{{ $user->subject }}</small>
                @endif
                
                <div class="d-grid gap-2 mt-3">
                    <a href="{{ route('admin.profile.edit') }}" class="btn btn-primary">
                        <i class="fas fa-edit me-2"></i>Edit Profil
                    </a>
                    
                    @if($user->hasAvatar())
                        <form action="{{ route('admin.profile.remove-avatar') }}" method="POST">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-outline-danger btn-sm w-100" 
                                    onclick="return confirm('Yakin ingin menghapus foto profil?')">
                                <i class="fas fa-trash me-2"></i>Hapus Foto
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
    
    <!-- Quick Stats -->
    <div class="col-12 col-md-6 col-lg-4 order-3 order-md-2 order-lg-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white py-3">
                <h6 class="card-title mb-0">
                    <i class="fas fa-chart-bar text-primary me-2"></i>
                    Statistik Akun
                </h6>
            </div>
            <div class="card-body">
                <div class="row text-center g-2">
                    <div class="col-6">
                        <div class="border-end h-100 d-flex flex-column justify-content-center py-2">
                            <h4 class="text-primary mb-1 stats-value text-capitalize">{{ $user->role }}</h4>
                            <small class="text-muted">Role</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="h-100 d-flex flex-column justify-content-center py-2">
                            <h4 class="text-success mb-1 stats-value">{{ $user->created_at->diffForHumans() }}</h4>
                            <small class="text-muted">Bergabung</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Profile Details -->
    <div class="col-12 col-lg-8 order-2 order-lg-2">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <h5 class="card-title mb-0 fs-6 fs-md-5">
                    <i class="fas fa-user-circle text-primary me-2"></i>
                    Informasi Profil
                </h5>
            </div>
            <div class="card-body p-3 p-md-4">
                <div class="row g-3">
                    <div class="col-12 col-sm-6">
                        <label class="form-label text-muted small">Nama Lengkap</label>
                        <div class="form-control-plaintext border rounded p-2 bg-light text-truncate">
                            {{ $user->name }}
                        </div>
                    </div>
                    
                    <div class="col-12 col-sm-6">
                        <label class="form-label text-muted small">Email</label>
                        <div class="form-control-plaintext border rounded p-2 bg-light text-truncate">
                            {{ $user->email }}
                        </div>
                    </div>
                    
                    <div class="col-12 col-sm-6">
                        <label class="form-label text-muted small">Jabatan</label>
                        <div class="form-control-plaintext border rounded p-2 bg-light">
                            {{ $user->position ?? '-' }}
                        </div>
                    </div>
                    
                    <div class="col-12 col-sm-6">
                        <label class="form-label text-muted small">Mata Pelajaran</label>
                        <div class="form-control-plaintext border rounded p-2 bg-light">
                            {{ $user->subject ?? '-' }}
                        </div>
                    </div>
                    
                    <div class="col-12 col-sm-6">
                        <label class="form-label text-muted small">Role</label>
                        <div class="form-control-plaintext border rounded p-2 bg-light">
                            <span class="badge bg-{{ $user->role === 'admin' ? 'danger' : 'primary' }}">
                                {{ ucfirst($user->role) }}
                            </span>
                        </div>
                    </div>
                    
                    <div class="col-12 col-sm-6">
                        <label class="form-label text-muted small">Bergabung Sejak</label>
                        <div class="form-control-plaintext border rounded p-2 bg-light">
                            {{ $user->created_at->format('d F Y') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Activity Log -->
        <div class="card border-0 shadow-sm mt-3 mt-lg-4">
            <div class="card-header bg-white py-3">
                <h6 class="card-title mb-0">
                    <i class="fas fa-history text-primary me-2"></i>
                    Aktivitas Terakhir
                </h6>
            </div>
            <div class="card-body p-3 p-md-4">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <div class="bg-success rounded-circle d-flex align-items-center justify-content-center activity-icon">
                            <i class="fas fa-sign-in-alt text-white"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3 overflow-hidden">
                        <h6 class="mb-1 fs-6">Login Terakhir</h6>
                        <p class="text-muted mb-0 small text-truncate">{{ now()->format('d F Y, H:i') }} WIB</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection