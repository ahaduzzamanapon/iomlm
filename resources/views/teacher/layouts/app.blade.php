<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Dashboard' }} — Teacher | IOM</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <style>
        /* Teacher-specific accent: green */
        .sidebar { --sidebar-active-bg: #064e3b; --sidebar-active: #34d399; }
        .sidebar-logo-icon { background: linear-gradient(135deg, #10b981, #059669) !important; }
        .user-avatar { background: linear-gradient(135deg, #10b981, #059669) !important; }
        .user-role { color: #10b981 !important; }
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
                <span class="sidebar-logo-name">Teacher Panel</span>
                <span class="sidebar-logo-sub">IOM ERP</span>
            </div>
        </div>

        <nav class="sidebar-nav">

            <!-- Overview -->
            <div class="nav-group-label">Overview</div>
            <a href="{{ route('teacher.dashboard') }}" class="nav-item {{ request()->routeIs('teacher.dashboard') ? 'active' : '' }}">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                Dashboard
            </a>

            <!-- My Schedule -->
            <div class="nav-group-label">My Schedule</div>
            <a href="{{ route('teacher.classes.today') }}" class="nav-item {{ request()->routeIs('teacher.classes.today') ? 'active' : '' }}" style="{{ request()->routeIs('teacher.classes.today') ? '' : 'border-left:3px solid #f59e0b' }}">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3v1m0 16v1M4.22 4.22l.707.707M18.364 18.364l.707.707M1 12h1m18 0h1M4.22 19.778l.707-.707M18.364 5.636l.707-.707M12 8a4 4 0 100 8 4 4 0 000-8z"/></svg>
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
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10l4.553-2.069A1 1 0 0121 8.87v6.26a1 1 0 01-1.447.894L15 14M3 8a2 2 0 012-2h8a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2V8z"/></svg>
                All Classes
            </a>
            <a href="{{ route('teacher.calendar') }}" class="nav-item {{ request()->routeIs('teacher.calendar*') ? 'active' : '' }}">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                Calendar
            </a>
            <a href="{{ route('teacher.routine.index') }}" class="nav-item {{ request()->routeIs('teacher.routine*') ? 'active' : '' }}">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M3 14h18M3 6h18M3 18h18"/></svg>
                My Routine
            </a>

            <!-- Teaching -->
            <div class="nav-group-label">Teaching</div>
            <a href="{{ route('teacher.subjects.index') }}" class="nav-item {{ request()->routeIs('teacher.subjects*') ? 'active' : '' }}">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                My Subjects
            </a>
            <a href="{{ route('teacher.attendance.index') }}" class="nav-item {{ request()->routeIs('teacher.attendance*') ? 'active' : '' }}">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                Mark Attendance
            </a>
            <a href="{{ route('teacher.students.index') }}" class="nav-item {{ request()->routeIs('teacher.students*') ? 'active' : '' }}">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                My Students
            </a>

            <!-- Assessments -->
            <div class="nav-group-label">Assessments</div>
            <a href="{{ route('teacher.exams.index') }}" class="nav-item {{ request()->routeIs('teacher.exams*') ? 'active' : '' }}">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                Exams
            </a>
            <a href="{{ route('teacher.results.index') }}" class="nav-item {{ request()->routeIs('teacher.results*') ? 'active' : '' }}">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                Submit Results
            </a>
            <a href="{{ route('teacher.resources.index') }}" class="nav-item {{ request()->routeIs('teacher.resources*') ? 'active' : '' }}">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
                Learning Resources
            </a>

        </nav>
    </aside>

    <!-- ═══ MAIN AREA ═══ -->
    <div class="main-area">
        <header class="topbar">
            <div class="topbar-left">
                <button class="topbar-btn" onclick="toggleSidebar()">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
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
                            <div class="user-role">Teacher</div>
                        </div>
                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                    </div>
                    <div class="dropdown-menu" id="teacherUserMenu">
                        <div class="dropdown-divider"></div>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="dropdown-item danger" style="width:100%;border:none;background:none;text-align:left">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                                Logout
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </header>

        @if(session('success'))
        <div style="padding:12px 24px 0"><div class="alert alert-success" id="lp-flash">✓ {{ session('success') }}</div></div>
        @endif
        @if(session('error'))
        <div style="padding:12px 24px 0"><div class="alert alert-danger" id="lp-flash">✕ {{ session('error') }}</div></div>
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
