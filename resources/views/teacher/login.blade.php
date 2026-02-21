@extends('layouts.mobile')

@section('title', 'Login Guru')

@section('content')
<div class="container-fluid px-3 py-4">
    <!-- Header -->
    <div class="row mb-5">
        <div class="col-12 text-center">
            <div class="mb-4">
                <i class="fas fa-chalkboard-teacher fa-4x text-success mb-3"></i>
                <h2 class="fw-bold text-success">Absensi Guru</h2>
                <p class="text-muted">Masuk dengan Email Anda</p>
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

                    @if(session('success'))
                        <div class="alert alert-success border-0 mb-4">
                            <i class="fas fa-check-circle me-2"></i>
                            {{ session('success') }}
                        </div>
                    @endif

                    <form action="{{ route('teacher.login.submit') }}" method="POST" id="loginForm">
                        @csrf
                        
                        <div class="mb-4">
                            <label for="email" class="form-label fw-semibold">
                                <i class="fas fa-envelope me-2 text-success"></i>
                                Email
                            </label>
                            <input type="email" 
                                   class="form-control form-control-lg @error('email') is-invalid @enderror" 
                                   id="email" 
                                   name="email" 
                                   value="{{ old('email') }}"
                                   placeholder="Masukkan email Anda"
                                   required
                                   autocomplete="username">
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">
                                <i class="fas fa-info-circle me-1"></i>
                                Masukkan email yang terdaftar
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="name" class="form-label fw-semibold">
                                <i class="fas fa-user me-2 text-success"></i>
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
                            <button type="submit" class="btn btn-success btn-lg" id="loginBtn">
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
                    <h6 class="text-success mb-2">
                        <i class="fas fa-question-circle me-2"></i>
                        Butuh Bantuan?
                    </h6>
                    <small class="text-muted">
                        Jika Anda lupa email atau mengalami masalah login, 
                        hubungi administrator sekolah.
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.card {
    border-radius: 1rem;
}

.btn-lg {
    padding: 0.875rem 1.5rem;
    border-radius: 0.75rem;
    font-weight: 600;
}

.form-control-lg {
    border-radius: 0.75rem;
    padding: 0.875rem 1.25rem;
}

.form-control:focus {
    border-color: #28a745;
    box-shadow: 0 0 0 0.2rem rgba(40, 167, 69, 0.25);
}

.alert {
    border-radius: 0.75rem;
}
</style>
@endsection
