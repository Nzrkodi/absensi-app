/**
 * Face Detection for Attendance System
 * Using MediaPipe Face Detection with improved error handling
 */

class FaceDetectionCamera {
    constructor() {
        this.faceDetection = null;
        this.camera = null;
        this.isDetecting = false;
        this.faceDetected = false;
        this.detectionResults = null;
        this.stream = null;
        this.videoElement = null;
        this.canvasElement = null;
        this.canvasCtx = null;
        this.isInitialized = false;
        
        // Configuration
        this.config = {
            minDetectionConfidence: 0.7,    // Increased from 0.5 to 0.7 for better accuracy
            minFaceSize: 0.20,              // Increased from 0.15 to 0.20 (20% of image)
            maxFaceSize: 0.70,              // Decreased from 0.8 to 0.70 (70% of image)
            centerTolerance: 0.25,          // Decreased from 0.3 to 0.25 (25% tolerance)
            requiredStability: 5,           // Increased from 3 to 5 consecutive detections
            stabilityCounter: 0,
            maxDistance: 0.15               // Maximum distance from center (15%)
        };
        
        // Don't initialize immediately, wait for first use
        console.log('FaceDetectionCamera created, will initialize on first use');
    }

    async ensureInitialized() {
        if (this.isInitialized) {
            return this.faceDetection !== null;
        }
        
        console.log('Initializing face detection for first use...');
        await this.initializeFaceDetection();
        this.isInitialized = true;
        return this.faceDetection !== null;
    }

    async initializeFaceDetection() {
        try {
            console.log('Initializing face detection...');
            
            // Try multiple CDN sources for MediaPipe
            const cdnSources = [
                'https://cdn.jsdelivr.net/npm/@mediapipe/face_detection@0.4/face_detection.js',
                'https://unpkg.com/@mediapipe/face_detection@0.4/face_detection.js'
            ];
            
            for (const cdnUrl of cdnSources) {
                try {
                    console.log(`Trying to load MediaPipe from: ${cdnUrl}`);
                    
                    const loadTimeout = new Promise((_, reject) => {
                        setTimeout(() => reject(new Error('MediaPipe load timeout')), 5000);
                    });
                    
                    const loadMediaPipe = import(cdnUrl)
                        .then(({ FaceDetection }) => {
                            console.log('MediaPipe loaded successfully from:', cdnUrl);
                            this.setupFaceDetection(FaceDetection);
                            return true;
                        });
                    
                    await Promise.race([loadMediaPipe, loadTimeout]);
                    return; // Success, exit function
                    
                } catch (error) {
                    console.warn(`Failed to load from ${cdnUrl}:`, error);
                    continue; // Try next CDN
                }
            }
            
            // If all CDNs fail, try local fallback
            throw new Error('All MediaPipe CDN sources failed');
            
        } catch (error) {
            console.error('Face detection initialization failed:', error);
            // Set to null so system knows face detection is not available
            this.faceDetection = null;
        }
    }

    setupFaceDetection(FaceDetection) {
        this.faceDetection = new FaceDetection({
            locateFile: (file) => {
                return `https://cdn.jsdelivr.net/npm/@mediapipe/face_detection@0.4/${file}`;
            }
        });

        this.faceDetection.setOptions({
            model: 'short',
            minDetectionConfidence: this.config.minDetectionConfidence,
        });

        this.faceDetection.onResults((results) => {
            this.onFaceDetectionResults(results);
        });

        console.log('Face detection initialized successfully');
    }

