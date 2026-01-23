@extends('layouts.mobile')

@section('title', 'Absensi Mobile')

@section('content')
<div class="container-fluid px-3 py-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm bg-primary text-white">
                <div class="card-body text-center py-4">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div></div>
                        <div class="text-center flex-grow-1">
                            <h4 class="mb-1">
                                <i class="fas fa-user-clock me-2"></i>
                                Absensi Mobile
                            </h4>
                        </div>
                        <div>
                            <a href="{{ route('student.logout') }}" 
                               class="btn btn-outline-light btn-sm"
                               onclick="return confirm('Yakin ingin keluar?')">
                                <i class="fas fa-sign-out-alt"></i>
                            </a>
                        </div>
                    </div>
                    <p class="mb-0 opacity-75">{{ $date->format('l, d F Y') }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Student Info -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="bg-light rounded-circle p-3 me-3">
                            <i class="fas fa-user fa-2x text-primary"></i>
                        </div>
                        <div>
                            <h5 class="mb-1">{{ $student->name }}</h5>
                            <p class="text-muted mb-0">
                                <small>NISN: {{ $student->nisn }}</small><br>
                                <small>Kelas: {{ $student->kelas ?? 'Tidak ada kelas' }}</small>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if($isHoliday)
    <!-- Holiday Notice -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="alert alert-info border-0 shadow-sm">
                <div class="d-flex align-items-center">
                    <i class="fas fa-calendar-times fa-2x me-3"></i>
                    <div>
                        <h6 class="mb-1">Hari Libur</h6>
                        <small>Hari ini adalah hari libur. Absensi tidak diperlukan.</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Status Cards -->
    <div class="row mb-4">
        <div class="col-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    @if($attendance && $attendance->clock_in)
                        <i class="fas fa-check-circle fa-3x text-success mb-2"></i>
                        <h6 class="text-success mb-1">Masuk</h6>
                        <small class="text-muted">{{ \Carbon\Carbon::parse($attendance->clock_in)->format('H:i') }}</small>
                        @if($attendance->status === 'late')
                            <br><span class="badge bg-warning text-dark mt-1">Terlambat</span>
                        @else
                            <br><span class="badge bg-success mt-1">Tepat Waktu</span>
                        @endif
                    @else
                        <i class="fas fa-clock fa-3x text-muted mb-2"></i>
                        <h6 class="text-muted mb-1">Belum Masuk</h6>
                        <small class="text-muted">--:--</small>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    @if($attendance && $attendance->clock_out)
                        <i class="fas fa-check-circle fa-3x text-success mb-2"></i>
                        <h6 class="text-success mb-1">Pulang</h6>
                        <small class="text-muted">{{ \Carbon\Carbon::parse($attendance->clock_out)->format('H:i') }}</small>
                    @else
                        <i class="fas fa-clock fa-3x text-muted mb-2"></i>
                        <h6 class="text-muted mb-1">Belum Pulang</h6>
                        <small class="text-muted">--:--</small>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Location & Photo Status -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="card-title mb-0">
                            <i class="fas fa-map-marker-alt me-2"></i>
                            Status Lokasi & Foto
                        </h6>
                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="attendanceMobile.refreshLocation()" title="Refresh lokasi">
                            <i class="fas fa-sync-alt"></i>
                        </button>
                    </div>
                    
                    <div class="mb-3">
                        <div id="locationStatus" class="d-flex align-items-center">
                            <div class="spinner-border spinner-border-sm me-2" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <span class="text-muted">Mengecek lokasi...</span>
                        </div>
                    </div>
                    
                    <div>
                        <div id="photoStatus" class="d-flex align-items-center">
                            <i class="fas fa-camera text-muted me-2"></i>
                            <span class="text-muted">Foto belum diambil</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @unless($isHoliday)
    <!-- Action Buttons -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h6 class="card-title mb-3">
                        <i class="fas fa-hand-paper me-2"></i>
                        Aksi Absensi
                    </h6>
                    
                    <!-- Take Photo Button -->
                    <button type="button" class="btn btn-outline-primary btn-lg w-100 mb-3" onclick="attendanceMobile.capturePhoto()">
                        <i class="fas fa-camera me-2"></i>
                        Ambil Foto Selfie
                    </button>
                    
                    <!-- Debug Button (for testing) -->
                    <button type="button" class="btn btn-outline-info btn-sm w-100 mb-3" onclick="attendanceMobile.debugCamera()" style="font-size: 0.8rem;">
                        <i class="fas fa-bug me-2"></i>
                        Test Kamera & Permission
                    </button>
                    
                    <div class="row g-2">
                        @if(!$attendance || !$attendance->clock_in)
                        <!-- Clock In Button -->
                        <div class="col-6">
                            <button type="button" class="btn btn-success btn-lg w-100" onclick="clockIn()" id="clockInBtn">
                                <i class="fas fa-sign-in-alt me-2"></i>
                                Masuk
                            </button>
                        </div>
                        @endif
                        
                        @if($attendance && $attendance->clock_in && !$attendance->clock_out)
                        <!-- Clock Out Button -->
                        <div class="col-6">
                            <button type="button" class="btn btn-danger btn-lg w-100" onclick="clockOut()" id="clockOutBtn">
                                <i class="fas fa-sign-out-alt me-2"></i>
                                Pulang
                            </button>
                        </div>
                        @endif
                        
                        @if($attendance && $attendance->clock_in && $attendance->clock_out)
                        <!-- Already Complete -->
                        <div class="col-12">
                            <div class="alert alert-success border-0 text-center mb-0">
                                <i class="fas fa-check-circle me-2"></i>
                                Absensi hari ini sudah lengkap
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endunless

    <!-- Settings Info -->
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h6 class="card-title mb-3">
                        <i class="fas fa-info-circle me-2"></i>
                        Informasi Jadwal
                    </h6>
                    
                    <div class="row text-center">
                        <div class="col-4">
                            <div class="border-end">
                                <small class="text-muted d-block">Jam Masuk</small>
                                <strong>{{ $settings['school_start_time'] ?? '07:00' }}</strong>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="border-end">
                                <small class="text-muted d-block">Jam Pulang</small>
                                <strong>{{ $settings['school_end_time'] ?? '15:00' }}</strong>
                            </div>
                        </div>
                        <div class="col-4">
                            <small class="text-muted d-block">Toleransi</small>
                            <strong>{{ $settings['late_tolerance_minutes'] ?? '15' }} menit</strong>
                        </div>
                    </div>
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
                <p class="mb-0">Memproses absensi...</p>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('js/face-detection.js') }}?v={{ time() }}"></script>
