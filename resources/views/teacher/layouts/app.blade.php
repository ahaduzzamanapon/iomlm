<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Dashboard' }} — Teacher | IOM</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <style>
    /* ═══ Official IOM Brand Color Theme (Teacher Portal) ═══ */
    :root {
        --iom-green:     #047857;   /* Official IOM Primary Brand Emerald Green */
        --iom-dark:      #064e3b;   /* Official IOM Deep Forest Green */
        --iom-deep-dark: #022c22;   /* Islamic Dark Midnight Background */
        --iom-light:     #ecfdf5;   /* Official IOM Mint Light */
        --iom-mint:      #d1fae5;   /* Mint Border Line */
        --iom-gold:      #fbbf24;   /* Official IOM Accent Gold */

        --blue: #047857;
        --blue-dark: #064e3b;
        --primary: #047857;
        --primary-dark: #064e3b;
        --sidebar-bg: #064e3b;
        --sidebar-active-bg: rgba(4, 120, 87, 0.45);
        --sidebar-active: #34d399;
    }

    body, input, select, textarea, button, .tree-toggle, .nav-item, .table, .card, h1, h2, h3, h4, h5, h6 {
        font-family: 'Kalpurush', 'Plus Jakarta Sans', 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    }

    /* ─── Teacher Sidebar: IOM Deep Forest Green Gradient ────────── */
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
    .sidebar-logo-icon {
        background: linear-gradient(135deg, #047857, #064e3b) !important;
    }
    .nav-group-label {
        color: #ffffff !important;
        opacity: 0.92;
        font-weight: 700;
        letter-spacing: 1.2px;
    }

    /* ─── Navigation Items: Pure White Text ────────────────────── */
    .nav-item {
        color: #ffffff !important;
    }
    .nav-item:hover {
        background: rgba(255, 255, 255, 0.12) !important;
        color: #ffffff !important;
    }
    .nav-item.active {
        background: linear-gradient(135deg, rgba(4, 120, 87, 0.85), rgba(5, 150, 105, 0.65)) !important;
        color: #ffffff !important;
        border: 1px solid rgba(52, 211, 153, 0.5) !important;
        box-shadow: 0 4px 14px rgba(2, 44, 34, 0.35) !important;
    }
    .nav-item i, .nav-item svg {
        color: #ffffff !important;
    }
    .nav-item.active i, .nav-item.active svg {
        color: #ffffff !important;
    }

    /* ─── Teacher Topbar & Profile ─────────────────────────────── */
    .topbar {
        background: rgba(255, 255, 255, 0.96) !important;
        border-bottom: 1px solid #d1fae5 !important;
    }
    .user-avatar {
        background: linear-gradient(135deg, #047857, #064e3b) !important;
        border: 2px solid #a7f3d0 !important;
        color: #ffffff !important;
    }
    .user-role {
        color: #047857 !important;
        font-weight: 700 !important;
    }

    /* ─── Buttons & Controls in IOM Brand Theme ────────────────── */
    .btn-primary, button.btn-primary, a.btn-primary {
        background: linear-gradient(135deg, #047857 0%, #064e3b 100%) !important;
        border: none !important;
        color: #ffffff !important;
        box-shadow: 0 4px 14px rgba(4, 120, 87, 0.3) !important;
    }
    .btn-primary:hover {
        background: linear-gradient(135deg, #064e3b 0%, #022c22 100%) !important;
        color: #ffffff !important;
    }
    .badge-primary {
        background: #047857 !important;
        color: #fff !important;
    }
    .loader-top-bar {
        background: linear-gradient(90deg, #047857, #34d399, #fbbf24) !important;
    }
    .loader-ring {
        border-top-color: #047857 !important;
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
        <div class="loader-label">Loading Teacher Portal...</div>
    </div>
</div>

<div class="app-wrapper">
    <div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>

    <!-- ═══ TEACHER SIDEBAR ═══ -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-logo" style="gap:12px">
            <img src="{{ asset('images/logo.png') }}" alt="IOM Logo" style="width:38px;height:38px;object-fit:contain">
            <div class="sidebar-logo-text">
                <span class="sidebar-logo-name">Teacher Portal</span>
                <span class="sidebar-logo-sub">IOM ERP</span>
            </div>
        </div>

        <nav class="sidebar-nav">

            <!-- Overview -->
            <div class="nav-group-label">Overview</div>
            <a href="{{ route('teacher.dashboard') }}" class="nav-item {{ request()->routeIs('teacher.dashboard') ? 'active' : '' }}">
                <i class="fa-solid fa-gauge-high"></i>
                Dashboard
            </a>

            <!-- My Schedule -->
            <div class="nav-group-label">My Schedule</div>
            <a href="{{ route('teacher.classes.today') }}" class="nav-item {{ request()->routeIs('teacher.classes.today') ? 'active' : '' }}" style="{{ request()->routeIs('teacher.classes.today') ? '' : 'border-left:3px solid #f59e0b' }}">
                <i class="fa-solid fa-sun"></i>
                Today's Classes
                @php
                    try {
                        $teacher = \App\Models\Teacher::where('email', auth()->user()->email)->first();
                        $todayCount = $teacher ? \App\Models\ClassSession::where('teacher_id', $teacher->id)->whereDate('session_date', today())->count() : 0;
                    } catch (\Exception) { $todayCount = 0; }
                @endphp
                @if($todayCount > 0)<span class="nav-badge" style="background:#f59e0b">{{ $todayCount }}</span>@endif
            </a>
            <a href="{{ route('teacher.classes.index') }}" class="nav-item {{ request()->routeIs('teacher.classes.index') ? 'active' : '' }}">
                <i class="fa-solid fa-video"></i>
                All Classes
            </a>
            <a href="{{ route('teacher.calendar') }}" class="nav-item {{ request()->routeIs('teacher.calendar*') ? 'active' : '' }}">
                <i class="fa-solid fa-calendar-days"></i>
                Calendar
            </a>
            <a href="{{ route('teacher.routine.index') }}" class="nav-item {{ request()->routeIs('teacher.routine*') ? 'active' : '' }}">
                <i class="fa-solid fa-calendar-week"></i>
                My Routine
            </a>

            <!-- Teaching -->
            <div class="nav-group-label">Teaching</div>
            <a href="{{ route('teacher.subjects.index') }}" class="nav-item {{ request()->routeIs('teacher.subjects*') ? 'active' : '' }}">
                <i class="fa-solid fa-book-bookmark"></i>
                My Subjects
            </a>
            <a href="{{ route('teacher.attendance.index') }}" class="nav-item {{ request()->routeIs('teacher.attendance*') ? 'active' : '' }}">
                <i class="fa-solid fa-clipboard-user"></i>
                Mark Attendance
            </a>
            <a href="{{ route('teacher.students.index') }}" class="nav-item {{ request()->routeIs('teacher.students*') ? 'active' : '' }}">
                <i class="fa-solid fa-user-graduate"></i>
                My Students
            </a>

            <!-- Assessments -->
            <div class="nav-group-label">Assessments</div>
            <a href="{{ route('teacher.exams.index') }}" class="nav-item {{ request()->routeIs('teacher.exams*') ? 'active' : '' }}">
                <i class="fa-solid fa-file-pen"></i>
                Exams
            </a>
            <a href="{{ route('teacher.results.index') }}" class="nav-item {{ request()->routeIs('teacher.results*') ? 'active' : '' }}">
                <i class="fa-solid fa-square-poll-horizontal"></i>
                Submit Results
            </a>
            <a href="{{ route('teacher.resources.index') }}" class="nav-item {{ request()->routeIs('teacher.resources*') ? 'active' : '' }}">
                <i class="fa-solid fa-paperclip"></i>
                Learning Resources
            </a>

            <!-- Communication -->
            <div class="nav-group-label">Communication</div>
            <a href="{{ route('teacher.notices.index') }}" class="nav-item {{ request()->routeIs('teacher.notices*') ? 'active' : '' }}">
                <i class="fa-solid fa-bell"></i>
                Notice Board
            </a>

        </nav>
    </aside>

    <!-- ═══ MAIN AREA ═══ -->
    <div class="main-area">
        <header class="topbar">
            <div class="topbar-left">
                <button class="topbar-btn" onclick="toggleSidebar()">
                    <i class="fa-solid fa-bars"></i>
                </button>
                @if(isset($breadcrumbs))
                <div class="topbar-breadcrumb">
                    <a href="{{ route('teacher.dashboard') }}">Teacher</a>
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
                    <div class="user-menu" onclick="toggleDropdown('teacherUserMenu')" style="cursor:pointer">
                        <div class="user-avatar">{{ strtoupper(substr(auth()->user()->name ?? 'T', 0, 2)) }}</div>
                        <div>
                            <div class="user-name">{{ auth()->user()->name ?? 'Teacher' }}</div>
                            <div class="user-role">
                                {{ match(strtoupper(auth()->user()->role ?? 'TEACHER')) {
                                    'TEACHER' => 'Teacher',
                                    'ADMIN', 'SUPER_ADMIN' => 'Admin (Teacher View)',
                                    default => ucfirst(strtolower(auth()->user()->role ?? 'Teacher')),
                                } }}
                            </div>
                        </div>
                        <i class="fa-solid fa-chevron-down" style="font-size:11px;opacity:0.7"></i>
                    </div>
                    <div class="dropdown-menu" id="teacherUserMenu">
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
        <div style="padding:12px 24px 0"><div class="alert alert-success" id="lp-flash">{{ session('success') }}</div></div>
        @endif
        @if(session('error'))
        <div style="padding:12px 24px 0"><div class="alert alert-danger" id="lp-flash">{{ session('error') }}</div></div>
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
function toggleDropdown(id){ const m=document.getElementById(id),o=m.classList.contains('open'); document.querySelectorAll('.dropdown-menu.open').forEach(x=>x.classList.remove('open')); if(!o){ m.classList.add('open'); const r=m.getBoundingClientRect(),wh=window.innerHeight||document.documentElement.clientHeight; if(r.bottom>wh-10&&r.top>r.height){ m.style.top='auto'; m.style.bottom='calc(100% + 6px)'; }else{ m.style.top='calc(100% + 6px)'; m.style.bottom='auto'; } } }
document.addEventListener('click',function(e){ if(!e.target.closest('.dropdown')) document.querySelectorAll('.dropdown-menu.open').forEach(x=>x.classList.remove('open')); });
function openModal(id){ document.getElementById(id).classList.add('open'); document.body.style.overflow='hidden'; }
function closeModal(id){ document.getElementById(id).classList.remove('open'); document.body.style.overflow=''; }
document.addEventListener('click',function(e){ if(e.target.classList.contains('modal-overlay')){ e.target.classList.remove('open'); document.body.style.overflow=''; } });
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
</script>
<x-fcm-initializer />
@stack('scripts')
</body>
</html>