    async startCamera(videoElement, canvasElement) {
        console.log('Starting camera...');
        
        this.videoElement = videoElement;
        this.canvasElement = canvasElement;
        this.canvasCtx = canvasElement.getContext('2d');

        try {
            // Get optimal camera constraints for mobile
            const constraints = this.getCameraConstraints();
            console.log('Camera constraints:', constraints);
            
            this.stream = await navigator.mediaDevices.getUserMedia(constraints);
            this.videoElement.srcObject = this.stream;
            
            // Wait for video to load
            await new Promise((resolve, reject) => {
                this.videoElement.onloadedmetadata = () => {
                    console.log('Video metadata loaded');
                    // Set canvas size to match video
                    this.updateCanvasSize();
                    resolve();
                };
                
                this.videoElement.onerror = (error) => {
                    console.error('Video error:', error);
                    reject(error);
                };
                
                // Timeout after 10 seconds
                setTimeout(() => {
                    reject(new Error('Video load timeout'));
                }, 10000);
            });

            // Try to initialize face detection
            const faceDetectionReady = await this.ensureInitialized();
            
            if (faceDetectionReady) {
                console.log('Starting face detection...');
                this.startFaceDetection();
            } else {
                console.log('Face detection not available, camera ready without face detection');
            }

            return true;
        } catch (error) {
            console.error('Camera start error:', error);
            throw error;
        }
    }

    getCameraConstraints() {
        // Detect device type and set appropriate constraints
        const isMobile = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
        
        if (isMobile) {
            return {
                video: {
                    facingMode: 'user', // Front camera
                    width: { ideal: 720, max: 1280 },
                    height: { ideal: 1280, max: 1920 },
                    aspectRatio: { ideal: 9/16 }, // Portrait for mobile
                    frameRate: { ideal: 30, max: 30 }
                },
                audio: false
            };
        } else {
            return {
                video: {
                    facingMode: 'user',
                    width: { ideal: 640, max: 1280 },
                    height: { ideal: 480, max: 720 },
                    aspectRatio: { ideal: 4/3 }, // Standard for desktop
                    frameRate: { ideal: 30, max: 30 }
                },
                audio: false
            };
        }
    }

    updateCanvasSize() {
        if (this.videoElement && this.canvasElement) {
            // Set canvas size to match video's actual dimensions
            this.canvasElement.width = this.videoElement.videoWidth;
            this.canvasElement.height = this.videoElement.videoHeight;
            
            // Update canvas display size to fit container
            const container = this.canvasElement.parentElement;
            if (container) {
                const containerWidth = container.clientWidth;
                const aspectRatio = this.videoElement.videoWidth / this.videoElement.videoHeight;
                
                this.canvasElement.style.width = containerWidth + 'px';
                this.canvasElement.style.height = (containerWidth / aspectRatio) + 'px';
            }
        }
    }

    startFaceDetection() {
        if (!this.faceDetection || !this.videoElement) return;

        this.isDetecting = true;
        this.detectFaces();
    }

    async detectFaces() {
        if (!this.isDetecting || !this.videoElement || this.videoElement.readyState !== 4) {
            if (this.isDetecting) {
                requestAnimationFrame(() => this.detectFaces());
            }
            return;
        }

        try {
            await this.faceDetection.send({ image: this.videoElement });
        } catch (error) {
            console.error('Face detection error:', error);
        }

        if (this.isDetecting) {
            requestAnimationFrame(() => this.detectFaces());
        }
    }

    onFaceDetectionResults(results) {
        if (!this.canvasCtx || !this.videoElement) return;

        // Clear canvas
        this.canvasCtx.clearRect(0, 0, this.canvasElement.width, this.canvasElement.height);
        
        // Draw video frame
        this.canvasCtx.drawImage(this.videoElement, 0, 0, this.canvasElement.width, this.canvasElement.height);

        this.detectionResults = results;
        
        if (results.detections && results.detections.length > 0) {
            this.processFaceDetections(results.detections);
        } else {
            this.faceDetected = false;
            this.config.stabilityCounter = 0;
            this.showFaceGuidance('Tidak ada wajah terdeteksi', 'warning');
        }
    }

    processFaceDetections(detections) {
        const detection = detections[0]; // Use first detected face
        const bbox = detection.boundingBox;
        
        // Validate face detection
        const validation = this.validateFaceDetection(bbox);
        
        if (validation.isValid) {
            this.config.stabilityCounter++;
            
            if (this.config.stabilityCounter >= this.config.requiredStability) {
                this.faceDetected = true;
                this.showFaceGuidance('Wajah terdeteksi dengan baik!', 'success');
            } else {
                this.showFaceGuidance(`Tahan posisi... (${this.config.stabilityCounter}/${this.config.requiredStability})`, 'info');
            }
        } else {
            this.faceDetected = false;
            this.config.stabilityCounter = 0;
            this.showFaceGuidance(validation.message, 'warning');
        }

        // Draw face detection overlay
        this.drawFaceOverlay(bbox, validation.isValid);
    }