<script src="{{ asset('js/attendance-mobile.js') }}?v={{ time() }}"></script>

<style>
/* Face Detection Styles */
.face-guidance {
    transition: all 0.3s ease;
    font-weight: 500;
    border-radius: 20px !important;
}

.face-guidance.success {
    background-color: rgba(40, 167, 69, 0.9) !important;
    color: white !important;
}

.face-guidance.warning {
    background-color: rgba(255, 193, 7, 0.9) !important;
    color: #212529 !important;
}

.face-guidance.info {
    background-color: rgba(23, 162, 184, 0.9) !important;
    color: white !important;
}

.face-guidance.error {
    background-color: rgba(220, 53, 69, 0.9) !important;
    color: white !important;
}

/* Camera Modal Enhancements */
.modal-lg .modal-content {
    border-radius: 15px;
    overflow: hidden;
}

#cameraCanvas, #cameraVideo {
    border-radius: 10px;
    max-height: 70vh;
    object-fit: cover;
}

/* Mobile Responsive */
@media (max-width: 768px) {
    .modal-lg {
        margin: 0.5rem;
    }
    
    .modal-lg .modal-dialog {
        max-width: calc(100% - 1rem);
    }
    
    #cameraCanvas, #cameraVideo {
        max-height: 60vh;
    }
}

/* Loading Animation */
.spinner-border {
    animation: spinner-border 0.75s linear infinite;
}

@keyframes spinner-border {
    to {
        transform: rotate(360deg);
    }
}

/* Button Animations */
.btn {
    transition: all 0.2s ease;
}

.btn:hover {
    transform: translateY(-1px);
}

