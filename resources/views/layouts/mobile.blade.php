<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Absensi Mobile') - {{ config('app.name') }}</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- SweetAlert2 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    
    <!-- PWA Meta Tags -->
    <meta name="theme-color" content="#0d6efd">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="Absensi Mobile">
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="apple-touch-icon" href="{{ asset('favicon.ico') }}">
    
    <style>
        :root {
            --primary-color: #0d6efd;
            --secondary-color: #6c757d;
            --success-color: #198754;
            --danger-color: #dc3545;
            --warning-color: #ffc107;
            --info-color: #0dcaf0;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background-color: #f8f9fa;
            padding-bottom: env(safe-area-inset-bottom);
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }
        
        /* Mobile optimizations */
        .container-fluid {
            max-width: 480px;
            margin: 0 auto;
        }
        
        /* Card styling */
        .card {
            border-radius: 16px;
            border: none;
            box-shadow: 0 2px 12px rgba(0,0,0,0.08);
        }
        
        .card-body {
            padding: 1.5rem;
        }
        
        /* Button styling */
        .btn {
            border-radius: 12px;
            font-weight: 600;
            padding: 12px 24px;
            border: none;
            transition: all 0.2s ease;
        }
        
        .btn-lg {
            padding: 16px 32px;
            font-size: 1.1rem;
        }
        
        .btn:active {
            transform: scale(0.98);
        }
        
        /* Primary button gradient */
        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color) 0%, #0056b3 100%);
        }
        
        .btn-success {
            background: linear-gradient(135deg, var(--success-color) 0%, #146c43 100%);
        }
        
        .btn-danger {
            background: linear-gradient(135deg, var(--danger-color) 0%, #b02a37 100%);
        }
        
        /* Alert styling */
        .alert {
            border-radius: 12px;
            border: none;
        }
        
        /* Badge styling */
        .badge {
            border-radius: 8px;
            font-weight: 500;
        }
        
        /* Modal styling */
        .modal-content {
            border-radius: 16px;
            border: none;
        }
        
        .modal-header {
            border-bottom: 1px solid #e9ecef;
            border-radius: 16px 16px 0 0;
        }
        
        .modal-footer {
            border-top: 1px solid #e9ecef;
            border-radius: 0 0 16px 16px;
        }
        
        /* Camera modal specific */
        #cameraVideo {
            border-radius: 12px;
            max-height: 300px;
            object-fit: cover;
        }
        
        #capturedPhoto {
            border-radius: 12px;
            max-height: 300px;
            object-fit: cover;
        }
        
        /* Loading spinner */
        .spinner-border-sm {
            width: 1rem;
            height: 1rem;
        }
        
        /* Touch-friendly sizing */
        @media (max-width: 576px) {
            .container-fluid {
                padding-left: 1rem;
                padding-right: 1rem;
            }
            
            .card-body {
                padding: 1.25rem;
            }
            
            .btn-lg {
                padding: 14px 28px;
                font-size: 1rem;
            }
        }
        
        /* Safe area for notched devices */
        @supports (padding: max(0px)) {
            body {
                padding-top: max(1rem, env(safe-area-inset-top));
                padding-left: max(1rem, env(safe-area-inset-left));
                padding-right: max(1rem, env(safe-area-inset-right));
            }
        }
        
        /* Prevent zoom on input focus (iOS) */
        input, select, textarea {
            font-size: 16px;
        }
        
        /* Hide scrollbar but keep functionality */
        ::-webkit-scrollbar {
            width: 0px;
            background: transparent;
        }
        
        /* Status indicators */
        .status-indicator {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            display: inline-block;
            margin-right: 8px;
        }
        
        .status-success {
            background-color: var(--success-color);
        }
        
        .status-warning {
            background-color: var(--warning-color);
        }
        
        .status-danger {
            background-color: var(--danger-color);
        }
        
        /* Haptic feedback simulation */
        .btn:active,
        .card:active {
            transform: scale(0.98);
        }
        
        /* Dark mode support */
        @media (prefers-color-scheme: dark) {
            body {
                background-color: #1a1a1a;
                color: #ffffff;
            }
            
            .card {
                background-color: #2d2d2d;
                color: #ffffff;
            }
            
            .text-muted {
                color: #adb5bd !important;
            }
        }
    </style>
    
    @stack('styles')
</head>
<body>
    <!-- Main Content -->
    <main>
        @yield('content')
    </main>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <!-- Base Scripts -->
    <script>
        // Prevent zoom on double tap
        let lastTouchEnd = 0;
        document.addEventListener('touchend', function (event) {
            const now = (new Date()).getTime();
            if (now - lastTouchEnd <= 300) {
                event.preventDefault();
            }
            lastTouchEnd = now;
        }, false);
        
        // Add haptic feedback for supported devices
        function hapticFeedback(type = 'light') {
            if (navigator.vibrate) {
                switch(type) {
                    case 'light':
                        navigator.vibrate(10);
                        break;
                    case 'medium':
                        navigator.vibrate(20);
                        break;
                    case 'heavy':
                        navigator.vibrate(50);
                        break;
                }
            }
        }
        
        // Add haptic feedback to buttons
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.btn').forEach(btn => {
                btn.addEventListener('touchstart', () => hapticFeedback('light'));
            });
        });
        
        // Service Worker registration for PWA (optional)
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', function() {
                // Register service worker if available
                // navigator.serviceWorker.register('/sw.js');
            });
        }
        
        // Handle network status
        window.addEventListener('online', function() {
            console.log('Connection restored');
        });
        
        window.addEventListener('offline', function() {
            console.log('Connection lost');
            Swal.fire({
                icon: 'warning',
                title: 'Koneksi Terputus',
                text: 'Periksa koneksi internet Anda',
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000
            });
        });
    </script>
    
    @stack('scripts')
</body>
</html>