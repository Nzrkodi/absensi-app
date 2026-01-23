class AttendanceMobile {
    constructor() {
        this.currentPosition = null;
        this.photoBlob = null;
        this.schoolLocations = []; // Will be loaded from API
        this.validLocation = null;
        this.faceCamera = null; // Face detection camera instance
        
        this.loadSchoolLocations();
        this.initializeGeolocation();
    }

    // Load school locations from API
    async loadSchoolLocations() {
        try {
            console.log('Loading school locations...');
            const response = await fetch('/api/school-locations/active');
            const result = await response.json();
            
            console.log('School locations response:', result);
            
            if (result.success) {
                this.schoolLocations = result.locations;
                console.log('Loaded school locations:', this.schoolLocations);
            } else {
                console.error('Failed to load school locations:', result);
            }
        } catch (error) {
            console.error('Failed to load school locations:', error);
        }
    }

    // Ambil lokasi GPS
    initializeGeolocation() {
        if (navigator.geolocation) {
            // Update location status
            const locationStatus = document.getElementById('locationStatus');
            if (locationStatus) {
                locationStatus.innerHTML = `
                    <div class="spinner-border spinner-border-sm me-2" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <span class="text-muted">Mengecek lokasi...</span>
                `;
            }
            
            navigator.geolocation.getCurrentPosition(
                (position) => {
                    this.currentPosition = {
                        latitude: position.coords.latitude,
                        longitude: position.coords.longitude
                    };
                    console.log('Current position:', this.currentPosition);
                    this.validateLocation();
                    
                    // Refresh location every 30 seconds
                    setInterval(() => {
                        this.refreshLocation();
                    }, 30000);
                },
                (error) => {
                    console.error('Geolocation error:', error);
                    this.showLocationError(error);
                },
                {
                    enableHighAccuracy: true,
                    timeout: 10000,
                    maximumAge: 0
                }
            );
        } else {
            this.showLocationError({ code: 0, message: 'Browser tidak mendukung GPS' });
        }
    }
    
    // Refresh location
    refreshLocation() {
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(
                (position) => {
                    this.currentPosition = {
                        latitude: position.coords.latitude,
                        longitude: position.coords.longitude
                    };
                    this.validateLocation();
                },
                (error) => {
                    console.error('Location refresh error:', error);
                },
                {
                    enableHighAccuracy: true,
                    timeout: 5000,
                    maximumAge: 0
                }
            );
        }
    }

    // Validasi jarak dari sekolah
    async validateLocation() {
        if (!this.currentPosition) {
            console.log('No current position available');
            return false;
        }
        
        console.log('Validating location:', this.currentPosition);
        
        try {
            const response = await fetch('/api/school-locations/validate', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    latitude: this.currentPosition.latitude,
                    longitude: this.currentPosition.longitude
                })
            });
            
            const result = await response.json();
            console.log('Validation result:', result);
            
            // Update UI
            const locationStatus = document.getElementById('locationStatus');
            if (locationStatus) {
                if (result.valid) {
                    locationStatus.innerHTML = `
                        <i class="fas fa-check-circle text-success me-2"></i>
                        <span class="text-success">Lokasi valid - ${result.location.name} (${result.distance}m)</span>
                    `;
                    this.validLocation = result.location;
                } else {
                    const nearest = result.nearest_location;
                    locationStatus.innerHTML = `
                        <i class="fas fa-exclamation-triangle text-danger me-2"></i>
                        <span class="text-danger">Lokasi terlalu jauh${nearest ? ` - ${nearest.distance}m dari ${nearest.name}` : ''}</span>
                    `;
                    this.validLocation = null;
                }
            }
            
            return result.valid;
        } catch (error) {
            console.error('Location validation error:', error);
            const locationStatus = document.getElementById('locationStatus');
            if (locationStatus) {
                locationStatus.innerHTML = `
                    <i class="fas fa-exclamation-circle text-warning me-2"></i>
                    <span class="text-warning">Gagal memvalidasi lokasi</span>
                `;
            }
            return false;
        }
    }

    // Hitung jarak menggunakan Haversine formula
    calculateDistance(lat1, lon1, lat2, lon2) {
        const R = 6371e3; // Earth's radius in meters
        const φ1 = lat1 * Math.PI/180;
        const φ2 = lat2 * Math.PI/180;
        const Δφ = (lat2-lat1) * Math.PI/180;
        const Δλ = (lon2-lon1) * Math.PI/180;

        const a = Math.sin(Δφ/2) * Math.sin(Δφ/2) +
                Math.cos(φ1) * Math.cos(φ2) *
                Math.sin(Δλ/2) * Math.sin(Δλ/2);
        const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));

        return R * c;
    }

    // Ambil foto dari kamera dengan face detection
    async capturePhoto() {
        try {
            // Initialize face detection camera if not already done
            if (!this.faceCamera) {
                this.faceCamera = new FaceDetectionCamera();
            }

            // Show enhanced camera modal with face detection
            this.showEnhancedCameraModal();
            
        } catch (error) {
            console.error('Camera error:', error);
            alert('Tidak dapat mengakses kamera. Pastikan izin kamera sudah diberikan.');
        }
    }

    // Tampilkan modal kamera dengan face detection
    showEnhancedCameraModal() {
        const modal = document.createElement('div');
        modal.className = 'modal fade show';
        modal.style.display = 'block';
        modal.style.zIndex = '9999';
        modal.innerHTML = `
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title">
                            <i class="fas fa-camera me-2"></i>
                            Ambil Foto Absensi
                        </h5>
                        <button type="button" class="btn-close btn-close-white" onclick="attendanceMobile.closeCameraModal()"></button>
                    </div>
                    <div class="modal-body p-0">
                        <!-- Camera Container -->
                        <div id="cameraContainer" class="position-relative">
                            <video id="cameraVideo" class="w-100" autoplay muted playsinline style="display: none;"></video>
                            <canvas id="cameraCanvas" class="w-100"></canvas>
                            
                            <!-- Face Detection Overlay -->
                            <div class="position-absolute top-0 start-0 w-100 h-100 d-flex flex-column justify-content-between p-3" style="pointer-events: none;">
                                <!-- Top Instructions -->
                                <div class="text-center">
                                    <div class="bg-dark bg-opacity-75 text-white px-3 py-2 rounded">
                                        <small>Posisikan wajah di dalam lingkaran</small>
                                    </div>
                                </div>
                                
                                <!-- Bottom Status -->
                                <div class="text-center">
                                    <div id="faceGuidance" class="face-guidance info bg-dark bg-opacity-75 text-white px-3 py-2 rounded">
                                        Memulai deteksi wajah...
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Photo Preview (hidden initially) -->
                        <div id="photoPreview" style="display: none;" class="text-center p-3">
                            <img id="capturedPhoto" class="img-fluid rounded shadow">
                        </div>
                        
                        <!-- Loading State -->
                        <div id="cameraLoading" class="text-center p-5">
                            <div class="spinner-border text-primary mb-3" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <p class="text-muted">Memulai kamera...</p>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" onclick="attendanceMobile.closeCameraModal()">
                            <i class="fas fa-times me-2"></i>Batal
                        </button>
                        <button type="button" id="captureBtn" class="btn btn-primary" disabled>
                            <i class="fas fa-camera me-2"></i>Ambil Foto
                        </button>
                        <button type="button" id="retakeBtn" class="btn btn-warning" style="display: none;">
                            <i class="fas fa-redo me-2"></i>Ambil Ulang
                        </button>
                        <button type="button" id="usePhotoBtn" class="btn btn-success" style="display: none;">
                            <i class="fas fa-check me-2"></i>Gunakan Foto
                        </button>
                    </div>
                </div>
            </div>
        `;
        
        document.body.appendChild(modal);
        this.currentModal = modal;
        
        // Initialize camera
        this.initializeEnhancedCamera(modal);
    }

    async initializeEnhancedCamera(modal) {
        try {
            const video = modal.querySelector('#cameraVideo');
            const canvas = modal.querySelector('#cameraCanvas');
            const loading = modal.querySelector('#cameraLoading');
            const cameraContainer = modal.querySelector('#cameraContainer');
            
            // Start camera with face detection
            await this.faceCamera.startCamera(video, canvas);
            
            // Hide loading, show camera
            loading.style.display = 'none';
            cameraContainer.style.display = 'block';
            
            // Setup event listeners
            this.setupCameraEventListeners(modal);
            
            // Monitor face detection status
            this.monitorFaceDetection(modal);
            
        } catch (error) {
            console.error('Enhanced camera initialization error:', error);
            
            // Fallback to basic camera
            this.showBasicCameraFallback(modal);
        }
    }

    setupCameraEventListeners(modal) {
        const captureBtn = modal.querySelector('#captureBtn');
        const retakeBtn = modal.querySelector('#retakeBtn');
        const usePhotoBtn = modal.querySelector('#usePhotoBtn');
        
        captureBtn.onclick = () => this.takeEnhancedPhoto(modal);
        retakeBtn.onclick = () => this.retakeEnhancedPhoto(modal);
        usePhotoBtn.onclick = () => this.useEnhancedPhoto(modal);
    }

    monitorFaceDetection(modal) {
        const captureBtn = modal.querySelector('#captureBtn');
        
        const checkFaceDetection = () => {
            if (!this.faceCamera) return;
            
            const faceDetected = this.faceCamera.isFaceDetected();
            
            if (faceDetected) {
                captureBtn.disabled = false;
                captureBtn.innerHTML = '<i class="fas fa-camera me-2"></i>Ambil Foto';
                captureBtn.className = 'btn btn-success';
            } else {
                captureBtn.disabled = true;
                captureBtn.innerHTML = '<i class="fas fa-camera me-2"></i>Posisikan Wajah';
                captureBtn.className = 'btn btn-primary';
            }
            
            // Continue monitoring if modal is still open
            if (document.body.contains(modal)) {
                setTimeout(checkFaceDetection, 100);
            }
        };
        
        // Start monitoring after a short delay
        setTimeout(checkFaceDetection, 1000);
    }

    async takeEnhancedPhoto(modal) {
        try {
            if (!this.faceCamera.isFaceDetected()) {
                alert('Pastikan wajah terdeteksi dengan baik sebelum mengambil foto');
                return;
            }
            
            // Capture photo with high quality
            this.photoBlob = await this.faceCamera.capturePhoto();
            
            // Show preview
            const preview = modal.querySelector('#photoPreview');
            const img = modal.querySelector('#capturedPhoto');
            img.src = URL.createObjectURL(this.photoBlob);
            
            modal.querySelector('#cameraContainer').style.display = 'none';
            preview.style.display = 'block';
            
            // Update buttons
            modal.querySelector('#captureBtn').style.display = 'none';
            modal.querySelector('#retakeBtn').style.display = 'inline-block';
            modal.querySelector('#usePhotoBtn').style.display = 'inline-block';
            
        } catch (error) {
            console.error('Photo capture error:', error);
            alert('Gagal mengambil foto. Silakan coba lagi.');
        }
    }

    retakeEnhancedPhoto(modal) {
        // Show camera again
        modal.querySelector('#cameraContainer').style.display = 'block';
        modal.querySelector('#photoPreview').style.display = 'none';
        
        // Reset buttons
        modal.querySelector('#captureBtn').style.display = 'inline-block';
        modal.querySelector('#retakeBtn').style.display = 'none';
        modal.querySelector('#usePhotoBtn').style.display = 'none';
        
        // Restart face detection monitoring
        this.monitorFaceDetection(modal);
    }

    useEnhancedPhoto(modal) {
        // Update UI to show photo is captured
        const photoStatus = document.getElementById('photoStatus');
        if (photoStatus) {
            photoStatus.innerHTML = '<i class="fas fa-check-circle text-success me-2"></i>Foto wajah berhasil diambil';
        }
        
        this.closeCameraModal();
    }

    closeCameraModal() {
        if (this.faceCamera) {
            this.faceCamera.stopCamera();
        }
        
        if (this.currentModal) {
            this.currentModal.remove();
            this.currentModal = null;
        }
    }

    showBasicCameraFallback(modal) {
        // Fallback to basic camera without face detection
        modal.querySelector('#cameraLoading').innerHTML = `
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle me-2"></i>
                Face detection tidak tersedia. Menggunakan kamera standar.
            </div>
        `;
        
        // Initialize basic camera
        this.initializeBasicCamera(modal);
    }

    async initializeBasicCamera(modal) {
        try {
            const stream = await navigator.mediaDevices.getUserMedia({
                video: {
                    facingMode: 'user',
                    width: { ideal: 720, max: 1280 },
                    height: { ideal: 1280, max: 1920 }
                }
            });
            
            const video = modal.querySelector('#cameraVideo');
            video.srcObject = stream;
            video.style.display = 'block';
            
            modal.querySelector('#cameraCanvas').style.display = 'none';
            modal.querySelector('#cameraLoading').style.display = 'none';
            modal.querySelector('#cameraContainer').style.display = 'block';
            
            // Enable capture button for basic mode
            const captureBtn = modal.querySelector('#captureBtn');
            captureBtn.disabled = false;
            captureBtn.onclick = () => this.takeBasicPhoto(modal, video, stream);
            
        } catch (error) {
            console.error('Basic camera error:', error);
            modal.querySelector('#cameraLoading').innerHTML = `
                <div class="alert alert-danger">
                    <i class="fas fa-times-circle me-2"></i>
                    Tidak dapat mengakses kamera. Pastikan izin kamera sudah diberikan.
                </div>
            `;
        }
    }

    async takeBasicPhoto(modal, video, stream) {
        const canvas = document.createElement('canvas');
        const ctx = canvas.getContext('2d');
        
        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;
        
        ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
        
        canvas.toBlob((blob) => {
            this.photoBlob = blob;
            
            // Show preview
            const preview = modal.querySelector('#photoPreview');
            const img = modal.querySelector('#capturedPhoto');
            img.src = URL.createObjectURL(blob);
            
            modal.querySelector('#cameraContainer').style.display = 'none';
            preview.style.display = 'block';
            
            modal.querySelector('#captureBtn').style.display = 'none';
            modal.querySelector('#retakeBtn').style.display = 'inline-block';
            modal.querySelector('#usePhotoBtn').style.display = 'inline-block';
            
            // Setup retake for basic mode
            modal.querySelector('#retakeBtn').onclick = () => {
                modal.querySelector('#cameraContainer').style.display = 'block';
                modal.querySelector('#photoPreview').style.display = 'none';
                modal.querySelector('#captureBtn').style.display = 'inline-block';
                modal.querySelector('#retakeBtn').style.display = 'none';
                modal.querySelector('#usePhotoBtn').style.display = 'none';
            };
            
            modal.querySelector('#usePhotoBtn').onclick = () => {
                stream.getTracks().forEach(track => track.stop());
                this.useEnhancedPhoto(modal);
            };
            
        }, 'image/jpeg', 0.9);
    }

    // Helper methods
    showLocationError(error) {
        const locationStatus = document.getElementById('locationStatus');
        if (locationStatus) {
            let errorMessage = 'Tidak dapat mengakses lokasi';
            
            if (error) {
                switch(error.code) {
                    case error.PERMISSION_DENIED:
                        errorMessage = 'Izin lokasi ditolak. Aktifkan izin lokasi di pengaturan browser.';
                        break;
                    case error.POSITION_UNAVAILABLE:
                        errorMessage = 'Informasi lokasi tidak tersedia. Pastikan GPS aktif.';
                        break;
                    case error.TIMEOUT:
                        errorMessage = 'Waktu permintaan lokasi habis. Coba lagi.';
                        break;
                    default:
                        errorMessage = error.message || 'Tidak dapat mengakses lokasi';
                }
            }
            
            locationStatus.innerHTML = `
                <i class="fas fa-exclamation-triangle text-danger me-2"></i>
                <span class="text-danger">${errorMessage}</span>
            `;
        }
    }

    showSuccess(message) {
        // Implementasi toast notification
        alert('✅ ' + message);
    }

    showError(message) {
        // Implementasi toast notification
        alert('❌ ' + message);
    }
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    window.attendanceMobile = new AttendanceMobile();
});