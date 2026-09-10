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
    /* ═══ Official IOM Brand Color Theme (Student Portal) ═══ */
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

    /* ─── Student Sidebar: IOM Deep Forest Green Gradient ────────── */
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

    /* ─── Student Topbar & Profile ─────────────────────────────── */
    .topbar {
        background: rgba(255, 255, 255, 0.96) !important;
        border-bottom: 1px solid #d1fae5 !important;
    }
    .user-avatar {
        background: #f1f5f9 !important;
        border: 2px solid #a7f3d0 !important;
        width: 36px !important;
        height: 36px !important;
        border-radius: 50% !important;
        object-fit: cover !important;
        display: inline-block !important;
        flex-shrink: 0 !important;
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
            @php
                $sidebarStudent = \App\Models\Student::where('email', auth()->user()?->email)->first();
                $sidebarPct = $sidebarStudent ? ($sidebarStudent->profile_completed_percent ?? 0) : 0;
            @endphp
            <a href="{{ route('student.profile.index') }}" class="nav-item {{ request()->routeIs('student.profile.*') ? 'active' : '' }}" style="{{ $sidebarPct < 95 ? 'border-left:3px solid #fbbf24' : '' }}">
                <i class="fa-solid fa-user-check"></i>
                আমার প্রোফাইল
                @if($sidebarPct < 95)
                    <span class="nav-badge" style="background:#ef4444;color:#fff;font-size:11px">{{ $sidebarPct }}%</span>
                @else
                    <span class="nav-badge" style="background:#10b981;color:#fff;font-size:11px">১০০%</span>
                @endif
            </a>

            <!-- My Learning -->
            <div class="nav-group-label">My Learning</div>
            <a href="{{ route('student.my-course.index') }}" class="nav-item {{ request()->routeIs('student.my-course*') ? 'active' : '' }}">
                <i class="fa-solid fa-book-open"></i>
                My Course
            </a>
            <a href="{{ route('student.course-transfers.index') }}" class="nav-item {{ request()->routeIs('student.course-transfers*') ? 'active' : '' }}">
                <i class="fa-solid fa-arrow-right-arrow-left"></i>
                কোর্স পরিবর্তন (Transfer)
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
                        @php
                            $stUser = auth()->user();
                            $stRecord = \App\Models\Student::where('user_id', $stUser?->id)
                                ->orWhere('email', $stUser?->email)
                                ->first();
                            $stPhoto = $stRecord?->photo_url;
                        @endphp
                        @if(!empty($stPhoto))
                            <img src="{{ asset($stPhoto) }}" alt="Avatar" class="user-avatar" onerror="this.onerror=null;this.src='{{ asset('images/default-avatar.svg') }}'">
                        @else
                            <img src="{{ asset('images/default-avatar.svg') }}" alt="Avatar" class="user-avatar">
                        @endif
                        <div>
                            <div class="user-name">{{ $stUser->name ?? 'Student' }}</div>
                            <div class="user-role">
                                {{ match(strtoupper($stUser->role ?? 'STUDENT')) {
                                    'STUDENT' => 'Student',
                                    'ADMIN', 'SUPER_ADMIN' => 'Admin (Student View)',
                                    default => ucfirst(strtolower($stUser->role ?? 'Student')),
                                } }}
                            </div>
                        </div>
                        <i class="fa-solid fa-chevron-down" style="font-size:11px;opacity:0.7"></i>
                    </div>
                    <div class="dropdown-menu" id="studentUserMenu">
                        <a href="{{ route('student.profile.index') }}" class="dropdown-item" style="display:flex;align-items:center;gap:8px;padding:8px 14px;color:var(--text);text-decoration:none;font-weight:600">
                            <i class="fa-solid fa-user-gear" style="color:#047857"></i>
                            আমার প্রোফাইল ({{ $sidebarPct ?? 0 }}%)
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
let loaderTimer = null;

function isDownloadUrl(url) {
    if (!url) return false;
    const lower = url.toLowerCase();
    const downloadKeywords = [
        'download', 'export', 'template', 'sample', 'backup', 'aiken',
        '.csv', '.xlsx', '.xls', '.pdf', '.zip', '.txt', '.doc', '.docx'
    ];
    return downloadKeywords.some(kw => lower.includes(kw));
}

function showLoader(isDownload = false){
    const bar = document.getElementById('globalTopBar');
    const loader = document.getElementById('globalPageLoader');
    
    if (loaderTimer) {
        clearTimeout(loaderTimer);
        loaderTimer = null;
    }
    
    if (isDownload) {
        if (bar) bar.classList.add('active');
        loaderTimer = setTimeout(hideLoader, 2500);
        return;
    }
    
    if (loader) loader.classList.add('active');
    if (bar) bar.classList.add('active');
    
    // Safety auto-dismiss: unfreeze after 3.5s so downloads or slow requests never permanently block the screen
    loaderTimer = setTimeout(hideLoader, 3500);
}

function hideLoader(){
    if (loaderTimer) {
        clearTimeout(loaderTimer);
        loaderTimer = null;
    }
    const loader = document.getElementById('globalPageLoader');
    const bar = document.getElementById('globalTopBar');
    if(loader) loader.classList.remove('active');
    if(bar) bar.classList.remove('active');
}

window.addEventListener('pageshow', hideLoader);
window.addEventListener('focus', function(){ setTimeout(hideLoader, 500); });
window.addEventListener('blur', function(){ setTimeout(hideLoader, 1500); });

document.addEventListener('DOMContentLoaded', function(){
    hideLoader();
    
    const loaderEl = document.getElementById('globalPageLoader');
    if (loaderEl) {
        loaderEl.style.cursor = 'pointer';
        loaderEl.setAttribute('title', 'ক্লিক করে বন্ধ করুন (Click to dismiss)');
        loaderEl.addEventListener('click', hideLoader);
    }
    
    document.addEventListener('keydown', function(e){
        if (e.key === 'Escape') hideLoader();
    });
    
    document.addEventListener('click', function(e){
        const a = e.target.closest('a');
        if (!a || !a.href || a.href.startsWith('javascript:') || a.href.includes('#') || a.target !== '_blank') {
            return;
        }
        if (a.hasAttribute('download') || a.hasAttribute('data-no-loader') || a.classList.contains('no-loader') || a.closest('.no-loader')) {
            showLoader(true);
            return;
        }
        if (isDownloadUrl(a.href)) {
            showLoader(true);
            return;
        }
        showLoader(false);
    });
    
    document.addEventListener('submit', function(e){
        if (e.defaultPrevented || e.target.hasAttribute('data-ajax') || e.target.closest('.modal') || e.target.closest('.modal-overlay')) {
            return;
        }
        const action = e.target.getAttribute('action') || '';
        const isExportForm = isDownloadUrl(action) || e.target.querySelector('button[name="export"], input[name="export"], [data-action="export"]');
        if (isExportForm) {
            showLoader(true);
            return;
        }
        showLoader(false);
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

@php
    $portalStudent = \App\Models\Student::where('email', auth()->user()?->email)->first();
    $isProfileIncomplete = $portalStudent && !$portalStudent->isProfileCompleted();
@endphp

@if($isProfileIncomplete && !request()->routeIs('student.profile.*'))
<!-- ═══ MANDATORY PROFILE COMPLETION POPUP MODAL ═══ -->
<div id="profileCompletionModal" style="position:fixed;inset:0;background:rgba(2,44,34,0.88);backdrop-filter:blur(8px);z-index:999999;display:flex;align-items:center;justify-content:center;padding:20px;font-family:'Kalpurush',sans-serif;">
    <div style="background:#ffffff;border-radius:20px;max-width:520px;width:100%;box-shadow:0 25px 50px -12px rgba(0,0,0,0.6);overflow:hidden;border:2px solid #34d399;animation:popInModal 0.3s cubic-bezier(0.16, 1, 0.3, 1);">
        <div style="background:linear-gradient(135deg,#047857,#064e3b);padding:24px 22px;color:#ffffff;text-align:center;">
            <div style="width:68px;height:68px;background:rgba(255,255,255,0.15);border-radius:50%;display:inline-flex;align-items:center;justify-content:center;margin-bottom:12px;border:2px solid rgba(251,191,36,0.6);">
                <i class="fa-solid fa-id-card" style="font-size:30px;color:#fbbf24;"></i>
            </div>
            <h3 style="margin:0 0 6px;font-size:22px;font-weight:700;letter-spacing:-0.3px;">প্রোফাইল সম্পূর্ণ করা আবশ্যক</h3>
            <p style="margin:0;font-size:14px;opacity:0.92;line-height:1.45;">ইসলামিক অনলাইন মাদ্রাসা (IOM) পোর্টালে আপনার ভর্তি নিশ্চিত হয়েছে। তবে অন্যান্য অ্যাকাডেমিক সেবায় প্রবেশের পূর্বে প্রোফাইল অন্তত ৯৫% সম্পূর্ণ করতে হবে।</p>
        </div>

        <div style="padding:22px 24px;">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;">
                <span style="font-size:14px;font-weight:600;color:#374151;">বর্তমান প্রোফাইল সম্পূর্ণতা:</span>
                <span style="font-size:16px;font-weight:800;color:#047857;">{{ $portalStudent->profile_completed_percent ?? 0 }}% সম্পন্ন</span>
            </div>
            <div style="width:100%;height:12px;background:#e5e7eb;border-radius:999px;overflow:hidden;margin-bottom:18px;">
                <div style="width:{{ $portalStudent->profile_completed_percent ?? 0 }}%;height:100%;background:linear-gradient(90deg,#047857,#10b981);transition:width 0.6s ease;"></div>
            </div>

            <div style="background:#fef2f2;border-left:4px solid #ef4444;padding:12px 14px;border-radius:8px;margin-bottom:20px;">
                <div style="font-size:13px;color:#991b1b;font-weight:700;margin-bottom:4px;display:flex;align-items:center;gap:6px;">
                    <i class="fa-solid fa-circle-exclamation"></i>
                    কেন ৯৫% সম্পূর্ণ করা জরুরি?
                </div>
                <div style="font-size:12.5px;color:#b91c1c;line-height:1.5;">
                    মাদ্রাসার নিয়মিত পাঠদান, পরীক্ষা ও ক্লাসরুটিন সেবা নির্বিঘ্নে ব্যবহারের জন্য আপনার স্থায়ী ঠিকানা, শিক্ষাগত যোগ্যতা ও অভিভাবকের তথ্যাদি নথিভুক্ত থাকা আবশ্যক।
                </div>
            </div>

            <div style="text-align:center;">
                <a href="{{ route('student.profile.index') }}" style="display:inline-flex;align-items:center;justify-content:center;gap:10px;width:100%;padding:14px 20px;background:linear-gradient(135deg,#047857,#064e3b);color:#ffffff;font-size:16px;font-weight:700;border-radius:12px;text-decoration:none;box-shadow:0 4px 14px rgba(4,120,87,0.35);transition:all .2s;">
                    <i class="fa-solid fa-user-pen"></i>
                    এখনই প্রোফাইল তথ্য প্রদান করুন ({{ $portalStudent->profile_completed_percent ?? 0 }}%)
                </a>
            </div>
        </div>
    </div>
</div>
<style>
@keyframes popInModal {
    0% { transform: scale(0.88); opacity: 0; }
    100% { transform: scale(1); opacity: 1; }
}
</style>
@endif

<x-fcm-initializer />
@stack('scripts')
</body>
</html>