.btn:active {
    transform: translateY(0);
}
</style>
<script>
// Clock In Function
async function clockIn() {
    if (!attendanceMobile.photoBlob) {
        Swal.fire({
            icon: 'warning',
            title: 'Foto Diperlukan',
            text: 'Silakan ambil foto terlebih dahulu',
            confirmButtonText: 'OK'
        });
        return;
    }
    
    if (!attendanceMobile.currentPosition) {
        Swal.fire({
            icon: 'warning',
            title: 'Lokasi Diperlukan',
            text: 'Lokasi belum terdeteksi. Pastikan GPS aktif dan izin lokasi diberikan.',
            confirmButtonText: 'OK'
        });
        return;
    }
    
    // Validate location before proceeding
    const isValidLocation = await attendanceMobile.validateLocation();
    
    if (!isValidLocation) {
        const result = await Swal.fire({
            icon: 'warning',
            title: 'Lokasi Tidak Valid',
            text: 'Lokasi Anda terlalu jauh dari sekolah. Apakah Anda yakin ingin melanjutkan absensi?',
            showCancelButton: true,
            confirmButtonText: 'Ya, Lanjutkan',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6'
        });
        
        if (!result.isConfirmed) {
            return;
        }
    }
    
    // Show loading
    const loadingModal = new bootstrap.Modal(document.getElementById('loadingModal'));
    loadingModal.show();
    
    const formData = new FormData();
    formData.append('photo', attendanceMobile.photoBlob, 'clock_in.jpg');
    formData.append('latitude', attendanceMobile.currentPosition.latitude);
    formData.append('longitude', attendanceMobile.currentPosition.longitude);
    
    // Add face validation flag if photo has face validation data
    if (attendanceMobile.photoBlob && attendanceMobile.photoBlob.faceValidation) {
        formData.append('face_validation', 'true');
        console.log('Sending face validation data:', attendanceMobile.photoBlob.faceValidation);
    }
    
    try {
        const response = await fetch('{{ route("student.attendance.clock-in") }}', {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        });
        
        const result = await response.json();
        loadingModal.hide();
        
        if (result.success) {
            // Show success message
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: result.message,
                confirmButtonText: 'OK'
            }).then(() => {
                location.reload();
            });
        } else {
            // Check if need to redirect to login
            if (result.redirect) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Sesi Berakhir',
                    text: result.message,
                    confirmButtonText: 'Login Kembali'
                }).then(() => {
                    window.location.href = result.redirect;
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal!',
                    text: result.message,
                    confirmButtonText: 'OK'
                });
            }
        }
    } catch (error) {
        loadingModal.hide();
        console.error('Clock in error:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error!',
            text: 'Terjadi kesalahan saat memproses absensi',
            confirmButtonText: 'OK'
        });
    }
}

// Clock Out Function
async function clockOut() {
    if (!attendanceMobile.photoBlob) {
        alert('Silakan ambil foto terlebih dahulu');
        return;
    }
    
    if (!attendanceMobile.currentPosition) {
        alert('Lokasi belum terdeteksi. Pastikan GPS aktif dan izin lokasi diberikan.');
        return;
    }
    
    // Show loading
    const loadingModal = new bootstrap.Modal(document.getElementById('loadingModal'));
    loadingModal.show();
    
    const formData = new FormData();
    formData.append('photo', attendanceMobile.photoBlob, 'clock_out.jpg');
    formData.append('latitude', attendanceMobile.currentPosition.latitude);
    formData.append('longitude', attendanceMobile.currentPosition.longitude);
    
    // Add face validation flag if photo has face validation data
    if (attendanceMobile.photoBlob && attendanceMobile.photoBlob.faceValidation) {
        formData.append('face_validation', 'true');
        console.log('Sending face validation data for clock out:', attendanceMobile.photoBlob.faceValidation);
    }
    
    try {
        const response = await fetch('{{ route("student.attendance.clock-out") }}', {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        });
        
        const result = await response.json();
        loadingModal.hide();
        
        if (result.success) {
            // Show success message
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: result.message,
                confirmButtonText: 'OK'
            }).then(() => {
                location.reload();
            });
        } else {
            // Check if need to redirect to login
            if (result.redirect) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Sesi Berakhir',
                    text: result.message,
                    confirmButtonText: 'Login Kembali'
                }).then(() => {
                    window.location.href = result.redirect;
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal!',
                    text: result.message,
                    confirmButtonText: 'OK'
                });
            }
        }
    } catch (error) {
        loadingModal.hide();
        console.error('Clock out error:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error!',
            text: 'Terjadi kesalahan saat memproses absensi',
            confirmButtonText: 'OK'
        });
    }
}

// Initialize when page loads
document.addEventListener('DOMContentLoaded', function() {
    // Check if geolocation is supported
    if (!navigator.geolocation) {
        document.getElementById('locationStatus').innerHTML = 
            '<i class="fas fa-exclamation-triangle text-danger me-2"></i>Browser tidak mendukung GPS';
    }
    
    // Check if camera is supported
    if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
        alert('Browser tidak mendukung kamera. Gunakan browser yang lebih baru.');
    }
});
</script>
@endpush