    validateFaceDetection(bbox) {
        const videoWidth = this.videoElement.videoWidth;
        const videoHeight = this.videoElement.videoHeight;
        
        // Calculate face dimensions
        const faceWidth = bbox.width * videoWidth;
        const faceHeight = bbox.height * videoHeight;
        const faceArea = faceWidth * faceHeight;
        const videoArea = videoWidth * videoHeight;
        const faceRatio = faceArea / videoArea;
        
        // Calculate face center
        const faceCenterX = (bbox.xCenter * videoWidth);
        const faceCenterY = (bbox.yCenter * videoHeight);
        const videoCenterX = videoWidth / 2;
        const videoCenterY = videoHeight / 2;
        
        // Calculate distance from center (normalized)
        const distanceFromCenterX = Math.abs(faceCenterX - videoCenterX) / (videoWidth / 2);
        const distanceFromCenterY = Math.abs(faceCenterY - videoCenterY) / (videoHeight / 2);
        const totalDistance = Math.sqrt(distanceFromCenterX * distanceFromCenterX + distanceFromCenterY * distanceFromCenterY);
        
        // Validation checks with more specific messages
        if (faceRatio < this.config.minFaceSize) {
            return { isValid: false, message: `Wajah terlalu kecil (${Math.round(faceRatio * 100)}%). Dekatkan ke kamera.` };
        }
        
        if (faceRatio > this.config.maxFaceSize) {
            return { isValid: false, message: `Wajah terlalu besar (${Math.round(faceRatio * 100)}%). Jauhkan dari kamera.` };
        }
        
        if (totalDistance > this.config.maxDistance) {
            if (distanceFromCenterX > this.config.centerTolerance) {
                return { isValid: false, message: 'Geser wajah ke tengah kamera (kiri-kanan)' };
            }
            if (distanceFromCenterY > this.config.centerTolerance) {
                return { isValid: false, message: 'Geser wajah ke tengah kamera (atas-bawah)' };
            }
            return { isValid: false, message: 'Posisikan wajah di tengah kamera' };
        }
        
        // Check face aspect ratio (should be roughly portrait)
        const faceAspectRatio = faceWidth / faceHeight;
        if (faceAspectRatio < 0.6 || faceAspectRatio > 1.4) {
            return { isValid: false, message: 'Posisi kepala terlalu miring. Tegakkan kepala.' };
        }
        
        return { 
            isValid: true, 
            message: `Posisi sempurna! (${Math.round(faceRatio * 100)}% area)` 
        };
    }

    drawFaceOverlay(bbox, isValid) {
        if (!this.canvasCtx) return;

        const videoWidth = this.canvasElement.width;
        const videoHeight = this.canvasElement.height;
        
        // Calculate face rectangle
        const x = bbox.xCenter * videoWidth - (bbox.width * videoWidth) / 2;
        const y = bbox.yCenter * videoHeight - (bbox.height * videoHeight) / 2;
        const width = bbox.width * videoWidth;
        const height = bbox.height * videoHeight;
        
        // Draw face rectangle
        this.canvasCtx.strokeStyle = isValid ? '#00ff00' : '#ff0000';
        this.canvasCtx.lineWidth = 3;
        this.canvasCtx.strokeRect(x, y, width, height);
        
        // Draw center guide
        this.drawCenterGuide(isValid);
    }

