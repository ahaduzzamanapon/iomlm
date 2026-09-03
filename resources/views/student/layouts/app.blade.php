<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Dashboard' }} — Student | IOM</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <style>
        /* Student-specific accent: violet */
        .sidebar { --sidebar-active-bg: #2e1065; --sidebar-active: #a78bfa; }
        .sidebar-logo-icon { background: linear-gradient(135deg, #8b5cf6, #ec4899) !important; }
        .user-avatar { background: linear-gradient(135deg, #8b5cf6, #ec4899) !important; }
        .user-role { color: #8b5cf6 !important; }
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
        <div class="loader-label">Loading Student Portal...</div>
    </div>
</div>

<div class="app-wrapper">
    <div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>

    <!-- ═══ STUDENT SIDEBAR ═══ -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-logo" style="gap:12px">
            <img src="{{ asset('images/logo.png') }}" alt="IOM Logo" style="width:38px;height:38px;object-fit:contain">
            <div class="sidebar-logo-text">
                <span class="sidebar-logo-name">Student Portal</span>
                <span class="sidebar-logo-sub">IOM ERP</span>
            </div>
        </div>

        <nav class="sidebar-nav">

            <!-- Overview -->
            <div class="nav-group-label">Overview</div>
            <a href="{{ route('student.dashboard') }}" class="nav-item {{ request()->routeIs('student.dashboard') ? 'active' : '' }}">
                <i class="fa-solid fa-house"></i>
                Dashboard
            </a>

            <!-- My Learning -->
            <div class="nav-group-label">My Learning</div>
            <a href="{{ route('student.my-course.index') }}" class="nav-item {{ request()->routeIs('student.my-course*') ? 'active' : '' }}">
                <i class="fa-solid fa-book-open"></i>
                My Course
            </a>
            <a href="{{ route('student.classes.today') }}" class="nav-item {{ request()->routeIs('student.classes.today') ? 'active' : '' }}" style="{{ !request()->routeIs('student.classes.today') ? 'border-left:3px solid #f59e0b' : '' }}">
                <i class="fa-solid fa-sun"></i>
                Today's Classes
                @php
                    try {
                        $student = \App\Models\Student::where('email', auth()->user()->email)->first();
                        $batchIds = $student ? \App\Models\Enrollment::where('student_id', $student->id)->where('status','ACTIVE')->pluck('batch_id') : collect();
                        $todayClassCount = \App\Models\ClassSession::whereIn('batch_id', $batchIds)->whereDate('session_date', today())->count();
                    } catch (\Exception) { $todayClassCount = 0; }
                @endphp
                @if($todayClassCount > 0)<span class="nav-badge" style="background:#f59e0b">{{ $todayClassCount }}</span>@endif
            </a>
            <a href="{{ route('student.classes.index') }}" class="nav-item {{ request()->routeIs('student.classes.index') ? 'active' : '' }}">
                <i class="fa-solid fa-video"></i>
                My Classes
            </a>
            <a href="{{ route('student.calendar') }}" class="nav-item {{ request()->routeIs('student.calendar*') ? 'active' : '' }}">
                <i class="fa-solid fa-calendar-days"></i>
                My Calendar
            </a>
            <a href="{{ route('student.routine.index') }}" class="nav-item {{ request()->routeIs('student.routine*') ? 'active' : '' }}">
                <i class="fa-solid fa-calendar-week"></i>
                My Routine
            </a>
            <a href="{{ route('student.resources.index') }}" class="nav-item {{ request()->routeIs('student.resources*') ? 'active' : '' }}">
                <i class="fa-solid fa-paperclip"></i>
                Learning Resources
            </a>

            <!-- Progress -->
            <div class="nav-group-label">My Progress &amp; Exams</div>
            <a href="{{ route('student.exams.index') }}" class="nav-item {{ request()->routeIs('student.exams*') ? 'active' : '' }}">
                <i class="fa-solid fa-file-pen"></i>
                Online Exams
            </a>
            <a href="{{ route('student.attendance.index') }}" class="nav-item {{ request()->routeIs('student.attendance*') ? 'active' : '' }}">
                <i class="fa-solid fa-clipboard-check"></i>
                My Attendance
            </a>
            <a href="{{ route('student.results.index') }}" class="nav-item {{ request()->routeIs('student.results*') ? 'active' : '' }}">
                <i class="fa-solid fa-award"></i>
                Academic Results
            </a>

            <!-- Financials -->
            <div class="nav-group-label">Financials</div>
            <a href="{{ route('student.fees.index') }}" class="nav-item {{ request()->routeIs('student.fees*') ? 'active' : '' }}">
                <i class="fa-solid fa-credit-card"></i>
                My Fees &amp; Invoices
            </a>

            <!-- Support -->
            <div class="nav-group-label">Support &amp; Help</div>
            <a href="{{ route('student.support.index') }}" class="nav-item {{ request()->routeIs('student.support*') ? 'active' : '' }}">
                <i class="fa-solid fa-headset"></i>
                Online Support
            </a>

            <!-- Documents -->
            <div class="nav-group-label">Documents</div>
            <a href="{{ route('student.documents.index') }}" class="nav-item {{ request()->routeIs('student.documents*') ? 'active' : '' }}">
                <i class="fa-solid fa-folder-open"></i>
                My Documents
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
                    <a href="{{ route('student.dashboard') }}">Student</a>
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
                    <div class="user-menu" onclick="toggleDropdown('studentUserMenu')" style="cursor:pointer">
                        <div class="user-avatar">{{ strtoupper(substr(auth()->user()->name ?? 'S', 0, 2)) }}</div>
                        <div>
                            <div class="user-name">{{ auth()->user()->name ?? 'Student' }}</div>
                            <div class="user-role">
                                {{ match(strtoupper(auth()->user()->role ?? 'STUDENT')) {
                                    'STUDENT' => 'Student',
                                    'ADMIN', 'SUPER_ADMIN' => 'Admin (Student View)',
                                    default => ucfirst(strtolower(auth()->user()->role ?? 'Student')),
                                } }}
                            </div>
                        </div>
                        <i class="fa-solid fa-chevron-down" style="font-size:11px;opacity:0.7"></i>
                    </div>
                    <div class="dropdown-menu" id="studentUserMenu">
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
