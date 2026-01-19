@extends('layouts.mobile')

@section('title', 'Login Siswa')

@section('content')
<div class="container-fluid px-3 py-4">
    <!-- Header -->
    <div class="row mb-5">
        <div class="col-12 text-center">
            <div class="mb-4">
                <i class="fas fa-graduation-cap fa-4x text-primary mb-3"></i>
                <h2 class="fw-bold text-primary">Absensi Siswa</h2>
                <p class="text-muted">Masuk dengan NISN Anda</p>
            </div>
        </div>
    </div>

    <!-- Login Form -->
    <div class="row justify-content-center">
        <div class="col-12">
            <div class="card border-0 shadow-lg">
                <div class="card-body p-4">
                    @if(session('error'))
                        <div class="alert alert-danger border-0 mb-4">
                            <i class="fas fa-exclamation-circle me-2"></i>
                            {{ session('error') }}
                        </div>
                    @endif

                    <form action="{{ route('student.login.submit') }}" method="POST" id="loginForm">
                        @csrf
                        
                        <div class="mb-4">
                            <label for="nisn" class="form-label fw-semibold">
                                <i class="fas fa-id-card me-2 text-primary"></i>
                                NISN
                            </label>
                            <input type="text" 
                                   class="form-control form-control-lg @error('nisn') is-invalid @enderror" 
                                   id="nisn" 
                                   name="nisn" 
                                   value="{{ old('nisn') }}"
                                   placeholder="Masukkan NISN Anda"
                                   required
                                   autocomplete="username">
                            @error('nisn')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">
                                <i class="fas fa-info-circle me-1"></i>
                                Masukkan 10 digit NISN Anda
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="name" class="form-label fw-semibold">
                                <i class="fas fa-user me-2 text-primary"></i>
                                Nama Lengkap
                            </label>
                            <input type="text" 
                                   class="form-control form-control-lg @error('name') is-invalid @enderror" 
                                   id="name" 
                                   name="name" 
                                   value="{{ old('name') }}"
                                   placeholder="Masukkan nama lengkap Anda"
                                   required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">
                                <i class="fas fa-info-circle me-1"></i>
                                Nama harus sesuai dengan data di sekolah
                            </div>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg" id="loginBtn">
                                <i class="fas fa-sign-in-alt me-2"></i>
                                Masuk ke Absensi
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Info Card -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card border-0 bg-light">
                <div class="card-body text-center py-3">
                    <h6 class="text-primary mb-2">
                        <i class="fas fa-question-circle me-2"></i>
                        Butuh Bantuan?
                    </h6>
                    <small class="text-muted">
                        Jika Anda lupa NISN atau mengalami masalah login, 
                        hubungi guru atau staff administrasi sekolah.
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Loading Modal -->
<div class="modal fade" id="loadingModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content">
            <div class="modal-body text-center py-4">
                <div class="spinner-border text-primary mb-3" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mb-0">Memverifikasi data...</p>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('loginForm');
    const loginBtn = document.getElementById('loginBtn');
    const loadingModal = new bootstrap.Modal(document.getElementById('loadingModal'));
    
    form.addEventListener('submit', function(e) {
        // Show loading
        loginBtn.disabled = true;
        loginBtn.innerHTML = '<div class="spinner-border spinner-border-sm me-2" role="status"></div>Memverifikasi...';
        loadingModal.show();
        
        // Let form submit normally
        // Loading will be hidden by page redirect or error display
    });
    
    // Auto-format NISN input (numbers only, max 10 digits)
    const nisnInput = document.getElementById('nisn');
    nisnInput.addEventListener('input', function(e) {
        // Remove non-numeric characters
        let value = e.target.value.replace(/\D/g, '');
        
        // Limit to 10 digits
        if (value.length > 10) {
            value = value.substring(0, 10);
        }
        
        e.target.value = value;
    });
    
    // Auto-capitalize name input
    const nameInput = document.getElementById('name');
    nameInput.addEventListener('input', function(e) {
        // Capitalize first letter of each word
        e.target.value = e.target.value.replace(/\b\w/g, l => l.toUpperCase());
    });
});
</script>
@endpush