    drawCenterGuide(faceDetected) {
        const centerX = this.canvasElement.width / 2;
        const centerY = this.canvasElement.height / 2;
        const radius = 100;
        
        // Draw center circle
        this.canvasCtx.strokeStyle = faceDetected ? '#00ff00' : '#ffffff';
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

    showFaceGuidance(message, type) {
        const guidanceElement = document.getElementById('faceGuidance');
        if (guidanceElement) {
            guidanceElement.textContent = message;
            guidanceElement.className = `face-guidance ${type}`;
        }
    }

    capturePhoto() {
        if (!this.canvasElement || !this.videoElement) {
            throw new Error('Camera not initialized');
        }

        // Create a new canvas for the final photo
        const photoCanvas = document.createElement('canvas');
        const photoCtx = photoCanvas.getContext('2d');
        
        // Set photo canvas size to video dimensions for best quality
        photoCanvas.width = this.videoElement.videoWidth;
        photoCanvas.height = this.videoElement.videoHeight;
        
        // Draw the current video frame
        photoCtx.drawImage(this.videoElement, 0, 0, photoCanvas.width, photoCanvas.height);
        
        // Convert to blob with high quality
        return new Promise((resolve) => {
            photoCanvas.toBlob((blob) => {
                resolve(blob);
            }, 'image/jpeg', 0.9); // High quality JPEG
        });
    }

    stopCamera() {
        this.isDetecting = false;
        
        if (this.stream) {
            this.stream.getTracks().forEach(track => track.stop());
            this.stream = null;
        }
        
        if (this.videoElement) {
            this.videoElement.srcObject = null;
        }
        
        this.faceDetected = false;
        this.config.stabilityCounter = 0;
    }

    isFaceDetected() {
        return this.faceDetected;
    }

    isFaceValid() {
        return this.faceDetected && this.config.stabilityCounter >= this.config.requiredStability;
    }

    getValidationMessage() {
        if (!this.detectionResults || !this.detectionResults.detections || this.detectionResults.detections.length === 0) {
            return 'Tidak ada wajah terdeteksi';
        }

        const detection = this.detectionResults.detections[0];
        const bbox = detection.boundingBox;
        const validation = this.validateFaceDetection(bbox);
        
        return validation.message;
    }

    async captureValidatedPhoto() {
        if (!this.isFaceValid()) {
            throw new Error('Face not properly detected and validated');
        }

        if (!this.canvasElement || !this.videoElement) {
            throw new Error('Camera not initialized');
        }

        // Create a new canvas for the final photo
        const photoCanvas = document.createElement('canvas');
        const photoCtx = photoCanvas.getContext('2d');
        
        // Set photo canvas size to video dimensions for best quality
        photoCanvas.width = this.videoElement.videoWidth;
        photoCanvas.height = this.videoElement.videoHeight;
        
        // Check if video is mirrored and flip accordingly
        const isVideoMirrored = this.videoElement.style.transform.includes('scaleX(-1)');
        
        if (isVideoMirrored) {
            // If video is mirrored, flip the canvas to get normal photo
            photoCtx.scale(-1, 1);
            photoCtx.drawImage(this.videoElement, -photoCanvas.width, 0, photoCanvas.width, photoCanvas.height);
        } else {
            // If video is normal, draw normally
            photoCtx.drawImage(this.videoElement, 0, 0, photoCanvas.width, photoCanvas.height);
        }
        
        // Add face detection metadata to the photo
        const faceData = {
            timestamp: Date.now(),
            faceDetected: true,
            confidence: this.detectionResults.detections[0].score,
            boundingBox: this.detectionResults.detections[0].boundingBox,
            stabilityCount: this.config.stabilityCounter
        };
        
        console.log('Face validation data:', faceData);
        
        // Convert to blob with high quality
        return new Promise((resolve) => {
            photoCanvas.toBlob((blob) => {
                // Attach face validation metadata to blob
                blob.faceValidation = faceData;
                resolve(blob);
            }, 'image/jpeg', 0.9); // High quality JPEG
        });
    }

    // Fallback method for devices without face detection support
    async capturePhotoBasic() {
        if (!this.videoElement) {
            throw new Error('Camera not initialized');
        }

        const canvas = document.createElement('canvas');
        const ctx = canvas.getContext('2d');
        
        canvas.width = this.videoElement.videoWidth;
        canvas.height = this.videoElement.videoHeight;
        
        ctx.drawImage(this.videoElement, 0, 0, canvas.width, canvas.height);
        
        return new Promise((resolve) => {
            canvas.toBlob((blob) => {
                resolve(blob);
            }, 'image/jpeg', 0.9);
        });
    }
}

// Export for use in other modules
window.FaceDetectionCamera = FaceDetectionCamera;