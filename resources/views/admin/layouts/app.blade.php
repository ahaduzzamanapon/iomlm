<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Dashboard' }} — Admin | IOM</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <style>
    /* ═══ Official IOM Brand Color Theme (derived from https://iom.edu.bd/) ═══ */
    :root {
        --iom-green:     #047857;   /* Official IOM Primary Brand Emerald Green */
        --iom-dark:      #064e3b;   /* Official IOM Deep Forest Green */
        --iom-deep-dark: #022c22;   /* Islamic Dark Midnight Background */
        --iom-light:     #ecfdf5;   /* Official IOM Mint Light */
        --iom-mint:      #d1fae5;   /* Mint Border Line */
        --iom-gold:      #fbbf24;   /* Official IOM Accent Gold */
        --iom-ink:       #1e293b;   /* Dark Ink */
        --iom-muted:     #64748b;   /* Muted Slate */
        --iom-line:      #dbe7e2;   /* Border Line */

        /* Override App Theme Variables for Admin Panel */
        --blue: #047857;
        --blue-dark: #064e3b;
        --primary: #047857;
        --primary-dark: #064e3b;
        --sidebar-bg: #064e3b;
        --sidebar-active-bg: rgba(4, 120, 87, 0.45);
        --sidebar-active: #34d399;
        --topbar-border: #d1fae5;
    }

    body, input, select, textarea, button, .tree-toggle, .nav-item, .table, .card, h1, h2, h3, h4, h5, h6 {
        font-family: 'Kalpurush', 'Plus Jakarta Sans', 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    }

    /* ─── Admin Sidebar: IOM Deep Forest Green Gradient ────────── */
    .sidebar {
        background: linear-gradient(180deg, #022c22 0%, #064e3b 50%, #032b21 100%) !important;
        border-right: 1px solid rgba(52, 211, 153, 0.18) !important;
    }
    .sidebar-logo {
        background: rgba(0, 0, 0, 0.22) !important;
        border-bottom: 1px solid rgba(255, 255, 255, 0.08) !important;
    }
    .sidebar-logo-name {
        color: #ffffff !important;
    }
    .sidebar-logo-sub {
        color: #fbbf24 !important; /* IOM Brand Gold */
        font-weight: 800 !important;
        letter-spacing: 1.2px;
    }
    .nav-group-label {
        color: #6ee7b7 !important;
        opacity: 0.9;
    }

    /* ─── Tree Navigation & Items ──────────────────────────────── */
    .tree-group { margin-bottom: 2px; }

    .tree-toggle {
        display: flex;
        align-items: center;
        gap: 9px;
        padding: 9px 14px;
        border-radius: 8px;
        font-size: 13.5px;
        font-weight: 600;
        letter-spacing: .03em;
        text-transform: uppercase;
        color: #a7f3d0 !important;
        cursor: pointer;
        user-select: none;
        transition: background .15s, color .15s;
    }
    .tree-toggle:hover {
        background: rgba(255, 255, 255, 0.09) !important;
        color: #ffffff !important;
    }
    .tree-toggle.has-active {
        color: #34d399 !important;
    }

    .tree-toggle-arrow {
        margin-left: auto;
        transition: transform .25s cubic-bezier(.4,0,.2,1);
        opacity: .6;
        flex-shrink: 0;
    }
    .tree-toggle.open .tree-toggle-arrow { transform: rotate(90deg); opacity: 1; }

    .tree-children {
        overflow: hidden;
        max-height: 0;
        transition: max-height .3s cubic-bezier(.4,0,.2,1);
        padding-left: 8px;
    }
    .tree-children.open { max-height: 900px; }

    .tree-children .nav-item {
        font-size: 13px;
        padding: 7px 12px;
        border-radius: 6px;
        margin-bottom: 1px;
        border-left: 2px solid transparent;
        color: #9ca3af !important;
    }
    .tree-children .nav-item:hover {
        color: #ecfdf5 !important;
        background: rgba(255, 255, 255, 0.07) !important;
    }
    .tree-children .nav-item.active {
        border-left-color: #34d399 !important;
        background: rgba(4, 120, 87, 0.35) !important;
        color: #ffffff !important;
        font-weight: 700 !important;
    }

    .nav-item {
        color: #a7f3d0 !important;
    }
    .nav-item:hover {
        background: rgba(255, 255, 255, 0.08) !important;
        color: #ffffff !important;
    }
    .nav-item.active {
        background: linear-gradient(135deg, rgba(4, 120, 87, 0.75), rgba(5, 150, 105, 0.55)) !important;
        color: #ffffff !important;
        border: 1px solid rgba(52, 211, 153, 0.45) !important;
        box-shadow: 0 4px 14px rgba(2, 44, 34, 0.35) !important;
    }
    .nav-item.active i, .nav-item.active svg {
        color: #34d399 !important;
    }

    /* ─── Admin Topbar ─────────────────────────────────────────── */
    .topbar {
        background: rgba(255, 255, 255, 0.96) !important;
        border-bottom: 1px solid #d1fae5 !important;
    }
    .topbar-btn:hover {
        background: #ecfdf5 !important;
        color: #047857 !important;
    }
    .topbar-breadcrumb a:hover {
        color: #047857 !important;
    }
    .notif-dot {
        background: #fbbf24 !important; /* IOM Gold */
        box-shadow: 0 0 0 2px #fff;
    }
    .user-name {
        color: #1e293b !important;
    }

    /* ─── Admin Dashboard Banner: Forest Gradient & Gold Glow ──── */
    .dashboard-banner {
        background: linear-gradient(135deg, #064e3b 0%, #047857 55%, #065f46 100%) !important;
        border: 1px solid rgba(251, 191, 36, 0.25) !important;
        box-shadow: 0 10px 30px -5px rgba(6, 78, 59, 0.3) !important;
    }
    .dashboard-banner::after {
        background: radial-gradient(circle, rgba(251, 191, 36, 0.22) 0%, rgba(0,0,0,0) 70%) !important;
    }
    .banner-title { color: #ffffff !important; }
    .banner-sub { color: #d1fae5 !important; }
    .banner-status {
        background: rgba(255, 255, 255, 0.15) !important;
        border-color: rgba(251, 191, 36, 0.35) !important;
        color: #fbbf24 !important;
    }

    /* ─── Buttons & Controls in IOM Brand Theme ────────────────── */
    .btn-primary, button.btn-primary, a.btn-primary, .btn.btn-primary {
        background: linear-gradient(135deg, #047857 0%, #064e3b 100%) !important;
        border: none !important;
        color: #ffffff !important;
        box-shadow: 0 4px 14px rgba(4, 120, 87, 0.3) !important;
    }
    .btn-primary:hover, button.btn-primary:hover, a.btn-primary:hover, .btn.btn-primary:hover {
        background: linear-gradient(135deg, #064e3b 0%, #022c22 100%) !important;
        box-shadow: 0 6px 20px rgba(6, 78, 59, 0.45) !important;
        color: #ffffff !important;
    }

    .btn-outline:hover {
        border-color: #047857 !important;
        color: #047857 !important;
        background: #ecfdf5 !important;
    }

    .quick-tile:hover {
        border-color: #047857 !important;
        color: #047857 !important;
        box-shadow: 0 6px 16px -2px rgba(4, 120, 87, 0.15) !important;
    }
    .stat-card:hover {
        border-color: #a7f3d0 !important;
        box-shadow: 0 10px 25px -5px rgba(6, 78, 59, 0.12) !important;
    }

    /* Form Focus */
    input:focus, select:focus, textarea:focus {
        border-color: #047857 !important;
        box-shadow: 0 0 0 3px rgba(4, 120, 87, 0.18) !important;
        outline: none !important;
    }

    /* Loader Top Bar */
    .loader-top-bar {
        background: linear-gradient(90deg, #047857, #34d399, #fbbf24) !important;
    }
    .loader-ring {
        border-top-color: #047857 !important;
    }

    /* Pagination */
    .pagination .page-item.active .page-link, .page-item.active .page-link {
        background-color: #047857 !important;
        border-color: #047857 !important;
        color: #ffffff !important;
    }
    .page-link:hover {
        color: #047857 !important;
        background-color: #ecfdf5 !important;
    }

    /* Tables */
    thead th {
        border-bottom: 2px solid #d1fae5 !important;
    }
    tbody tr:hover td {
        background: #f0fdf4 !important;
    }
    </style>
    @stack('styles')
</head>
<body>
<!-- Global Page Loader System -->
<div class="loader-top-bar" id="globalTopBar"></div>
<div class="page-loader" id="globalPageLoader" style="display:none">
    <div class="loader-card">
        <div class="loader-spinner-container">
            <div class="loader-ring"></div>
            <img src="{{ asset('images/logo.png') }}" alt="IOM Logo" class="loader-logo-static" style="width:26px;height:26px;max-width:26px;max-height:26px;object-fit:contain">
        </div>
        <div class="loader-label">Loading IOM ERP...</div>
    </div>
</div>

<div class="app-wrapper">
    <div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>

    <!-- ═══ ADMIN SIDEBAR ═══ -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-logo" style="gap:12px">
            <img src="{{ asset('images/logo.png') }}" alt="IOM Logo" style="width:38px;height:38px;object-fit:contain">
            <div class="sidebar-logo-text">
                <span class="sidebar-logo-name">Admin Panel</span>
                <span class="sidebar-logo-sub">IOM ERP</span>
            </div>
        </div>

        <nav class="sidebar-nav">

            {{-- ── 1. Dashboard ── --}}
            <a href="{{ route('admin.dashboard') }}" class="nav-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" style="margin-bottom:4px">
                <i class="fa-solid fa-gauge-high"></i>
                Dashboard
            </a>

            {{-- ── 2. Academic Setup ── --}}
            @php $academicActive = request()->routeIs('admin.academic-years*','admin.subjects*','admin.courses*','admin.semesters*','admin.holiday-calendar*'); @endphp
            <div class="tree-group">
                <div class="tree-toggle {{ $academicActive ? 'has-active open' : '' }}" onclick="treeToggle(this)">
                    <i class="fa-solid fa-graduation-cap"></i>
                    Academic Setup
                    <i class="fa-solid fa-chevron-right tree-toggle-arrow"></i>
                </div>
                <div class="tree-children {{ $academicActive ? 'open' : '' }}">
                    <a href="{{ route('admin.academic-years.index') }}" class="nav-item {{ request()->routeIs('admin.academic-years*') ? 'active' : '' }}">
                        <i class="fa-solid fa-calendar-days"></i>
                        Academic Years
                    </a>
                    <a href="{{ route('admin.courses.index') }}" class="nav-item {{ request()->routeIs('admin.courses*') ? 'active' : '' }}">
                        <i class="fa-solid fa-book-open"></i>
                        Courses
                    </a>
                    <a href="{{ route('admin.subjects.index') }}" class="nav-item {{ request()->routeIs('admin.subjects*') ? 'active' : '' }}">
                        <i class="fa-solid fa-book-bookmark"></i>
                        Subjects &amp; Modules
                    </a>
                    <a href="{{ route('admin.holiday-calendar.index') }}" class="nav-item {{ request()->routeIs('admin.holiday-calendar*') ? 'active' : '' }}">
                        <i class="fa-solid fa-calendar-xmark"></i>
                        Holiday Calendar
                    </a>
                </div>
            </div>

            {{-- ── 3. People ── --}}
            @php 
                $peopleActive = request()->routeIs('admin.admissions*','admin.students*','admin.teachers*','admin.waiver-applications*'); 
                try { $pendingCount = \App\Models\AdmissionForm::where('status','PENDING')->count(); } catch(\Exception $e) { $pendingCount = 0; }
                try { $waiverPending = \App\Models\WaiverApplication::where('status','PENDING')->count(); } catch(\Exception) { $waiverPending = 0; }
                $peopleBadge = $pendingCount + $waiverPending;
            @endphp
            <div class="tree-group">
                <div class="tree-toggle {{ $peopleActive ? 'has-active open' : '' }}" onclick="treeToggle(this)">
                    <i class="fa-solid fa-users"></i>
                    People
                    @if($peopleBadge > 0)
                        <span class="nav-badge" style="margin-left:auto;margin-right:6px">{{ $peopleBadge }}</span>
                    @endif
                    <i class="fa-solid fa-chevron-right tree-toggle-arrow"></i>
                </div>
                <div class="tree-children {{ $peopleActive ? 'open' : '' }}">
                    <a href="{{ route('admin.admissions.index') }}" class="nav-item {{ request()->routeIs('admin.admissions*') ? 'active' : '' }}">
                        <i class="fa-solid fa-user-plus"></i>
                        Admissions
                        @if($pendingCount > 0)<span class="nav-badge">{{ $pendingCount }}</span>@endif
                    </a>
                    <a href="{{ route('admin.waiver-applications.index') }}" class="nav-item {{ request()->routeIs('admin.waiver-applications*') ? 'active' : '' }}">
                        <i class="fa-solid fa-hand-holding-dollar"></i>
                        Poor Fund / Waivers
                        @if($waiverPending > 0)<span class="nav-badge" style="background:#8b5cf6">{{ $waiverPending }}</span>@endif
                    </a>
                    <a href="{{ route('admin.students.index') }}" class="nav-item {{ request()->routeIs('admin.students*') ? 'active' : '' }}">
                        <i class="fa-solid fa-user-graduate"></i>
                        Students
                    </a>
                    <a href="{{ route('admin.teachers.index') }}" class="nav-item {{ request()->routeIs('admin.teachers*') ? 'active' : '' }}">
                        <i class="fa-solid fa-chalkboard-user"></i>
                        Teachers
                    </a>
                </div>
            </div>

            {{-- ── 4. Classes & Batches ── --}}
            @php $classesActive = request()->routeIs('admin.batches*','admin.classes*','admin.routine*'); @endphp
            <div class="tree-group">
                <div class="tree-toggle {{ $classesActive ? 'has-active open' : '' }}" onclick="treeToggle(this)">
                    <i class="fa-solid fa-video"></i>
                    Class &amp; Batch
                    <i class="fa-solid fa-chevron-right tree-toggle-arrow"></i>
                </div>
                <div class="tree-children {{ $classesActive ? 'open' : '' }}">
                    <a href="{{ route('admin.batches.index') }}" class="nav-item {{ request()->routeIs('admin.batches*') ? 'active' : '' }}">
                        <i class="fa-solid fa-layer-group"></i>
                        Batches
                    </a>
                    <a href="{{ route('admin.routine.index') }}" class="nav-item {{ request()->routeIs('admin.routine*') ? 'active' : '' }}">
                        <i class="fa-solid fa-calendar-week"></i>
                        Class Routine
                    </a>
                    <a href="{{ route('admin.classes.index') }}?date={{ today()->toDateString() }}" class="nav-item {{ request()->routeIs('admin.classes*') && request()->query('date') === today()->toDateString() ? 'active' : '' }}">
                        <i class="fa-solid fa-sun"></i>
                        Today's Classes
                        @php try { $adminTodayCount = \App\Models\ClassSession::whereDate('session_date', today())->count(); } catch (\Exception) { $adminTodayCount = 0; } @endphp
                        @if($adminTodayCount > 0)<span class="nav-badge" style="background:#f59e0b">{{ $adminTodayCount }}</span>@endif
                    </a>
                    <a href="{{ route('admin.classes.index') }}" class="nav-item {{ request()->routeIs('admin.classes*') && !request()->query('date') ? 'active' : '' }}">
                        <i class="fa-solid fa-list-check"></i>
                        All Classes
                    </a>
                </div>
            </div>

            {{-- ── 5. Exams & Results ── --}}
            @php $examsActive = request()->routeIs('admin.exams*','admin.questions*','admin.retakes*','admin.promotions*','admin.final-marks*'); @endphp
            <div class="tree-group">
                <div class="tree-toggle {{ $examsActive ? 'has-active open' : '' }}" onclick="treeToggle(this)">
                    <i class="fa-solid fa-file-signature"></i>
                    Exams &amp; Results
                    <i class="fa-solid fa-chevron-right tree-toggle-arrow"></i>
                </div>
                <div class="tree-children {{ $examsActive ? 'open' : '' }}">
                    <a href="{{ route('admin.exams.index') }}" class="nav-item {{ request()->routeIs('admin.exams*') ? 'active' : '' }}">
                        <i class="fa-solid fa-file-pen"></i>
                        Exams &amp; Results
                    </a>
                    <a href="{{ route('admin.questions.index') }}" class="nav-item {{ request()->routeIs('admin.questions*') ? 'active' : '' }}">
                        <i class="fa-solid fa-database"></i>
                        Question Bank
                    </a>
                    <a href="{{ route('admin.retakes.index') }}" class="nav-item {{ request()->routeIs('admin.retakes*') ? 'active' : '' }}">
                        <i class="fa-solid fa-rotate-left"></i>
                        Retakes
                    </a>
                    <a href="{{ route('admin.promotions.index') }}" class="nav-item {{ request()->routeIs('admin.promotions*') ? 'active' : '' }}">
                        <i class="fa-solid fa-arrow-trend-up"></i>
                        Promotions
                    </a>
                    <a href="{{ route('admin.final-marks.index') }}" class="nav-item {{ request()->routeIs('admin.final-marks*') ? 'active' : '' }}">
                        <i class="fa-solid fa-chart-pie"></i>
                        Final Mark Generator
                    </a>
                </div>
            </div>

            {{-- ── 6. Communication ── --}}
            @php $commActive = request()->routeIs('admin.notices*','admin.notifications*','admin.surveys*'); @endphp
            <div class="tree-group">
                <div class="tree-toggle {{ $commActive ? 'has-active open' : '' }}" onclick="treeToggle(this)">
                    <i class="fa-solid fa-bullhorn"></i>
                    Communication
                    <i class="fa-solid fa-chevron-right tree-toggle-arrow"></i>
                </div>
                <div class="tree-children {{ $commActive ? 'open' : '' }}">
                    <a href="{{ route('admin.surveys.index') }}" class="nav-item {{ request()->routeIs('admin.surveys*') ? 'active' : '' }}">
                        <i class="fa-solid fa-square-poll-vertical"></i>
                        Survey &amp; Forms
                    </a>
                    <a href="{{ route('admin.notifications.index') }}" class="nav-item {{ request()->routeIs('admin.notifications*') ? 'active' : '' }}">
                        <i class="fa-solid fa-paper-plane"></i>
                        Send Notification
                    </a>
                    <a href="{{ route('admin.notices.index') }}" class="nav-item {{ request()->routeIs('admin.notices*') ? 'active' : '' }}">
                        <i class="fa-solid fa-bell"></i>
                        Notice Board
                    </a>
                </div>
            </div>

            {{-- ── 7. Accounts & Financials ── --}}
            @php $accountsActive = request()->routeIs('admin.accounts*'); @endphp
            <div class="tree-group">
                <div class="tree-toggle {{ $accountsActive ? 'has-active open' : '' }}" onclick="treeToggle(this)">
                    <i class="fa-solid fa-wallet"></i>
                    Accounts &amp; Financials
                    <i class="fa-solid fa-chevron-right tree-toggle-arrow"></i>
                </div>
                <div class="tree-children {{ $accountsActive ? 'open' : '' }}">
                    <a href="{{ route('admin.accounts.dashboard') }}" class="nav-item {{ request()->routeIs('admin.accounts.dashboard') ? 'active' : '' }}">
                        <i class="fa-solid fa-hand-holding-dollar"></i>
                        Collect Fees
                    </a>

                    <a href="{{ route('admin.accounts.invoices') }}" class="nav-item {{ request()->routeIs('admin.accounts.invoices') ? 'active' : '' }}">
                        <i class="fa-solid fa-file-invoice-dollar"></i>
                        Invoices &amp; Dues
                    </a>
                    <a href="{{ route('admin.accounts.reports') }}" class="nav-item {{ request()->routeIs('admin.accounts.reports') ? 'active' : '' }}">
                        <i class="fa-solid fa-chart-column"></i>
                        Accounts Reports
                    </a>
                </div>
            </div>

            {{-- ── 7.5. Support & Helpdesk ── --}}
            @php $supportActive = request()->routeIs('admin.support-tickets*','admin.support-departments*','admin.support-agents*','support*'); @endphp
            <div class="tree-group">
                <div class="tree-toggle {{ $supportActive ? 'has-active open' : '' }}" onclick="treeToggle(this)">
                    <i class="fa-solid fa-headset"></i>
                    Support &amp; Helpdesk
                    @php
                        $pendingSupportCount = \App\Models\SupportTicket::where('status', 'PENDING')->count();
                    @endphp
                    @if($pendingSupportCount > 0)
                        <span class="nav-badge" style="background:#f59e0b">{{ $pendingSupportCount }}</span>
                    @endif
                    <i class="fa-solid fa-chevron-right tree-toggle-arrow"></i>
                </div>
                <div class="tree-children {{ $supportActive ? 'open' : '' }}">
                    <a href="{{ route('admin.support-tickets.index') }}" class="nav-item {{ request()->routeIs('admin.support-tickets*') ? 'active' : '' }}">
                        <i class="fa-solid fa-ticket"></i>
                        All Support Tickets
                    </a>
                    <a href="{{ route('admin.support-departments.index') }}" class="nav-item {{ request()->routeIs('admin.support-departments*','admin.support-agents*') ? 'active' : '' }}">
                        <i class="fa-solid fa-building-user"></i>
                        Departments &amp; Agents
                    </a>
                    <a href="{{ route('support.dashboard') }}" target="_blank" class="nav-item">
                        <i class="fa-solid fa-comments"></i>
                        Open Agent Live Chat ↗
                    </a>
                </div>
            </div>

            {{-- ── 8. System ── --}}
            @php $systemActive = request()->routeIs('admin.reports*','admin.settings*','admin.app-settings*','admin.fee-heads*'); @endphp
            <div class="tree-group">
                <div class="tree-toggle {{ $systemActive ? 'has-active open' : '' }}" onclick="treeToggle(this)">
                    <i class="fa-solid fa-gear"></i>
                    System
                    <i class="fa-solid fa-chevron-right tree-toggle-arrow"></i>
                </div>
                <div class="tree-children {{ $systemActive ? 'open' : '' }}">
                    <a href="{{ route('admin.reports.index') }}" class="nav-item {{ request()->routeIs('admin.reports*') ? 'active' : '' }}">
                        <i class="fa-solid fa-chart-line"></i>
                        Reports
                    </a>
                    <a href="{{ route('admin.fee-heads.index') }}" class="nav-item {{ request()->routeIs('admin.fee-heads*') ? 'active' : '' }}">
                        <i class="fa-solid fa-receipt"></i>
                        Fee Head
                    </a>
                    <a href="{{ route('admin.settings.index') }}" class="nav-item {{ request()->routeIs('admin.settings.index') ? 'active' : '' }}">
                        <i class="fa-solid fa-sliders"></i>
                        Settings
                    </a>
                    <a href="{{ route('admin.settings.notifications.index') }}" class="nav-item {{ request()->routeIs('admin.settings.notifications*') ? 'active' : '' }}">
                        <i class="fa-solid fa-bell-slash"></i>
                        Notification Settings
                    </a>
                    <a href="{{ route('admin.app-settings.index') }}" class="nav-item {{ request()->routeIs('admin.app-settings*') ? 'active' : '' }}">
                        <i class="fa-solid fa-mobile-screen"></i>
                        App Settings
                    </a>
                    <a href="{{ route('admin.settings.google-auth.index') }}" class="nav-item {{ request()->routeIs('admin.settings.google-auth*') ? 'active' : '' }}">
                        <i class="fa-brands fa-google" style="color:#ea4335"></i>
                        Google Auth Setup
                    </a>
                    <a href="{{ route('admin.support-departments.index') }}" class="nav-item {{ request()->routeIs('admin.support-departments*','admin.support-agents*') ? 'active' : '' }}">
                        <i class="fa-solid fa-life-ring"></i>
                        Support Setup
                    </a>
                </div>
            </div>

        </nav>
    </aside>

    <!-- ═══ MAIN AREA ═══ -->
    <div class="main-area">
        <!-- Topbar -->
        <header class="topbar">
            <div class="topbar-left">
                <button class="topbar-btn" onclick="toggleSidebar()" title="Toggle Sidebar">
                    <i class="fa-solid fa-bars"></i>
                </button>
                @if(isset($breadcrumbs))
                <div class="topbar-breadcrumb">
                    <a href="{{ route('admin.dashboard') }}">Admin</a>
                    @foreach($breadcrumbs as $bc)
                        <span>›</span>
                        @if(!$loop->last)<a href="{{ $bc['url'] }}">{{ $bc['label'] }}</a>
                        @else<span style="color:var(--text-secondary)">{{ $bc['label'] }}</span>@endif
                    @endforeach
                </div>
                @endif
            </div>
            <div class="topbar-right">
                <div class="dropdown">
                    <button class="topbar-btn" onclick="toggleDropdown('adminNotif')" title="Notifications">
                        <i class="fa-solid fa-bell"></i>
                        <span class="notif-dot"></span>
                    </button>
                    <div class="dropdown-menu" id="adminNotif" style="min-width:260px;right:0">
                        <div style="padding:11px 14px;border-bottom:1px solid var(--card-border);font-size:13px;font-weight:600">Notifications</div>
                        <div style="padding:20px;text-align:center;font-size:12px;color:var(--text-muted)">No new notifications</div>
                    </div>
                </div>
                <div class="dropdown">
                    <div class="user-menu" onclick="toggleDropdown('adminUserMenu')" style="cursor:pointer">
                        <div class="user-avatar" style="background:linear-gradient(135deg,#047857,#064e3b);border:2px solid #a7f3d0">
                            {{ strtoupper(substr(auth()->user()->name ?? 'A', 0, 2)) }}
                        </div>
                        <div>
                            <div class="user-name">{{ auth()->user()->name ?? 'Admin' }}</div>
                            <div class="user-role" style="color:#047857">
                                {{ match(strtoupper(auth()->user()->role ?? 'ADMIN')) {
                                    'SUPER_ADMIN' => 'Super Admin',
                                    'ADMIN'       => 'Administrator',
                                    'TEACHER'     => 'Teacher',
                                    'STUDENT'     => 'Student',
                                    'SUPPORT'     => 'Support Agent',
                                    'ACCOUNTS'    => 'Accounts Officer',
                                    default       => ucfirst(strtolower(auth()->user()->role ?? 'User')),
                                } }}
                            </div>
                        </div>
                        <i class="fa-solid fa-chevron-down" style="font-size:11px;opacity:0.7"></i>
                    </div>
                    <div class="dropdown-menu" id="adminUserMenu">
                        <a href="{{ route('admin.settings.index') }}" class="dropdown-item">
                            <i class="fa-solid fa-gear"></i>
                            Settings
                        </a>
                        <div class="dropdown-divider"></div>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="dropdown-item danger" style="width:100%;border:none;background:none;text-align:left">
                                <i class="fa-solid fa-right-from-bracket"></i>
                                Logout
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </header>

        @if(session('success'))
        <div style="padding:12px 24px 0">
            <div class="alert alert-success" id="lp-flash">{{ session('success') }}</div>
        </div>
        @endif
        @if(session('error'))
        <div style="padding:12px 24px 0">
            <div class="alert alert-danger" id="lp-flash">{{ session('error') }}</div>
        </div>
        @endif

        <main class="page-content">{{ $slot }}</main>
    </div>
</div>

<script>
function toggleSidebar(){
    const s = document.getElementById('sidebar');
    const w = document.querySelector('.app-wrapper');
    const o = document.getElementById('sidebarOverlay');
    if (window.innerWidth <= 768) {
        s.classList.toggle('open');
        if (o) o.classList.toggle('active');
    } else {
        s.classList.toggle('collapsed');
        if (w) w.classList.toggle('sidebar-collapsed');
    }
}

function toggleDropdown(id){
    const m=document.getElementById(id),open=m.classList.contains('open');
    document.querySelectorAll('.dropdown-menu.open').forEach(x=>x.classList.remove('open'));
    if(!open) {
        m.classList.add('open');
        const rect = m.getBoundingClientRect();
        const winHeight = window.innerHeight || document.documentElement.clientHeight;
        if (rect.bottom > winHeight - 10 && rect.top > rect.height) {
            m.style.top = 'auto';
            m.style.bottom = 'calc(100% + 6px)';
        } else {
            m.style.top = 'calc(100% + 6px)';
            m.style.bottom = 'auto';
        }
    }
}
document.addEventListener('click',function(e){
    if(!e.target.closest('.dropdown')) document.querySelectorAll('.dropdown-menu.open').forEach(x=>x.classList.remove('open'));
});

/* ── Tree toggle ── */
function treeToggle(header){
    const isOpen = header.classList.contains('open');
    const children = header.nextElementSibling;
    if(isOpen){
        header.classList.remove('open');
        children.classList.remove('open');
    } else {
        header.classList.add('open');
        children.classList.add('open');
    }
}

function openModal(id){ document.getElementById(id).classList.add('open'); document.body.style.overflow='hidden'; }
function closeModal(id){ document.getElementById(id).classList.remove('open'); document.body.style.overflow=''; }
document.addEventListener('click',function(e){ if(e.target.classList.contains('modal-overlay')){ e.target.classList.remove('open'); document.body.style.overflow=''; } });
document.addEventListener('keydown',function(e){ if(e.key==='Escape') document.querySelectorAll('.modal-overlay.open').forEach(m=>{ m.classList.remove('open'); document.body.style.overflow=''; }); });
const lf=document.getElementById('lp-flash');
if(lf) setTimeout(()=>{ lf.style.opacity='0'; lf.style.transition='opacity .4s'; setTimeout(()=>lf.remove(),400); },4000);

/* ── Global Page Loader Logic ── */
function showLoader(){
    const loader = document.getElementById('globalPageLoader');
    const bar = document.getElementById('globalTopBar');
    if(loader) loader.classList.add('active');
    if(bar) bar.classList.add('active');
}
function hideLoader(){
    const loader = document.getElementById('globalPageLoader');
    const bar = document.getElementById('globalTopBar');
    if(loader) loader.classList.remove('active');
    if(bar) bar.classList.remove('active');
}
window.addEventListener('pageshow', function(){ hideLoader(); });
document.addEventListener('DOMContentLoaded', function(){
    hideLoader();
    document.addEventListener('click', function(e){
        const a = e.target.closest('a');
        if(a && a.href && !a.href.startsWith('javascript:') && !a.href.includes('#') && a.target !== '_blank' && !a.hasAttribute('download')){
            showLoader();
        }
    });
    document.addEventListener('submit', function(e){
        if (e.defaultPrevented || e.target.hasAttribute('data-ajax') || e.target.closest('.modal') || e.target.closest('.modal-overlay')) {
            return;
        }
        showLoader();
    });
});
if(window.fetch){
    const origFetch = window.fetch;
    window.fetch = function(){
        const bar = document.getElementById('globalTopBar');
        if(bar) bar.classList.add('active');
        return origFetch.apply(this, arguments).finally(function(){
            if(bar) bar.classList.remove('active');
        });
    };
}
function togglePasswordVisibility(inputId, btn) {
    const input = document.getElementById(inputId);
    const eyeShow = btn.querySelector('.eye-show');
    const eyeHide = btn.querySelector('.eye-hide');
    if (input.type === 'password') {
        input.type = 'text';
        if (eyeShow) eyeShow.style.display = 'none';
        if (eyeHide) eyeHide.style.display = 'inline-block';
    } else {
        input.type = 'password';
        if (eyeShow) eyeShow.style.display = 'inline-block';
        if (eyeHide) eyeHide.style.display = 'none';
    }
}
</script>
<x-fcm-initializer />
@stack('scripts')
</body>
</html>
