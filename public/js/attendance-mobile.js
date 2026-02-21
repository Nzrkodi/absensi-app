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

    // Ambil lokasi GPS dengan timeout dan fallback
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
            
            // Set timeout for location - more aggressive
            const locationTimeout = setTimeout(() => {
                console.log('Location timeout after 10 seconds, using fallback');
                this.handleLocationTimeout();
            }, 10000); // Reduced to 10 seconds
            
            navigator.geolocation.getCurrentPosition(
                (position) => {
                    clearTimeout(locationTimeout);
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
                    clearTimeout(locationTimeout);
                    console.error('Geolocation error:', error);
                    this.showLocationError(error);
                },
                {
                    enableHighAccuracy: true,
                    timeout: 8000, // Reduced to 8 seconds
                    maximumAge: 30000 // Accept 30 second old location
                }
            );
        } else {
            this.showLocationError({ code: 0, message: 'Browser tidak mendukung GPS' });
        }
    }

    handleLocationTimeout() {
        console.log('Location detection timeout - allowing manual override');
        const locationStatus = document.getElementById('locationStatus');
        if (locationStatus) {
            locationStatus.innerHTML = `
                <i class="fas fa-exclamation-triangle text-warning me-2"></i>
                <span class="text-warning">GPS timeout - Lokasi akan divalidasi saat absen</span>
            `;
        }
        
        // Set a default position (will be validated during attendance)
        this.currentPosition = {
            latitude: 0,
            longitude: 0,
            isTimeout: true
        };
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

    // Validasi jarak dari sekolah dengan timeout
    async validateLocation() {
        if (!this.currentPosition) {
            console.log('No current position available');
            return false;
        }
        
        // Skip validation if position is from timeout
        if (this.currentPosition.isTimeout) {
            console.log('Skipping validation for timeout position');
            return false;
        }
        
        console.log('Validating location:', this.currentPosition);
        
        try {
            // Add timeout for API call
            const controller = new AbortController();
            const timeoutId = setTimeout(() => controller.abort(), 8000); // 8 second timeout
            
            const response = await fetch('/api/school-locations/validate', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    latitude: this.currentPosition.latitude,
                    longitude: this.currentPosition.longitude
                }),
                signal: controller.signal
            });
            
            clearTimeout(timeoutId);
            
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}`);
            }
            
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
                if (error.name === 'AbortError') {
                    locationStatus.innerHTML = `
                        <i class="fas fa-clock text-warning me-2"></i>
                        <span class="text-warning">Validasi lokasi timeout - Akan dicek saat absen</span>
                    `;
                } else {
                    locationStatus.innerHTML = `
                        <i class="fas fa-exclamation-circle text-warning me-2"></i>
                        <span class="text-warning">Gagal validasi lokasi - Akan dicek saat absen</span>
                    `;
                }
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

    // Ambil foto dari kamera dengan face detection WAJIB
    async capturePhoto() {
        try {
            console.log('Starting photo capture with MANDATORY face detection...');
            
            // Initialize face detection camera - WAJIB untuk absensi
            if (!this.faceCamera) {
                console.log('Creating new FaceDetectionCamera instance...');
                this.faceCamera = new FaceDetectionCamera();
                
                // Wait for initialization
                await new Promise(resolve => setTimeout(resolve, 1000));
            }

            // Show face detection camera modal (WAJIB)
            this.showMandatoryFaceDetectionModal();
            
        } catch (error) {
            console.error('Camera error:', error);
            
            // Show user-friendly error message
            if (error.name === 'NotAllowedError') {
                alert('Izin kamera ditolak. Silakan berikan izin kamera di pengaturan browser dan coba lagi.');
            } else if (error.name === 'NotFoundError') {
                alert('Kamera tidak ditemukan. Pastikan device Anda memiliki kamera.');
            } else {
                alert('Tidak dapat mengakses kamera. Pastikan izin kamera sudah diberikan dan tidak ada aplikasi lain yang menggunakan kamera.');
            }
        }
    }

    // Modal kamera dengan WAJIB face detection
    showMandatoryFaceDetectionModal() {
        const modal = document.createElement('div');
        modal.className = 'modal fade show';
        modal.style.display = 'block';
        modal.style.zIndex = '9999';
        modal.innerHTML = `
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title">
                            <i class="fas fa-user-check me-2"></i>
                            Verifikasi Wajah untuk Absensi
                        </h5>
                        <button type="button" class="btn-close btn-close-white" onclick="attendanceMobile.closeFaceDetectionModal()"></button>
                    </div>
                    <div class="modal-body p-0">
                        <!-- Loading State -->
                        <div id="faceLoading" class="text-center p-5">
                            <div class="spinner-border text-primary mb-3" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <p class="text-muted">Memulai deteksi wajah...</p>
                            <small class="text-info">Pastikan wajah Anda terlihat jelas di kamera</small>
                        </div>
                        
                        <!-- Face Detection Camera Container -->
                        <div id="faceDetectionContainer" style="display: none; position: relative;">
                            <video id="faceVideo" class="w-100" autoplay muted playsinline style="transform: scaleX(-1); display: none;"></video>
                            <canvas id="faceCanvas" class="w-100" style="border-radius: 10px;"></canvas>
                            
                            <!-- Face Detection Status Overlay -->
                            <div class="position-absolute top-0 start-0 end-0 p-3" style="z-index: 10;">
                                <div id="faceStatus" class="text-center">
                                    <div class="bg-dark bg-opacity-75 text-white px-3 py-2 rounded">
                                        <i class="fas fa-search me-2"></i>
                                        <span id="faceStatusText">Mencari wajah...</span>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Instructions Overlay -->
                            <div class="position-absolute bottom-0 start-0 end-0 p-3" style="z-index: 10;">
                                <div class="bg-dark bg-opacity-75 text-white px-3 py-2 rounded text-center">
                                    <small>
                                        <i class="fas fa-info-circle me-1"></i>
                                        Posisikan wajah di tengah lingkaran dan tunggu hingga terdeteksi
                                    </small>
                                </div>
                            </div>
                            
                            <!-- Mirror Toggle -->
                            <div class="position-absolute top-0 end-0 p-3" style="z-index: 10;">
                                <button type="button" class="btn btn-sm btn-outline-light" onclick="attendanceMobile.toggleFaceMirror()" title="Toggle Mirror Mode">
                                    <i class="fas fa-sync-alt"></i>
                                </button>
                            </div>
                        </div>
                        
                        <!-- Photo Preview -->
                        <div id="facePhotoPreview" style="display: none;" class="text-center p-3">
                            <img id="faceCapturedPhoto" class="img-fluid rounded shadow">
                            <div class="mt-3">
                                <div class="alert alert-success border-0">
                                    <i class="fas fa-check-circle me-2"></i>
                                    <strong>Wajah terdeteksi!</strong> Foto siap digunakan untuk absensi.
                                </div>
                            </div>
                        </div>
                        
                        <!-- Fallback Message -->
                        <div id="faceDetectionFailed" style="display: none;" class="text-center p-5">
                            <div class="alert alert-danger border-0">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <strong>Face Detection Diperlukan untuk Absensi</strong>
                                <p class="mb-3">Sistem deteksi wajah tidak dapat dimuat. Absensi memerlukan verifikasi wajah untuk keamanan.</p>
                                <div class="mb-3">
                                    <small class="text-muted">
                                        <strong>Solusi:</strong><br>
                                        • Pastikan koneksi internet stabil<br>
                                        • Refresh halaman dan coba lagi<br>
                                        • Gunakan browser Chrome/Firefox terbaru<br>
                                        • Hubungi admin jika masalah berlanjut
                                    </small>
                                </div>
                                <button class="btn btn-primary me-2" onclick="location.reload()">
                                    <i class="fas fa-redo me-2"></i>Refresh Halaman
                                </button>
                                <button class="btn btn-outline-secondary" onclick="attendanceMobile.retryFaceDetection()">
                                    <i class="fas fa-sync me-2"></i>Coba Lagi
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" onclick="attendanceMobile.closeFaceDetectionModal()">
                            <i class="fas fa-times me-2"></i>Batal
                        </button>
                        <button type="button" id="faceDetectionCaptureBtn" class="btn btn-primary" disabled>
                            <i class="fas fa-camera me-2"></i>
                            <span id="captureButtonText">Menunggu Wajah...</span>
                        </button>
                        <button type="button" id="faceRetakeBtn" class="btn btn-warning" style="display: none;">
                            <i class="fas fa-redo me-2"></i>Ambil Ulang
                        </button>
                        <button type="button" id="faceUseBtn" class="btn btn-success" style="display: none;">
                            <i class="fas fa-check me-2"></i>Gunakan Foto
                        </button>
                    </div>
                </div>
            </div>
        `;
        
        document.body.appendChild(modal);
        this.currentModal = modal;
        
        // Initialize face detection camera
        this.initializeMandatoryFaceDetection(modal);
    }

    async initializeMandatoryFaceDetection(modal) {
        console.log('Initializing MANDATORY face detection...');
        
        try {
            const video = modal.querySelector('#faceVideo');
            const canvas = modal.querySelector('#faceCanvas');
            const loading = modal.querySelector('#faceLoading');
            const container = modal.querySelector('#faceDetectionContainer');
            const failedDiv = modal.querySelector('#faceDetectionFailed');
            
            // Set timeout for face detection initialization
            const initTimeout = setTimeout(() => {
                console.log('Face detection initialization timeout, trying simple detection...');
                this.trySimpleFaceDetection(modal);
            }, 8000); // 8 second timeout
            
            // Try MediaPipe first
            const faceDetectionReady = await this.faceCamera.ensureInitialized();
            
            if (faceDetectionReady) {
                // MediaPipe is ready
                await this.faceCamera.startCamera(video, canvas);
                clearTimeout(initTimeout);
                
                console.log('MediaPipe face detection started successfully');
                
                // Hide loading, show camera
                loading.style.display = 'none';
                container.style.display = 'block';
                
                // Setup event listeners
                this.setupFaceDetectionEventListeners(modal);
                
                // Start monitoring face detection
                this.monitorMandatoryFaceDetection(modal);
                
            } else {
                // MediaPipe failed, try simple detection
                clearTimeout(initTimeout);
                console.log('MediaPipe not available, trying simple face detection...');
                this.trySimpleFaceDetection(modal);
            }
            
        } catch (error) {
            console.error('Face detection initialization error:', error);
            clearTimeout(initTimeout);
            
            // Try simple face detection as fallback
            this.trySimpleFaceDetection(modal);
        }
    }

    async trySimpleFaceDetection(modal) {
        console.log('Trying simple face detection...');
        
        try {
            // Load simple face detection
            if (!window.SimpleFaceDetection) {
                console.error('SimpleFaceDetection not available');
                throw new Error('Simple face detection not loaded');
            }
            
            const video = modal.querySelector('#faceVideo');
            const canvas = modal.querySelector('#faceCanvas');
            const loading = modal.querySelector('#faceLoading');
            const container = modal.querySelector('#faceDetectionContainer');
            
            // Create simple face detector
            this.simpleFaceDetector = new SimpleFaceDetection();
            
            // Start camera
            const constraints = {
                video: {
                    facingMode: 'user',
                    width: { ideal: 720, max: 1280 },
                    height: { ideal: 1280, max: 1920 }
                },
                audio: false
            };
            
            const stream = await navigator.mediaDevices.getUserMedia(constraints);
            video.srcObject = stream;
            this.currentStream = stream;
            
            // Wait for video to load
            await new Promise((resolve, reject) => {
                video.onloadedmetadata = () => {
                    // Set canvas size
                    canvas.width = video.videoWidth;
                    canvas.height = video.videoHeight;
                    resolve();
                };
                video.onerror = reject;
                setTimeout(() => reject(new Error('Video load timeout')), 10000);
            });
            
            // Start simple face detection
            await this.simpleFaceDetector.startDetection(video, canvas);
            
            // Hide loading, show camera
            loading.style.display = 'none';
            container.style.display = 'block';
            
            // Setup event listeners for simple detection
            this.setupSimpleFaceDetectionEventListeners(modal);
            
            // Monitor simple face detection
            this.monitorSimpleFaceDetection(modal);
            
            console.log('Simple face detection started successfully');
            
        } catch (error) {
            console.error('Simple face detection failed:', error);
            
            // Show final error
            const loading = modal.querySelector('#faceLoading');
            const failedDiv = modal.querySelector('#faceDetectionFailed');
            
            loading.style.display = 'none';
            failedDiv.style.display = 'block';
        }
    }

    setupFaceDetectionEventListeners(modal) {
        const captureBtn = modal.querySelector('#faceDetectionCaptureBtn');
        const retakeBtn = modal.querySelector('#faceRetakeBtn');
        const useBtn = modal.querySelector('#faceUseBtn');
        
        captureBtn.onclick = () => this.takeMandatoryFacePhoto(modal);
        retakeBtn.onclick = () => this.retakeMandatoryFacePhoto(modal);
        useBtn.onclick = () => this.useMandatoryFacePhoto(modal);
    }

    monitorMandatoryFaceDetection(modal) {
        const captureBtn = modal.querySelector('#faceDetectionCaptureBtn');
        const statusText = modal.querySelector('#faceStatusText');
        const captureButtonText = modal.querySelector('#captureButtonText');
        
        const checkFaceDetection = () => {
            if (!this.faceCamera || !document.body.contains(modal)) {
                return; // Stop monitoring if modal is closed
            }
            
            const faceDetected = this.faceCamera.isFaceDetected();
            const faceValid = this.faceCamera.isFaceValid();
            
            if (faceDetected && faceValid) {
                // Face detected and valid
                captureBtn.disabled = false;
                captureBtn.className = 'btn btn-success';
                captureButtonText.textContent = 'Ambil Foto Sekarang!';
                
                statusText.innerHTML = '<i class="fas fa-check-circle text-success me-2"></i>Wajah Terdeteksi - Siap!';
                
            } else if (faceDetected && !faceValid) {
                // Face detected but not valid position
                captureBtn.disabled = true;
                captureBtn.className = 'btn btn-warning';
                captureButtonText.textContent = 'Perbaiki Posisi Wajah';
                
                const validation = this.faceCamera.getValidationMessage();
                statusText.innerHTML = `<i class="fas fa-exclamation-triangle text-warning me-2"></i>${validation}`;
                
            } else {
                // No face detected
                captureBtn.disabled = true;
                captureBtn.className = 'btn btn-primary';
                captureButtonText.textContent = 'Menunggu Wajah...';
                
                statusText.innerHTML = '<i class="fas fa-search text-info me-2"></i>Mencari wajah...';
            }
            
            // Continue monitoring
            setTimeout(checkFaceDetection, 200); // Check every 200ms
        };
        
        // Start monitoring after a short delay
        setTimeout(checkFaceDetection, 1000);
    }

    // Simple camera modal without face detection (for testing)
    showSimpleCameraModal() {
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
                        <button type="button" class="btn-close btn-close-white" onclick="attendanceMobile.closeSimpleModal()"></button>
                    </div>
                    <div class="modal-body p-0">
                        <!-- Loading State -->
                        <div id="simpleLoading" class="text-center p-5">
                            <div class="spinner-border text-primary mb-3" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <p class="text-muted">Memulai kamera...</p>
                        </div>
                        
                        <!-- Camera Container -->
                        <div id="simpleCameraContainer" style="display: none; position: relative;">
                            <video id="simpleCameraVideo" class="w-100" autoplay muted playsinline style="transform: scaleX(-1);"></video>
                            
                            <!-- Camera Controls Overlay -->
                            <div class="position-absolute top-0 end-0 p-3" style="z-index: 10;">
                                <button type="button" class="btn btn-sm btn-outline-light" onclick="attendanceMobile.toggleMirror()" title="Toggle Mirror Mode">
                                    <i class="fas fa-sync-alt"></i>
                                </button>
                            </div>
                            
                            <!-- Info Overlay -->
                            <div class="position-absolute bottom-0 start-0 end-0 p-3" style="z-index: 10;">
                                <div class="bg-dark bg-opacity-75 text-white px-3 py-2 rounded text-center">
                                    <small><i class="fas fa-info-circle me-1"></i>Klik tombol <i class="fas fa-sync-alt"></i> untuk mengubah mode mirror</small>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Photo Preview -->
                        <div id="simplePhotoPreview" style="display: none;" class="text-center p-3">
                            <img id="simpleCapturedPhoto" class="img-fluid rounded shadow">
                            <div class="mt-2">
                                <small class="text-muted">
                                    <i class="fas fa-check-circle text-success me-1"></i>
                                    Foto hasil akan tersimpan dalam orientasi normal (tidak mirror)
                                </small>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" onclick="attendanceMobile.closeSimpleModal()">
                            <i class="fas fa-times me-2"></i>Batal
                        </button>
                        <button type="button" id="simpleCaptureBtn" class="btn btn-primary" disabled>
                            <i class="fas fa-camera me-2"></i>Ambil Foto
                        </button>
                        <button type="button" id="simpleRetakeBtn" class="btn btn-warning" style="display: none;">
                            <i class="fas fa-redo me-2"></i>Ambil Ulang
                        </button>
                        <button type="button" id="simpleUseBtn" class="btn btn-success" style="display: none;">
                            <i class="fas fa-check me-2"></i>Gunakan Foto
                        </button>
                    </div>
                </div>
            </div>
        `;
        
        document.body.appendChild(modal);
        this.currentModal = modal;
        
        // Initialize simple camera
        this.initializeSimpleCamera(modal);
    }

    async initializeSimpleCamera(modal) {
        console.log('Initializing simple camera...');
        
        try {
            const video = modal.querySelector('#simpleCameraVideo');
            const loading = modal.querySelector('#simpleLoading');
            const cameraContainer = modal.querySelector('#simpleCameraContainer');
            const captureBtn = modal.querySelector('#simpleCaptureBtn');
            
            // Simple camera constraints
            const constraints = {
                video: {
                    facingMode: 'user',
                    width: { ideal: 720, max: 1280 },
                    height: { ideal: 1280, max: 1920 }
                },
                audio: false
            };
            
            console.log('Requesting camera access...');
            const stream = await navigator.mediaDevices.getUserMedia(constraints);
            
            video.srcObject = stream;
            this.currentStream = stream;
            
            // Wait for video to load
            await new Promise((resolve, reject) => {
                video.onloadedmetadata = () => {
                    console.log('Video loaded successfully');
                    resolve();
                };
                video.onerror = (error) => {
                    console.error('Video load error:', error);
                    reject(error);
                };
                
                setTimeout(() => {
                    reject(new Error('Video load timeout'));
                }, 10000);
            });
            
            // Hide loading, show camera
            loading.style.display = 'none';
            cameraContainer.style.display = 'block';
            
            // Enable capture button
            captureBtn.disabled = false;
            captureBtn.onclick = () => this.takeSimplePhoto(modal, video);
            
            console.log('Simple camera initialized successfully');
            
        } catch (error) {
            console.error('Simple camera error:', error);
            
            const loading = modal.querySelector('#simpleLoading');
            let errorMessage = 'Tidak dapat mengakses kamera.';
            
            if (error.name === 'NotAllowedError') {
                errorMessage = 'Izin kamera ditolak. Silakan berikan izin kamera dan coba lagi.';
            } else if (error.name === 'NotFoundError') {
                errorMessage = 'Kamera tidak ditemukan. Pastikan device memiliki kamera.';
            } else if (error.name === 'NotReadableError') {
                errorMessage = 'Kamera sedang digunakan aplikasi lain. Tutup aplikasi lain dan coba lagi.';
            }
            
            loading.innerHTML = `
                <div class="alert alert-danger border-0">
                    <i class="fas fa-times-circle me-2"></i>
                    <strong>Error:</strong> ${errorMessage}
                </div>
                <button class="btn btn-primary" onclick="attendanceMobile.initializeSimpleCamera(attendanceMobile.currentModal)">
                    <i class="fas fa-redo me-2"></i>Coba Lagi
                </button>
            `;
        }
    }

    takeSimplePhoto(modal, video) {
        try {
            console.log('Taking simple photo...');
            
            const canvas = document.createElement('canvas');
            const ctx = canvas.getContext('2d');
            
            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;
            
            // Check if video is mirrored
            const isVideoMirrored = video.style.transform.includes('scaleX(-1)');
            
            if (isVideoMirrored) {
                // If video is mirrored, flip the canvas to get normal photo
                ctx.scale(-1, 1);
                ctx.drawImage(video, -canvas.width, 0, canvas.width, canvas.height);
            } else {
                // If video is normal, draw normally
                ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
            }
            
            canvas.toBlob((blob) => {
                this.photoBlob = blob;
                
                // Show preview
                const preview = modal.querySelector('#simplePhotoPreview');
                const img = modal.querySelector('#simpleCapturedPhoto');
                img.src = URL.createObjectURL(blob);
                
                modal.querySelector('#simpleCameraContainer').style.display = 'none';
                preview.style.display = 'block';
                
                // Update buttons
                modal.querySelector('#simpleCaptureBtn').style.display = 'none';
                modal.querySelector('#simpleRetakeBtn').style.display = 'inline-block';
                modal.querySelector('#simpleUseBtn').style.display = 'inline-block';
                
                // Setup button events
                modal.querySelector('#simpleRetakeBtn').onclick = () => this.retakeSimplePhoto(modal);
                modal.querySelector('#simpleUseBtn').onclick = () => this.useSimplePhoto(modal);
                
                console.log('Photo captured successfully');
                
            }, 'image/jpeg', 0.9);
            
        } catch (error) {
            console.error('Photo capture error:', error);
            alert('Gagal mengambil foto. Silakan coba lagi.');
        }
    }

    retakeSimplePhoto(modal) {
        // Show camera again
        modal.querySelector('#simpleCameraContainer').style.display = 'block';
        modal.querySelector('#simplePhotoPreview').style.display = 'none';
        
        // Reset buttons
        modal.querySelector('#simpleCaptureBtn').style.display = 'inline-block';
        modal.querySelector('#simpleRetakeBtn').style.display = 'none';
        modal.querySelector('#simpleUseBtn').style.display = 'none';
    }

    useSimplePhoto(modal) {
        // Update UI to show photo is captured
        const photoStatus = document.getElementById('photoStatus');
        if (photoStatus) {
            photoStatus.innerHTML = '<i class="fas fa-check-circle text-success me-2"></i>Foto berhasil diambil';
        }
        
        this.closeSimpleModal();
    }

    closeSimpleModal() {
        if (this.currentStream) {
            this.currentStream.getTracks().forEach(track => track.stop());
            this.currentStream = null;
        }
        
        if (this.currentModal) {
            this.currentModal.remove();
            this.currentModal = null;
        }
    }

    // Toggle mirror effect on simple camera preview
    toggleMirror() {
        const video = document.getElementById('simpleCameraVideo');
        if (video) {
            const currentTransform = video.style.transform;
            if (currentTransform.includes('scaleX(-1)')) {
                video.style.transform = 'scaleX(1)'; // Normal view
            } else {
                video.style.transform = 'scaleX(-1)'; // Mirror view (default for selfie)
            }
        }
    }

    async takeMandatoryFacePhoto(modal) {
        try {
            console.log('Taking mandatory face photo...');
            
            // Double check face detection
            if (!this.faceCamera.isFaceDetected() || !this.faceCamera.isFaceValid()) {
                alert('Wajah belum terdeteksi dengan benar. Pastikan posisi wajah sudah tepat.');
                return;
            }
            
            // Capture photo with face detection validation
            this.photoBlob = await this.faceCamera.captureValidatedPhoto();
            
            if (!this.photoBlob) {
                alert('Gagal mengambil foto. Pastikan wajah terdeteksi dengan baik.');
                return;
            }
            
            // Show preview
            const preview = modal.querySelector('#facePhotoPreview');
            const img = modal.querySelector('#faceCapturedPhoto');
            img.src = URL.createObjectURL(this.photoBlob);
            
            modal.querySelector('#faceDetectionContainer').style.display = 'none';
            preview.style.display = 'block';
            
            // Update buttons
            modal.querySelector('#faceDetectionCaptureBtn').style.display = 'none';
            modal.querySelector('#faceRetakeBtn').style.display = 'inline-block';
            modal.querySelector('#faceUseBtn').style.display = 'inline-block';
            
            console.log('Mandatory face photo captured successfully');
            
        } catch (error) {
            console.error('Mandatory face photo capture error:', error);
            alert('Gagal mengambil foto wajah. Silakan coba lagi.');
        }
    }

    retakeMandatoryFacePhoto(modal) {
        // Show camera again
        modal.querySelector('#faceDetectionContainer').style.display = 'block';
        modal.querySelector('#facePhotoPreview').style.display = 'none';
        
        // Reset buttons
        modal.querySelector('#faceDetectionCaptureBtn').style.display = 'inline-block';
        modal.querySelector('#faceRetakeBtn').style.display = 'none';
        modal.querySelector('#faceUseBtn').style.display = 'none';
        
        // Restart face detection monitoring
        this.monitorMandatoryFaceDetection(modal);
    }

    useMandatoryFacePhoto(modal) {
        // Update UI to show photo is captured with face validation
        const photoStatus = document.getElementById('photoStatus');
        if (photoStatus) {
            photoStatus.innerHTML = '<i class="fas fa-user-check text-success me-2"></i>Foto wajah terverifikasi berhasil diambil';
        }
        
        this.closeFaceDetectionModal();
    }

    toggleFaceMirror() {
        const video = document.getElementById('faceVideo');
        if (video) {
            const currentTransform = video.style.transform;
            if (currentTransform.includes('scaleX(-1)')) {
                video.style.transform = 'scaleX(1)'; // Normal view
            } else {
                video.style.transform = 'scaleX(-1)'; // Mirror view (default for selfie)
            }
        }
    }

    closeFaceDetectionModal() {
        // Stop MediaPipe face detection
        if (this.faceCamera) {
            this.faceCamera.stopCamera();
        }
        
        // Stop simple face detection
        if (this.simpleFaceDetector) {
            this.simpleFaceDetector.stopDetection();
            this.simpleFaceDetector = null;
        }
        
        // Stop camera stream
        if (this.currentStream) {
            this.currentStream.getTracks().forEach(track => track.stop());
            this.currentStream = null;
        }
        
        if (this.currentModal) {
            this.currentModal.remove();
            this.currentModal = null;
        }
    }

    // Retry face detection initialization
    async retryFaceDetection() {
        console.log('Retrying face detection initialization...');
        
        const modal = this.currentModal;
        if (!modal) return;
        
        // Show loading again
        modal.querySelector('#faceDetectionFailed').style.display = 'none';
        modal.querySelector('#faceLoading').style.display = 'block';
        
        // Stop any existing detection
        if (this.simpleFaceDetector) {
            this.simpleFaceDetector.stopDetection();
            this.simpleFaceDetector = null;
        }
        
        if (this.currentStream) {
            this.currentStream.getTracks().forEach(track => track.stop());
            this.currentStream = null;
        }
        
        // Recreate face camera instance
        this.faceCamera = new FaceDetectionCamera();
        
        // Wait a bit then try initialization
        setTimeout(() => {
            this.initializeMandatoryFaceDetection(modal);
        }, 1000);
    }

    // Remove fallback to simple camera - face detection is MANDATORY
    showSimpleCameraFallback() {
        alert('Absensi memerlukan verifikasi wajah. Silakan refresh halaman atau hubungi admin jika masalah berlanjut.');
    }

    setupSimpleFaceDetectionEventListeners(modal) {
        const captureBtn = modal.querySelector('#faceDetectionCaptureBtn');
        const retakeBtn = modal.querySelector('#faceRetakeBtn');
        const useBtn = modal.querySelector('#faceUseBtn');
        
        captureBtn.onclick = () => this.takeSimpleFacePhoto(modal);
        retakeBtn.onclick = () => this.retakeSimpleFacePhoto(modal);
        useBtn.onclick = () => this.useSimpleFacePhoto(modal);
    }

    monitorSimpleFaceDetection(modal) {
        const captureBtn = modal.querySelector('#faceDetectionCaptureBtn');
        const captureButtonText = modal.querySelector('#captureButtonText');
        
        const checkSimpleFaceDetection = () => {
            if (!this.simpleFaceDetector || !document.body.contains(modal)) {
                return; // Stop monitoring if modal is closed
            }
            
            const faceDetected = this.simpleFaceDetector.isFaceDetected();
            
            if (faceDetected) {
                // Face detected and valid
                captureBtn.disabled = false;
                captureBtn.className = 'btn btn-success';
                captureButtonText.textContent = 'Ambil Foto Sekarang!';
            } else {
                // No face detected or not valid
                captureBtn.disabled = true;
                captureBtn.className = 'btn btn-primary';
                captureButtonText.textContent = 'Menunggu Validasi...';
            }
            
            // Continue monitoring
            setTimeout(checkSimpleFaceDetection, 300); // Check every 300ms
        };
        
        // Start monitoring after a short delay
        setTimeout(checkSimpleFaceDetection, 1000);
    }

    async takeSimpleFacePhoto(modal) {
        try {
            console.log('Taking simple face photo...');
            
            // Check face detection
            if (!this.simpleFaceDetector.isFaceDetected()) {
                alert('Pastikan wajah terdeteksi dengan baik sebelum mengambil foto.');
                return;
            }
            
            // Capture photo with simple validation
            this.photoBlob = await this.simpleFaceDetector.captureValidatedPhoto();
            
            if (!this.photoBlob) {
                alert('Gagal mengambil foto. Silakan coba lagi.');
                return;
            }
            
            // Show preview
            const preview = modal.querySelector('#facePhotoPreview');
            const img = modal.querySelector('#faceCapturedPhoto');
            img.src = URL.createObjectURL(this.photoBlob);
            
            modal.querySelector('#faceDetectionContainer').style.display = 'none';
            preview.style.display = 'block';
            
            // Update buttons
            modal.querySelector('#faceDetectionCaptureBtn').style.display = 'none';
            modal.querySelector('#faceRetakeBtn').style.display = 'inline-block';
            modal.querySelector('#faceUseBtn').style.display = 'inline-block';
            
            console.log('Simple face photo captured successfully');
            
        } catch (error) {
            console.error('Simple face photo capture error:', error);
            alert('Gagal mengambil foto wajah. Silakan coba lagi.');
        }
    }

    retakeSimpleFacePhoto(modal) {
        // Show camera again
        modal.querySelector('#faceDetectionContainer').style.display = 'block';
        modal.querySelector('#facePhotoPreview').style.display = 'none';
        
        // Reset buttons
        modal.querySelector('#faceDetectionCaptureBtn').style.display = 'inline-block';
        modal.querySelector('#faceRetakeBtn').style.display = 'none';
        modal.querySelector('#faceUseBtn').style.display = 'none';
        
        // Restart simple face detection monitoring
        this.monitorSimpleFaceDetection(modal);
    }

    useSimpleFacePhoto(modal) {
        // Update UI to show photo is captured with face validation
        const photoStatus = document.getElementById('photoStatus');
        if (photoStatus) {
            photoStatus.innerHTML = '<i class="fas fa-user-check text-success me-2"></i>Foto wajah tervalidasi berhasil diambil';
        }
        
        this.closeFaceDetectionModal();
    }

    // Remove fallback to simple camera - face detection is MANDATORY
    showSimpleCameraFallback() {
        alert('Absensi memerlukan verifikasi wajah. Silakan refresh halaman atau hubungi admin jika masalah berlanjut.');
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
        console.log('Initializing enhanced camera...');
        
        const video = modal.querySelector('#cameraVideo');
        const canvas = modal.querySelector('#cameraCanvas');
        const loading = modal.querySelector('#cameraLoading');
        const cameraContainer = modal.querySelector('#cameraContainer');
        
        try {
            // Add timeout for the entire initialization process
            const initTimeout = setTimeout(() => {
                console.log('Enhanced camera initialization timeout, falling back to basic camera');
                this.showBasicCameraFallback(modal);
            }, 8000); // 8 second timeout
            
            // Check if face camera is available and try to start it
            if (this.faceCamera) {
                console.log('Attempting to start face detection camera...');
                
                try {
                    await this.faceCamera.startCamera(video, canvas);
                    clearTimeout(initTimeout);
                    
                    console.log('Face detection camera started successfully');
                    
                    // Hide loading, show camera
                    loading.style.display = 'none';
                    cameraContainer.style.display = 'block';
                    
                    // Setup event listeners
                    this.setupCameraEventListeners(modal);
                    
                    // Monitor face detection status
                    this.monitorFaceDetection(modal);
                    
                    return; // Success, exit function
                    
                } catch (cameraError) {
                    console.error('Face detection camera failed:', cameraError);
                    clearTimeout(initTimeout);
                    // Fall through to basic camera
                }
            }
            
            // If we reach here, face detection failed or not available
            console.log('Face detection not available, using basic camera');
            this.showBasicCameraFallback(modal);
            
        } catch (error) {
            console.error('Enhanced camera initialization error:', error);
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

    // Debug function to test camera access
    async debugCamera() {
        console.log('=== CAMERA DEBUG START ===');
        
        try {
            // Test 1: Check if getUserMedia is available
            console.log('1. getUserMedia available:', !!navigator.mediaDevices?.getUserMedia);
            
            // Test 2: Check camera permissions
            if (navigator.permissions) {
                const permission = await navigator.permissions.query({ name: 'camera' });
                console.log('2. Camera permission:', permission.state);
            }
            
            // Test 3: Try to get camera devices
            if (navigator.mediaDevices?.enumerateDevices) {
                const devices = await navigator.mediaDevices.enumerateDevices();
                const cameras = devices.filter(device => device.kind === 'videoinput');
                console.log('3. Available cameras:', cameras.length);
                cameras.forEach((camera, index) => {
                    console.log(`   Camera ${index + 1}:`, camera.label || 'Unknown Camera');
                });
            }
            
            // Test 4: Try basic camera access
            console.log('4. Testing basic camera access...');
            const stream = await navigator.mediaDevices.getUserMedia({
                video: { facingMode: 'user' }
            });
            console.log('4. Basic camera access: SUCCESS');
            
            // Stop the test stream
            stream.getTracks().forEach(track => track.stop());
            
            // Test 5: Check MediaPipe availability
            console.log('5. Testing MediaPipe availability...');
            try {
                const response = await fetch('https://cdn.jsdelivr.net/npm/@mediapipe/face_detection@0.4/face_detection.js');
                console.log('5. MediaPipe CDN status:', response.status);
            } catch (e) {
                console.log('5. MediaPipe CDN error:', e.message);
            }
            
            console.log('=== CAMERA DEBUG SUCCESS ===');
            alert('✅ Debug berhasil! Cek console untuk detail. Kamera seharusnya bisa digunakan.');
            
        } catch (error) {
            console.error('=== CAMERA DEBUG ERROR ===');
            console.error('Error details:', error);
            console.error('Error name:', error.name);
            console.error('Error message:', error.message);
            
            let errorMsg = 'Debug gagal: ';
            if (error.name === 'NotAllowedError') {
                errorMsg += 'Izin kamera ditolak';
            } else if (error.name === 'NotFoundError') {
                errorMsg += 'Kamera tidak ditemukan';
            } else if (error.name === 'NotReadableError') {
                errorMsg += 'Kamera sedang digunakan aplikasi lain';
            } else {
                errorMsg += error.message;
            }
            
            alert('❌ ' + errorMsg + '\n\nCek console browser untuk detail lengkap.');
        }
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
        console.log('Switching to basic camera fallback');
        
        // Update loading message
        const loading = modal.querySelector('#cameraLoading');
        loading.innerHTML = `
            <div class="alert alert-info border-0">
                <i class="fas fa-info-circle me-2"></i>
                Menggunakan kamera standar (tanpa deteksi wajah)
            </div>
            <div class="spinner-border text-primary mb-3" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="text-muted">Memulai kamera...</p>
        `;
        
        // Initialize basic camera
        setTimeout(() => {
            this.initializeBasicCamera(modal);
        }, 1000);
    }

    async initializeBasicCamera(modal) {
        try {
            console.log('Initializing basic camera...');
            
            // Get camera constraints
            const constraints = {
                video: {
                    facingMode: 'user',
                    width: { ideal: 720, max: 1280 },
                    height: { ideal: 1280, max: 1920 }
                },
                audio: false
            };
            
            console.log('Requesting camera access with constraints:', constraints);
            const stream = await navigator.mediaDevices.getUserMedia(constraints);
            
            const video = modal.querySelector('#cameraVideo');
            const canvas = modal.querySelector('#cameraCanvas');
            const loading = modal.querySelector('#cameraLoading');
            const cameraContainer = modal.querySelector('#cameraContainer');
            
            video.srcObject = stream;
            video.style.display = 'block';
            canvas.style.display = 'none';
            
            // Wait for video to load
            await new Promise((resolve, reject) => {
                video.onloadedmetadata = () => {
                    console.log('Video loaded successfully');
                    resolve();
                };
                video.onerror = (error) => {
                    console.error('Video load error:', error);
                    reject(error);
                };
                
                // Timeout after 10 seconds
                setTimeout(() => {
                    reject(new Error('Video load timeout'));
                }, 10000);
            });
            
            // Hide loading, show camera
            loading.style.display = 'none';
            cameraContainer.style.display = 'block';
            
            // Enable capture button for basic mode
            const captureBtn = modal.querySelector('#captureBtn');
            captureBtn.disabled = false;
            captureBtn.innerHTML = '<i class="fas fa-camera me-2"></i>Ambil Foto';
            captureBtn.className = 'btn btn-primary';
            captureBtn.onclick = () => this.takeBasicPhoto(modal, video, stream);
            
            console.log('Basic camera initialized successfully');
            
        } catch (error) {
            console.error('Basic camera error:', error);
            
            const loading = modal.querySelector('#cameraLoading');
            let errorMessage = 'Tidak dapat mengakses kamera.';
            
            if (error.name === 'NotAllowedError') {
                errorMessage = 'Izin kamera ditolak. Silakan berikan izin kamera dan coba lagi.';
            } else if (error.name === 'NotFoundError') {
                errorMessage = 'Kamera tidak ditemukan. Pastikan device memiliki kamera.';
            } else if (error.name === 'NotReadableError') {
                errorMessage = 'Kamera sedang digunakan aplikasi lain. Tutup aplikasi lain dan coba lagi.';
            }
            
            loading.innerHTML = `
                <div class="alert alert-danger border-0">
                    <i class="fas fa-times-circle me-2"></i>
                    <strong>Error:</strong> ${errorMessage}
                </div>
                <button class="btn btn-primary" onclick="attendanceMobile.initializeBasicCamera(attendanceMobile.currentModal)">
                    <i class="fas fa-redo me-2"></i>Coba Lagi
                </button>
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

// Initialize AttendanceMobile when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    window.attendanceMobile = new AttendanceMobile();
});
