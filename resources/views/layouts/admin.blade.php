<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin') - Absensi Siswa</title>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    @vite(['resources/scss/app.scss', 'resources/js/app.js'])
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <!-- Custom Alert Styles -->
    <style>
        .alert {
            border: none;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-bottom: 1rem;
        }
        
        .alert-success {
            background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
            border-left: 4px solid #28a745;
        }
        
        .alert-danger {
            background: linear-gradient(135deg, #f8d7da 0%, #f1b0b7 100%);
            border-left: 4px solid #dc3545;
        }
        
        .alert-warning {
            background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%);
            border-left: 4px solid #ffc107;
        }
        
        .alert-info {
            background: linear-gradient(135deg, #d1ecf1 0%, #bee5eb 100%);
            border-left: 4px solid #17a2b8;
        }
        
        .alert .fas {
            margin-right: 8px;
        }
        
        .alert-permanent {
            /* Class untuk alert yang tidak auto-hide */
        }
        
        @keyframes slideInDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        @keyframes slideOutUp {
            from {
                opacity: 1;
                transform: translateY(0);
            }
            to {
                opacity: 0;
                transform: translateY(-20px);
            }
        }
        
        .alert-slide-in {
            animation: slideInDown 0.3s ease-out;
        }
        
        .alert-slide-out {
            animation: slideOutUp 0.3s ease-in;
        }
        
        /* Sidebar Navigation Styles */
        .nav-dropdown {
            margin: 0.25rem 0;
            position: relative;
        }
        
        .nav-dropdown-toggle {
            position: relative;
            display: flex !important;
            align-items: center;
            justify-content: space-between;
        }
        
        .nav-dropdown-toggle .dropdown-arrow {
            transition: transform 0.3s ease;
            font-size: 0.75rem;
            margin-left: auto;
        }
        
        .nav-dropdown:hover .dropdown-arrow {
            transform: rotate(180deg);
        }
        
        .nav-dropdown-menu {
            background: rgba(0, 0, 0, 0.15);
            border-radius: 0.5rem;
            margin: 0.25rem 0;
            padding: 0.5rem 0;
            max-height: 0;
            overflow: hidden;
            opacity: 0;
            transition: all 0.3s ease;
            transform: translateY(-10px);
        }
        
        .nav-dropdown-menu.show {
            max-height: 200px;
            opacity: 1;
            transform: translateY(0);
        }
        
        .nav-sub {
            padding-left: 3.5rem !important;
            font-size: 0.9rem;
            margin: 0.125rem 0;
        }
        
        .nav-sub i {
            width: 16px;
            margin-right: 0.75rem;
            font-size: 0.875rem;
        }
        
        .nav-link-sidebar {
            display: flex;
            align-items: center;
            padding: 0.75rem 1.5rem;
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            transition: all 0.3s ease;
            border-radius: 0.5rem;
            margin: 0.125rem 0.75rem;
        }
        
        .nav-link-sidebar:hover {
            background: rgba(255, 255, 255, 0.1);
            color: white;
            transform: translateX(4px);
        }
        
        .nav-link-sidebar.active {
            background: rgba(255, 255, 255, 0.15);
            color: white;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        
        .nav-link-sidebar svg,
        .nav-link-sidebar i {
            width: 20px;
            height: 20px;
            margin-right: 0.75rem;
            flex-shrink: 0;
        }
        
        .nav-text {
            flex: 1;
        }
        
        /* Modern Table Styles */
        .table-modern {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            border: none;
        }
        
        .table-modern thead {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        
        .table-modern thead th {
            color: white;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.5px;
            padding: 1rem 0.75rem;
            border: none;
        }
        
        .table-modern tbody tr {
            transition: all 0.3s ease;
            border: none;
        }
        
        .table-row-hover:hover {
            background: linear-gradient(135deg, #f8f9ff 0%, #f0f4ff 100%);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }
        
        .table-modern td {
            padding: 1rem 0.75rem;
            border: none;
            border-bottom: 1px solid #f1f3f4;
            vertical-align: middle;
        }
        
        .table-number {
            width: 32px;
            height: 32px;
            background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            color: #1976d2;
            font-size: 0.875rem;
        }
        
        .violation-name strong {
            color: #2c3e50;
            font-size: 0.95rem;
        }
        
        .badge-success {
            background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .badge-warning {
            background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%);
            color: #856404;
            border: 1px solid #ffeaa7;
        }
        
        .badge-danger {
            background: linear-gradient(135deg, #f8d7da 0%, #f1b0b7 100%);
            color: #721c24;
            border: 1px solid #f1b0b7;
        }
        
        .badge-points {
            background: linear-gradient(135deg, #e1f5fe 0%, #b3e5fc 100%);
            color: #01579b;
            border: 1px solid #b3e5fc;
        }
        
        .badge {
            padding: 0.5rem 0.75rem;
            border-radius: 20px;
            font-weight: 500;
            font-size: 0.75rem;
        }
        
        .btn-toggle {
            border-radius: 20px;
            font-weight: 500;
            font-size: 0.75rem;
            padding: 0.4rem 0.8rem;
            border: none;
            transition: all 0.3s ease;
        }
        
        .btn-toggle-active {
            background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
            color: #155724;
        }
        
        .btn-toggle-active:hover {
            background: linear-gradient(135deg, #c3e6cb 0%, #a3d9a4 100%);
            transform: translateY(-1px);
        }
        
        .btn-toggle-inactive {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            color: #6c757d;
        }
        
        .btn-toggle-inactive:hover {
            background: linear-gradient(135deg, #e9ecef 0%, #dee2e6 100%);
            transform: translateY(-1px);
        }
        
        .description-text {
            color: #6c757d;
            font-size: 0.875rem;
            line-height: 1.4;
        }
        
        .action-buttons {
            display: flex;
            gap: 0.5rem;
        }
        
        .btn-action {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            border: none;
            transition: all 0.3s ease;
        }
        
        .btn-edit {
            background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%);
            color: #856404;
        }
        
        .btn-edit:hover {
            background: linear-gradient(135deg, #ffeaa7 0%, #fdcb6e 100%);
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(255, 193, 7, 0.3);
        }
        
        .btn-delete {
            background: linear-gradient(135deg, #f8d7da 0%, #f1b0b7 100%);
            color: #721c24;
        }
        
        .btn-delete:hover {
            background: linear-gradient(135deg, #f1b0b7 0%, #e17055 100%);
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(220, 53, 69, 0.3);
        }
        
        .empty-state {
            padding: 3rem 2rem;
        }
        
        .empty-state i {
            color: #dee2e6 !important;
        }
        
        /* Student Violations Table Specific Styles */
        .date-info {
            text-align: center;
        }
        
        .date-main {
            font-weight: 600;
            color: #2c3e50;
            font-size: 0.9rem;
        }
        
        .date-sub {
            color: #6c757d;
            font-size: 0.75rem;
            margin-top: 0.25rem;
        }
        
        .student-info {
            min-width: 150px;
        }
        
        .student-name {
            font-weight: 600;
            color: #2c3e50;
            font-size: 0.9rem;
            margin-bottom: 0.25rem;
        }
        
        .student-details {
            display: flex;
            flex-direction: column;
            gap: 0.125rem;
        }
        
        .student-nisn, .student-class {
            font-size: 0.75rem;
            color: #6c757d;
        }
        
        .violation-info {
            min-width: 180px;
        }
        
        .violation-name {
            font-weight: 600;
            color: #2c3e50;
            font-size: 0.9rem;
            margin-bottom: 0.5rem;
        }
        
        .violation-badges {
            display: flex;
            gap: 0.25rem;
            flex-wrap: wrap;
        }
        
        .location-text, .reporter-text {
            color: #6c757d;
            font-size: 0.875rem;
            font-style: italic;
        }
        
        .badge-status {
            font-weight: 500;
            padding: 0.5rem 0.75rem;
        }
        
        .badge-status-warning {
            background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%);
            color: #856404;
            border: 1px solid #ffeaa7;
        }
        
        .badge-status-danger {
            background: linear-gradient(135deg, #f8d7da 0%, #f1b0b7 100%);
            color: #721c24;
            border: 1px solid #f1b0b7;
        }
        
        .badge-status-success {
            background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .btn-view {
            background: linear-gradient(135deg, #e1f5fe 0%, #b3e5fc 100%);
            color: #01579b;
        }
        
        .btn-view:hover {
            background: linear-gradient(135deg, #b3e5fc 0%, #81d4fa 100%);
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(3, 169, 244, 0.3);
        }
        
        /* Modern Filter Card */
        .filter-card {
            background: linear-gradient(135deg, #f8f9ff 0%, #f0f4ff 100%);
            border: 1px solid #e3f2fd;
            border-radius: 12px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }
        
        .form-control-modern, .form-select.form-control-modern {
            border: 1px solid #e1e5e9;
            border-radius: 8px;
            padding: 0.75rem 1rem;
            transition: all 0.3s ease;
            background: white;
        }
        
        .form-control-modern:focus, .form-select.form-control-modern:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
            transform: translateY(-1px);
        }
        
        .btn-modern {
            border-radius: 8px;
            padding: 0.75rem 1.5rem;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .btn-modern:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
        }
    </style>
</head>
<body class="bg-light">
    <div class="d-flex min-vh-100">
        <!-- Sidebar Overlay (Mobile) -->
        <div class="sidebar-overlay" id="sidebarOverlay"></div>

        <!-- Sidebar -->
        <aside class="sidebar bg-primary text-white" id="sidebar">
            <div class="p-4 d-flex justify-content-between align-items-start">
                <div>
                    <h1 class="h4 fw-bold mb-1">📚 Absensi</h1>
                    <p class="text-white-50 small mb-0">Sistem Absensi Siswa</p>
                </div>
                <button class="btn btn-link text-white d-lg-none p-0" id="closeSidebar">
                    <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            
            <nav class="mt-3">
                <!-- Menu untuk semua role -->
                <a href="{{ route('admin.dashboard') }}" 
                   class="nav-link-sidebar {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                    </svg>
                    <span class="nav-text">Dashboard</span>
                </a>
                
                <a href="{{ route('admin.attendance.index') }}" 
                   class="nav-link-sidebar {{ request()->routeIs('admin.attendance.*') ? 'active' : '' }}">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                    </svg>
                    <span class="nav-text">Absensi</span>
                </a>
                
                <!-- Menu khusus admin -->
                @if(auth()->check() && auth()->user()->role === 'admin')
                    <a href="{{ route('admin.students.index') }}" 
                       class="nav-link-sidebar {{ request()->routeIs('admin.students.*') ? 'active' : '' }}">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                        </svg>
                        <span class="nav-text">Siswa</span>
                    </a>
                    
                    <!-- Dropdown Menu Pelanggaran -->
                    <div class="nav-dropdown" onmouseenter="showDropdown('violationDropdown')" onmouseleave="hideDropdown('violationDropdown')">
                        <a href="#" class="nav-link-sidebar nav-dropdown-toggle {{ request()->routeIs('admin.violation-types.*') || request()->routeIs('admin.student-violations.*') ? 'active' : '' }}">
                            <i class="fas fa-exclamation-triangle"></i>
                            <span class="nav-text">Pelanggaran</span>
                            <i class="fas fa-chevron-down dropdown-arrow"></i>
                        </a>
                        <div class="nav-dropdown-menu" id="violationDropdown">
                            <a href="{{ route('admin.violation-types.index') }}" 
                               class="nav-link-sidebar nav-sub {{ request()->routeIs('admin.violation-types.*') ? 'active' : '' }}">
                                <i class="fas fa-list-alt"></i>
                                <span class="nav-text">Jenis Pelanggaran</span>
                            </a>
                            <a href="{{ route('admin.student-violations.index') }}" 
                               class="nav-link-sidebar nav-sub {{ request()->routeIs('admin.student-violations.*') ? 'active' : '' }}">
                                <i class="fas fa-clipboard-list"></i>
                                <span class="nav-text">Data Pelanggaran</span>
                            </a>
                        </div>
                    </div>
                    
                    <a href="{{ route('admin.users.index') }}" 
                       class="nav-link-sidebar {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                        <span class="nav-text">User</span>
                    </a>
                    
                    <!-- Dropdown Menu Laporan -->
                    <div class="nav-dropdown" onmouseenter="showDropdown('reportDropdown')" onmouseleave="hideDropdown('reportDropdown')">
                        <a href="#" class="nav-link-sidebar nav-dropdown-toggle {{ request()->routeIs('admin.reports.*') ? 'active' : '' }}">
                            <i class="fas fa-chart-bar"></i>
                            <span class="nav-text">Laporan</span>
                            <i class="fas fa-chevron-down dropdown-arrow"></i>
                        </a>
                        <div class="nav-dropdown-menu" id="reportDropdown">
                            <a href="{{ route('admin.reports.index') }}" 
                               class="nav-link-sidebar nav-sub {{ request()->routeIs('admin.reports.index') ? 'active' : '' }}">
                                <i class="fas fa-file-alt"></i>
                                <span class="nav-text">Laporan Absensi</span>
                            </a>
                            <a href="#" class="nav-link-sidebar nav-sub">
                                <i class="fas fa-exclamation-circle"></i>
                                <span class="nav-text">Laporan Pelanggaran</span>
                            </a>
                        </div>
                    </div>
                    
                    <a href="{{ route('admin.holidays.index') }}" 
                       class="nav-link-sidebar {{ request()->routeIs('admin.holidays.*') ? 'active' : '' }}">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        <span class="nav-text">Hari Libur</span>
                    </a>
                @endif
                
                <!-- Menu pengaturan untuk semua role (tapi dengan akses berbeda) -->
                <a href="{{ route('admin.settings.index') }}" 
                   class="nav-link-sidebar {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    <span class="nav-text">Pengaturan</span>
                </a>
            </nav>
        </aside>

        <!-- Main Content -->
        <div class="flex-grow-1 main-content" id="mainContent">
            <!-- Top Navbar -->
            <header class="bg-white shadow-sm border-bottom sticky-top">
                <div class="d-flex align-items-center justify-content-between px-4 py-3">
                    <div class="d-flex align-items-center gap-3">
                        <button class="btn btn-link text-dark p-0 d-lg-none" id="toggleSidebar">
                            <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                            </svg>
                        </button>
                        <button class="btn btn-link text-dark p-0 d-none d-lg-block" id="collapseSidebar" title="Toggle Sidebar">
                            <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                            </svg>
                        </button>
                        <h2 class="h5 fw-semibold text-dark mb-0">@yield('header', 'Dashboard')</h2>
                    </div>
                    
                    <div class="d-flex align-items-center gap-3">
                        <!-- User Profile Dropdown -->
                        <div class="dropdown">
                            <button class="btn btn-link text-decoration-none p-0 d-flex align-items-center gap-2" 
                                    type="button" 
                                    id="userDropdown" 
                                    data-bs-toggle="dropdown" 
                                    aria-expanded="false">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="text-muted d-none d-sm-inline">{{ auth()->check() ? auth()->user()->name : 'Admin' }}</span>
                                    @if(auth()->check() && auth()->user()->hasAvatar())
                                        <img src="{{ auth()->user()->avatar_url }}" 
                                             alt="Avatar {{ auth()->user()->name }}" 
                                             class="rounded-circle" 
                                             style="width: 35px; height: 35px; object-fit: cover;">
                                    @else
                                        <div class="avatar-circle bg-primary rounded-circle text-white d-flex align-items-center justify-content-center" style="width: 35px; height: 35px;">
                                            {{ auth()->check() ? substr(auth()->user()->name, 0, 1) : 'A' }}
                                        </div>
                                    @endif
                                    <i class="fas fa-chevron-down text-muted small"></i>
                                </div>
                            </button>
                            
                            <ul class="dropdown-menu dropdown-menu-end shadow-sm" aria-labelledby="userDropdown">
                                <li>
                                    <div class="dropdown-header">
                                        <div class="d-flex align-items-center">
                                            @if(auth()->check() && auth()->user()->hasAvatar())
                                                <img src="{{ auth()->user()->avatar_url }}" 
                                                     alt="Avatar {{ auth()->user()->name }}" 
                                                     class="rounded-circle me-2" 
                                                     style="width: 30px; height: 30px; object-fit: cover;">
                                            @else
                                                <div class="avatar-circle bg-primary rounded-circle text-white d-flex align-items-center justify-content-center me-2" style="width: 30px; height: 30px;">
                                                    {{ auth()->check() ? substr(auth()->user()->name, 0, 1) : 'A' }}
                                                </div>
                                            @endif
                                            <div>
                                                <div class="fw-semibold">{{ auth()->check() ? auth()->user()->name : 'Admin' }}</div>
                                                <small class="text-muted">{{ auth()->check() ? (auth()->user()->position ?? 'Guru') : 'Guru' }}</small>
                                            </div>
                                        </div>
                                    </div>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <a class="dropdown-item" href="{{ route('admin.profile.show') }}">
                                        <i class="fas fa-user me-2"></i>Profil Saya
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="{{ route('admin.profile.edit') }}">
                                        <i class="fas fa-edit me-2"></i>Edit Profil
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="{{ route('admin.settings.index') }}">
                                        <i class="fas fa-cog me-2"></i>Pengaturan
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <form action="{{ route('logout') }}" method="POST" class="d-inline w-100">
                                        @csrf
                                        <button type="submit" class="dropdown-item text-danger">
                                            <i class="fas fa-sign-out-alt me-2"></i>Logout
                                        </button>
                                    </form>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Page Content -->
            <main class="p-4">
                @if(session('success'))
                    <script>
                        document.addEventListener('DOMContentLoaded', function() {
                            Swal.fire({
                                title: 'Berhasil!',
                                text: '{{ session('success') }}',
                                icon: 'success',
                                timer: 3000,
                                timerProgressBar: true,
                                showConfirmButton: false,
                                toast: true,
                                position: 'top-end'
                            });
                        });
                    </script>
                @endif

                @if(session('error'))
                    <script>
                        document.addEventListener('DOMContentLoaded', function() {
                            Swal.fire({
                                title: 'Error!',
                                text: '{{ session('error') }}',
                                icon: 'error',
                                timer: 3000,
                                timerProgressBar: true,
                                showConfirmButton: false,
                                toast: true,
                                position: 'top-end'
                            });
                        });
                    </script>
                @endif

                @yield('content')
            </main>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const sidebar = document.getElementById('sidebar');
            const mainContent = document.getElementById('mainContent');
            const overlay = document.getElementById('sidebarOverlay');
            const toggleBtn = document.getElementById('toggleSidebar');
            const closeBtn = document.getElementById('closeSidebar');
            const collapseBtn = document.getElementById('collapseSidebar');

            // Mobile toggle
            toggleBtn?.addEventListener('click', () => {
                sidebar.classList.add('show');
                overlay.classList.add('show');
            });

            // Close sidebar mobile
            const closeSidebar = () => {
                sidebar.classList.remove('show');
                overlay.classList.remove('show');
            };

            closeBtn?.addEventListener('click', closeSidebar);
            overlay?.addEventListener('click', closeSidebar);

            // Desktop collapse
            collapseBtn?.addEventListener('click', () => {
                sidebar.classList.toggle('collapsed');
                mainContent.classList.toggle('expanded');
            });
        });

        // Hover dropdown functions
        function showDropdown(dropdownId) {
            const dropdown = document.getElementById(dropdownId);
            if (dropdown) {
                dropdown.classList.add('show');
            }
        }

        function hideDropdown(dropdownId) {
            const dropdown = document.getElementById(dropdownId);
            if (dropdown) {
                dropdown.classList.remove('show');
            }
        }

        // SweetAlert Delete Confirmation
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('btn-delete') || e.target.closest('.btn-delete')) {
                e.preventDefault();
                
                const button = e.target.classList.contains('btn-delete') ? e.target : e.target.closest('.btn-delete');
                const form = button.closest('form');
                
                Swal.fire({
                    title: 'Apakah Anda yakin?',
                    text: "Data yang dihapus tidak dapat dikembalikan!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Ya, Hapus!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            }
        });
    </script>
    
    <!-- Auto-hide Alert Script -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Auto-hide Bootstrap alerts after 3 seconds
        const alerts = document.querySelectorAll('.alert:not(.alert-permanent)');
        
        alerts.forEach(function(alert) {
            // Add slide-in animation
            alert.classList.add('alert-slide-in');
            
            // Auto-hide after 3 seconds
            setTimeout(function() {
                if (alert && alert.parentNode) {
                    // Add slide-out animation
                    alert.classList.remove('alert-slide-in');
                    alert.classList.add('alert-slide-out');
                    
                    // Remove from DOM after animation
                    setTimeout(function() {
                        if (alert && alert.parentNode) {
                            alert.remove();
                        }
                    }, 300);
                }
            }, 3000);
        });
        
        // Handle manual close button with animation
        const closeButtons = document.querySelectorAll('.alert .btn-close');
        closeButtons.forEach(function(button) {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                const alert = this.closest('.alert');
                if (alert) {
                    alert.classList.remove('alert-slide-in');
                    alert.classList.add('alert-slide-out');
                    
                    setTimeout(function() {
                        if (alert && alert.parentNode) {
                            alert.remove();
                        }
                    }, 300);
                }
            });
        });
        
        // Progress bar for auto-hide (optional visual indicator)
        alerts.forEach(function(alert) {
            if (!alert.classList.contains('alert-permanent')) {
                const progressBar = document.createElement('div');
                progressBar.style.cssText = `
                    position: absolute;
                    bottom: 0;
                    left: 0;
                    height: 3px;
                    background: rgba(0,0,0,0.2);
                    width: 100%;
                    animation: shrink 3s linear;
                `;
                
                alert.style.position = 'relative';
                alert.style.overflow = 'hidden';
                alert.appendChild(progressBar);
            }
        });
    });
    
    // Add CSS animation for progress bar
    const style = document.createElement('style');
    style.textContent = `
        @keyframes shrink {
            from { width: 100%; }
            to { width: 0%; }
        }
    `;
    document.head.appendChild(style);
    </script>

    @stack('scripts')
</body>
</html>
