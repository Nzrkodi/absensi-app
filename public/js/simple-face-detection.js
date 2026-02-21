/**
 * Simple Face Detection using Browser's built-in capabilities
 * Fallback when MediaPipe is not available
 */

class SimpleFaceDetection {
    constructor() {
        this.faceDetector = null;
        this.isSupported = false;
        this.videoElement = null;
        this.canvasElement = null;
        this.canvasCtx = null;
        this.detectionInterval = null;
        this.faceDetected = false;
        this.detectionCount = 0;
        this.requiredDetections = 3;
        
        // Store initialization promise
        this.initPromise = this.initializeFaceDetector();
    }
    
    // Wait for initialization to complete
    async waitForInitialization() {
        await this.initPromise;
        return this.isSupported;
    }

    async initializeFaceDetector() {
        try {
            // Check if browser supports FaceDetector API
            if ('FaceDetector' in window) {
                console.log('Using browser FaceDetector API');
                this.faceDetector = new FaceDetector({
                    maxDetectedFaces: 1,
                    fastMode: true
                });
                this.isSupported = true;
                return;
            }

            // Fallback: Check if we can use getUserMedia for basic validation
            if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
                console.log('Using basic camera validation (no face detection)');
                this.isSupported = true; // We'll do basic validation
                return;
            }

            throw new Error('No face detection capabilities available');

        } catch (error) {
            console.error('Face detector initialization failed:', error);
            this.isSupported = false;
        }
    }

    async startDetection(videoElement, canvasElement) {
        this.videoElement = videoElement;
        this.canvasElement = canvasElement;
        this.canvasCtx = canvasElement.getContext('2d');

        if (!this.isSupported) {
            throw new Error('Face detection not supported');
        }

        // Start detection loop
        this.detectionInterval = setInterval(() => {
            this.detectFaces();
        }, 500); // Check every 500ms
    }

    async detectFaces() {
        if (!this.videoElement || this.videoElement.readyState !== 4) {
            return;
        }

        try {
            // Clear canvas and draw video frame
            this.canvasCtx.clearRect(0, 0, this.canvasElement.width, this.canvasElement.height);
            this.canvasCtx.drawImage(this.videoElement, 0, 0, this.canvasElement.width, this.canvasElement.height);

            if (this.faceDetector) {
                // Use browser FaceDetector API
                const faces = await this.faceDetector.detect(this.canvasElement);
                
                if (faces && faces.length > 0) {
                    this.processFaceDetection(faces[0]);
                } else {
                    this.faceDetected = false;
                    this.detectionCount = 0;
                    this.showStatus('Tidak ada wajah terdeteksi', 'warning');
                }
            } else {
                // Fallback: Basic validation (assume face is present if video is working)
                this.processBasicValidation();
            }

        } catch (error) {
            console.error('Face detection error:', error);
            this.faceDetected = false;
            this.detectionCount = 0;
        }
    }

    processFaceDetection(face) {
        const bounds = face.boundingBox;
        const videoWidth = this.videoElement.videoWidth;
        const videoHeight = this.videoElement.videoHeight;

        // Calculate face size relative to video
        const faceArea = bounds.width * bounds.height;
        const videoArea = videoWidth * videoHeight;
        const faceRatio = faceArea / videoArea;

        // Calculate face center
        const faceCenterX = bounds.x + bounds.width / 2;
        const faceCenterY = bounds.y + bounds.height / 2;
        const videoCenterX = videoWidth / 2;
        const videoCenterY = videoHeight / 2;

        // Calculate distance from center
        const distanceX = Math.abs(faceCenterX - videoCenterX) / (videoWidth / 2);
        const distanceY = Math.abs(faceCenterY - videoCenterY) / (videoHeight / 2);

        // Validation rules
        const minFaceSize = 0.15; // 15% of video area
        const maxFaceSize = 0.7;  // 70% of video area
        const maxDistance = 0.4;  // 40% from center

        let isValid = true;
        let message = '';

        if (faceRatio < minFaceSize) {
            isValid = false;
            message = 'Wajah terlalu kecil, dekatkan ke kamera';
        } else if (faceRatio > maxFaceSize) {
            isValid = false;
            message = 'Wajah terlalu besar, jauhkan dari kamera';
        } else if (distanceX > maxDistance || distanceY > maxDistance) {
            isValid = false;
            message = 'Posisikan wajah di tengah kamera';
        } else {
            message = `Wajah terdeteksi dengan baik! (${Math.round(faceRatio * 100)}%)`;
        }

        if (isValid) {
            this.detectionCount++;
            if (this.detectionCount >= this.requiredDetections) {
                this.faceDetected = true;
                this.showStatus(message, 'success');
            } else {
                this.showStatus(`Validasi wajah... (${this.detectionCount}/${this.requiredDetections})`, 'info');
            }
        } else {
            this.faceDetected = false;
            this.detectionCount = 0;
            this.showStatus(message, 'warning');
        }

        // Draw face rectangle
        this.drawFaceRectangle(bounds, isValid);
    }

    processBasicValidation() {
        // Basic validation without actual face detection
        // Just check if video is working and assume face is present
        this.detectionCount++;
        
        if (this.detectionCount >= this.requiredDetections) {
            this.faceDetected = true;
            this.showStatus('Kamera aktif - Pastikan wajah terlihat jelas', 'success');
        } else {
            this.showStatus(`Memvalidasi kamera... (${this.detectionCount}/${this.requiredDetections})`, 'info');
        }

        // Draw center guide
        this.drawCenterGuide();
    }

    drawFaceRectangle(bounds, isValid) {
        const scaleX = this.canvasElement.width / this.videoElement.videoWidth;
        const scaleY = this.canvasElement.height / this.videoElement.videoHeight;

        const x = bounds.x * scaleX;
        const y = bounds.y * scaleY;
        const width = bounds.width * scaleX;
        const height = bounds.height * scaleY;

        this.canvasCtx.strokeStyle = isValid ? '#00ff00' : '#ff0000';
        this.canvasCtx.lineWidth = 3;
        this.canvasCtx.strokeRect(x, y, width, height);
    }

    drawCenterGuide() {
        const centerX = this.canvasElement.width / 2;
        const centerY = this.canvasElement.height / 2;
        const radius = 100;

        // Draw center circle
        this.canvasCtx.strokeStyle = this.faceDetected ? '#00ff00' : '#ffffff';
        this.canvasCtx.lineWidth = 2;
        this.canvasCtx.setLineDash([5, 5]);
        this.canvasCtx.beginPath();
        this.canvasCtx.arc(centerX, centerY, radius, 0, 2 * Math.PI);
        this.canvasCtx.stroke();
        this.canvasCtx.setLineDash([]);

        // Draw crosshair
        this.canvasCtx.beginPath();
        this.canvasCtx.moveTo(centerX - 20, centerY);
        this.canvasCtx.lineTo(centerX + 20, centerY);
        this.canvasCtx.moveTo(centerX, centerY - 20);
        this.canvasCtx.lineTo(centerX, centerY + 20);
        this.canvasCtx.stroke();
    }

    showStatus(message, type) {
        const statusElement = document.getElementById('faceStatusText');
        if (statusElement) {
            statusElement.textContent = message;
            
            const statusContainer = statusElement.closest('#faceStatus');
            if (statusContainer) {
                const bgClass = type === 'success' ? 'bg-success' : 
                              type === 'warning' ? 'bg-warning' : 
                              type === 'info' ? 'bg-info' : 'bg-dark';
                
                statusContainer.className = `text-center`;
                statusContainer.innerHTML = `
                    <div class="${bgClass} bg-opacity-75 text-white px-3 py-2 rounded">
                        <i class="fas ${type === 'success' ? 'fa-check-circle' : 
                                      type === 'warning' ? 'fa-exclamation-triangle' : 
                                      'fa-info-circle'} me-2"></i>
                        <span>${message}</span>
                    </div>
                `;
            }
        }
    }

    isFaceDetected() {
        return this.faceDetected;
    }

    stopDetection() {
        if (this.detectionInterval) {
            clearInterval(this.detectionInterval);
            this.detectionInterval = null;
        }
        this.faceDetected = false;
        this.detectionCount = 0;
    }

    async captureValidatedPhoto() {
        if (!this.faceDetected) {
            throw new Error('Face not detected');
        }

        if (!this.canvasElement || !this.videoElement) {
            throw new Error('Camera not initialized');
        }

        // Create photo canvas
        const photoCanvas = document.createElement('canvas');
        const photoCtx = photoCanvas.getContext('2d');
        
        photoCanvas.width = this.videoElement.videoWidth;
        photoCanvas.height = this.videoElement.videoHeight;
        
        // Handle mirror mode
        const isVideoMirrored = this.videoElement.style.transform.includes('scaleX(-1)');
        
        if (isVideoMirrored) {
            photoCtx.scale(-1, 1);
            photoCtx.drawImage(this.videoElement, -photoCanvas.width, 0, photoCanvas.width, photoCanvas.height);
        } else {
            photoCtx.drawImage(this.videoElement, 0, 0, photoCanvas.width, photoCanvas.height);
        }
        
        // Add validation metadata
        const validationData = {
            timestamp: Date.now(),
            faceDetected: true,
            detectionMethod: this.faceDetector ? 'browser_api' : 'basic_validation',
            detectionCount: this.detectionCount,
            isSupported: this.isSupported
        };
        
        console.log('Simple face validation data:', validationData);
        
        return new Promise((resolve) => {
            photoCanvas.toBlob((blob) => {
                blob.faceValidation = validationData;
                resolve(blob);
            }, 'image/jpeg', 0.9);
        });
    }
}

// Export for use
window.SimpleFaceDetection = SimpleFaceDetection;