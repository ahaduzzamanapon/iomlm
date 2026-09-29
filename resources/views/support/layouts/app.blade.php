<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Support Agent Portal' }} — IOM Support</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <style>
        :root {
            --sidebar-bg: #064e3b;
            --sidebar-hover: rgba(255, 255, 255, 0.12);
            --sidebar-active: #047857;
            --sidebar-text: #ffffff;
            --sidebar-text-active: #ffffff;
        }
        body { font-family: 'Kalpurush', 'Plus Jakarta Sans', 'Inter', sans-serif; background: #f8fafc; color: #0f172a; margin: 0; }
        .support-wrapper { display: flex; min-height: 100vh; }
        .support-sidebar {
            width: 250px; background: linear-gradient(180deg, #022c22 0%, #064e3b 50%, #032b21 100%) !important; color: #fff;
            display: flex; flex-direction: column; flex-shrink: 0;
            border-right: 1px solid rgba(52, 211, 153, 0.18);
            transition: all 0.3s ease; z-index: 100;
        }
        .sidebar-brand {
            height: 64px; padding: 0 20px; display: flex; align-items: center; gap: 12px;
            background: rgba(0, 0, 0, 0.22);
            border-bottom: 1px solid rgba(255, 255, 255, 0.08); text-decoration: none; color: #fff;
        }
        .sidebar-brand-icon {
            width: 36px; height: 36px; background: linear-gradient(135deg, #047857, #064e3b); border-radius: 8px;
            display: flex; align-items: center; justify-content: center; font-size: 16px;
        }
        .sidebar-brand-text h2 { font-size: 15px; font-weight: 700; margin: 0; line-height: 1.2; color: #ffffff; }
        .sidebar-brand-text span { font-size: 11px; color: #fbbf24; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px; }

        .sidebar-menu { padding: 16px 12px; flex: 1; overflow-y: auto; }
        .menu-section-title {
            font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px;
            color: #ffffff; opacity: 0.92; padding: 12px 10px 6px; margin-top: 6px;
        }
        .menu-item {
            display: flex; align-items: center; gap: 12px; padding: 10px 14px;
            color: #ffffff; text-decoration: none; border-radius: 8px;
            font-size: 13px; font-weight: 600; margin-bottom: 4px; transition: all 0.2s;
        }
        .menu-item:hover { background: rgba(255, 255, 255, 0.12); color: #ffffff; }
        .menu-item.active {
            background: linear-gradient(135deg, rgba(4, 120, 87, 0.85), rgba(5, 150, 105, 0.65)) !important;
            border: 1px solid rgba(52, 211, 153, 0.5) !important;
            color: #ffffff !important;
        }
        .menu-item .menu-badge {
            margin-left: auto; background: #334155; color: #f8fafc; font-size: 11px;
            padding: 2px 7px; border-radius: 12px; font-weight: 700;
        }
        .menu-item.active .menu-badge { background: rgba(255,255,255,0.2); }

        .support-main { flex: 1; display: flex; flex-direction: column; min-width: 0; }
        .support-topbar {
            height: 64px; background: #ffffff; border-bottom: 1px solid #e2e8f0;
            display: flex; align-items: center; justify-content: space-between;
            padding: 0 24px; position: sticky; top: 0; z-index: 90;
        }
        .support-content { padding: 24px; flex: 1; }

        /* ════ UNIVERSAL FOOLPROOF MODAL SCROLLING & POSITIONING ════ */
        .modal-overlay,
        .modal-backdrop,
        .modal-wrapper,
        [class*="modal-overlay"],
        [class*="modal-backdrop"],
        [class*="modal-wrapper"],
        div[id*="Modal"][style*="fixed"],
        div[id*="modal"][style*="fixed"],
        div[id*="Modal"][style*="position: fixed"],
        div[id*="modal"][style*="position: fixed"],
        div[id*="Modal"][style*="position:fixed"],
        div[id*="modal"][style*="position:fixed"] {
            position: fixed !important;
            top: 0 !important; left: 0 !important; right: 0 !important; bottom: 0 !important;
            width: 100% !important; height: 100% !important;
            background: rgba(15, 23, 42, 0.65) !important;
            backdrop-filter: blur(4px) !important;
            z-index: 99999 !important;
            overflow-x: hidden !important;
            overflow-y: auto !important;
            -webkit-overflow-scrolling: touch !important;
            align-items: flex-start !important;
            justify-content: center !important;
            padding: 30px 15px !important;
            box-sizing: border-box !important;
        }
        .modal-overlay:not(.open):not(.active):not(.show),
        .modal-backdrop:not(.open):not(.active):not(.show),
        .modal-wrapper:not(.open):not(.active):not(.show) {
            display: none;
        }
        .modal-overlay.open,
        .modal-overlay.active,
        .modal-overlay.show,
        .modal-backdrop.open,
        .modal-backdrop.active,
        .modal-backdrop.show,
        .modal-overlay[style*="display: flex"],
        .modal-overlay[style*="display:flex"],
        .modal-backdrop[style*="display: flex"],
        .modal-backdrop[style*="display:flex"],
        div[id*="Modal"][style*="display: flex"],
        div[id*="Modal"][style*="display:flex"],
        div[id*="modal"][style*="display: flex"],
        div[id*="modal"][style*="display:flex"] {
            display: flex !important;
            opacity: 1 !important;
            pointer-events: auto !important;
        }
        .modal-content-box,
        .modal-dialog,
        .modal,
        [class*="modal-content"],
        [class*="modal-box"],
        .modal-overlay > div:not(.modal-backdrop),
        .modal-backdrop > div {
            margin: 0 auto !important;
            max-height: calc(100vh - 60px) !important;
            overflow-y: auto !important;
            -webkit-overflow-scrolling: touch !important;
            box-sizing: border-box !important;
        }
        .modal-overlay::-webkit-scrollbar,
        .modal-backdrop::-webkit-scrollbar,
        .modal-content-box::-webkit-scrollbar,
        .modal::-webkit-scrollbar {
            width: 7px;
            height: 7px;
        }
        .modal-overlay::-webkit-scrollbar-thumb,
        .modal-backdrop::-webkit-scrollbar-thumb,
        .modal-content-box::-webkit-scrollbar-thumb,
        .modal::-webkit-scrollbar-thumb {
            background: rgba(148, 163, 184, 0.6);
            border-radius: 4px;
        }
        .modal-overlay::-webkit-scrollbar-thumb:hover,
        .modal-backdrop::-webkit-scrollbar-thumb:hover,
        .modal-content-box::-webkit-scrollbar-thumb:hover,
        .modal::-webkit-scrollbar-thumb:hover {
            background: rgba(100, 116, 139, 0.9);
        }
    </style>
</head>
<body>
    <div class="support-wrapper">
        {{-- Sidebar Menu --}}
        <aside class="support-sidebar">
            <a href="{{ route('support.dashboard') }}" class="sidebar-brand">
                <div class="sidebar-brand-icon">
                    <i class="fa-solid fa-headset"></i>
                </div>
                <div class="sidebar-brand-text">
                    <h2>IOM Support</h2>
                    <span>Agent Panel</span>
                </div>
            </a>

            <div class="sidebar-menu">
                <div class="menu-section-title">Support Queue</div>

                <a href="{{ route('support.dashboard') }}" class="menu-item {{ request()->fullUrl() === route('support.dashboard') ? 'active' : '' }}">
                    <i class="fa-solid fa-gauge-high"></i>
                    Dashboard Overview
                </a>

                <a href="{{ route('support.dashboard', ['status' => 'PENDING']) }}" class="menu-item {{ request()->query('status') === 'PENDING' ? 'active' : '' }}">
                    <i class="fa-solid fa-clock"></i>
                    Pending Queue
                    @php
                        $userDepts = auth()->user()->isAdmin() ? \App\Models\SupportDepartment::pluck('id')->toArray() : auth()->user()->supportDepartments()->pluck('support_departments.id')->toArray();
                        $pCount = \App\Models\SupportTicket::whereIn('department_id', $userDepts)->where('status', 'PENDING')->count();
                    @endphp
                    @if($pCount > 0)
                        <span class="menu-badge" style="background:#e11d48;color:#fff">{{ $pCount }}</span>
                    @endif
                </a>

                <a href="{{ route('support.dashboard', ['status' => 'IN_PROGRESS']) }}" class="menu-item {{ request()->query('status') === 'IN_PROGRESS' ? 'active' : '' }}">
                    <i class="fa-solid fa-comments"></i>
                    Active Support Chats
                    @php
                        $aCount = \App\Models\SupportTicket::where('assigned_agent_id', auth()->id())->where('status', 'IN_PROGRESS')->count();
                    @endphp
                    @if($aCount > 0)
                        <span class="menu-badge" style="background:#0284c7;color:#fff">{{ $aCount }}</span>
                    @endif
                </a>

                <a href="{{ route('support.dashboard', ['status' => 'CLOSED']) }}" class="menu-item {{ request()->query('status') === 'CLOSED' ? 'active' : '' }}">
                    <i class="fa-solid fa-circle-check"></i>
                    Resolved Tickets
                </a>

                <a href="{{ route('support.canned-messages.index') }}" class="menu-item {{ request()->routeIs('support.canned-messages*') ? 'active' : '' }}">
                    <i class="fa-solid fa-bolt"></i>
                    My Quick Replies
                </a>

                <div class="menu-section-title">Student Profile Lookup</div>
                <a href="javascript:void(0)" onclick="openStudentProfileModal('')" class="menu-item" style="color:#fef08a">
                    <i class="fa-solid fa-address-card" style="color:#fbbf24"></i>
                    স্টুডেন্ট প্রোফাইল চেক
                </a>

                <div class="menu-section-title">My Assigned Departments</div>
                @php
                    $myDepts = auth()->user()->isAdmin() ? \App\Models\SupportDepartment::get() : auth()->user()->supportDepartments;
                @endphp
                @forelse($myDepts as $dept)
                    <div class="menu-item" style="font-size:12px;opacity:0.85">
                        <span><i class="fa-solid fa-building" style="margin-right:6px"></i> {{ $dept->name }}</span>
                    </div>
                @empty
                    <div style="font-size:11px;color:#64748b;padding:8px 14px">কোনো ডিপার্টমেন্ট অ্যাসাইন করা নেই</div>
                @endforelse

                @if(auth()->user()->isAdmin())
                    <div class="menu-section-title">Admin Management</div>
                    <a href="{{ route('admin.support-departments.index') }}" class="menu-item">
                        <i class="fa-solid fa-users-gear"></i>
                        Manage Departments &amp; Agents
                    </a>
                @endif
            </div>
        </aside>

        {{-- Main Content Area --}}
        <div class="support-main">
            <header class="support-topbar">
                <div style="font-weight:700;font-size:15px;color:#0f172a">
                    {{ $title ?? 'Support Agent Portal' }}
                </div>

                {{-- Global Student Profile Quick Check --}}
                <div style="display:flex;align-items:center;background:#f8fafc;border:1px solid #cbd5e1;border-radius:20px;padding:3px 12px;gap:8px">
                    <i class="fa-solid fa-magnifying-glass" style="color:#64748b;font-size:12px"></i>
                    <input type="text" id="topbarStudentLookupInput" placeholder="স্টুডেন্ট আইডি দিয়ে প্রোফাইল চেক..." style="border:none;background:transparent;outline:none;font-size:13px;font-family:'Kalpurush',sans-serif;width:220px" onkeydown="if(event.key==='Enter'){event.preventDefault();openStudentProfileModal(this.value);}">
                    <button type="button" onclick="openStudentProfileModal(document.getElementById('topbarStudentLookupInput').value)" style="background:#0284c7;color:#fff;border:none;border-radius:12px;padding:3px 10px;font-size:11px;font-weight:700;cursor:pointer">
                        চেক করুন
                    </button>
                </div>

                <div style="display:flex;align-items:center;gap:16px">
                    <div style="text-align:right">
                        <div style="font-weight:700;font-size:13px">{{ auth()->user()->name }}</div>
                        <div style="font-size:11px;color:#0284c7;font-weight:600">{{ auth()->user()->isAdmin() ? 'Super Admin' : 'Support Agent' }}</div>
                    </div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="btn btn-outline btn-sm" style="color:#e11d48;border-color:#fecaca">
                            <i class="fa-solid fa-right-from-bracket"></i>
                            Logout
                        </button>
                    </form>
                </div>
            </header>

            <main class="support-content">
                @if(session('success'))
                    <div style="background:#ecfdf5;border:1px solid #a7f3d0;color:#047857;padding:12px 16px;border-radius:8px;margin-bottom:16px;font-size:13px;font-weight:600">
                        {{ session('success') }}
                    </div>
                @endif
                @if(session('error'))
                    <div style="background:#fef2f2;border:1px solid #fecaca;color:#991b1b;padding:12px 16px;border-radius:8px;margin-bottom:16px;font-size:13px;font-weight:600">
                        {{ session('error') }}
                    </div>
                @endif

                {{ $slot }}

                {{-- Global Reusable Support Student Profile Modal --}}
                @include('support.partials.student_profile_modal')
            </main>
        </div>
    </div>

    <script>
    window.openModal = function(id) {
        const m = typeof id === 'string' ? document.getElementById(id) : id;
        if (!m) return;
        m.classList.add('open', 'active', 'show');
        m.style.display = 'flex';
        m.style.pointerEvents = 'auto';
        m.style.opacity = '1';
        document.body.style.overflow = 'hidden';
    };

    window.closeModal = function(id) {
        const m = typeof id === 'string' ? document.getElementById(id) : id;
        if (!m) return;
        m.classList.remove('open', 'active', 'show');
        m.style.display = 'none';
        m.style.pointerEvents = '';
        m.style.opacity = '';
        const anyStillOpen = document.querySelectorAll(
            '.modal-overlay.open, .modal-overlay.active, .modal-backdrop.open, .modal-backdrop.active, ' +
            '.modal-overlay[style*="display: flex"], .modal-backdrop[style*="display: flex"], ' +
            '[id*="Modal"][style*="display: flex"], [id*="modal"][style*="display: flex"]'
        );
        if (!anyStillOpen || anyStillOpen.length === 0) {
            document.body.style.overflow = '';
        }
    };

    document.addEventListener('click', function(e) {
        const target = e.target;
        if (!target) return;

        // Never close if clicking inside modal content
        if (target.closest('.modal, .modal-dialog, .modal-content, .modal-content-box, [class*="modal-box"], [class*="modal-content"], [class*="modal-dialog"], form')) {
            return;
        }

        const isBackdrop = target.classList.contains('modal-overlay') ||
                           target.classList.contains('modal-backdrop') ||
                           target.classList.contains('modal-wrapper') ||
                           (target.id && (target.id.toLowerCase().includes('modal') || target.id.toLowerCase().includes('dialog')) && 
                            (target.style.position === 'fixed' || window.getComputedStyle(target).position === 'fixed'));
        if (isBackdrop) {
            if (typeof closeStudentProfileModal === 'function' && target.id === 'supportStudentProfileModal') {
                closeStudentProfileModal();
            } else {
                closeModal(target);
            }
        }
    });

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' || e.keyCode === 27) {
            const candidates = document.querySelectorAll(
                '.modal-backdrop, .modal-overlay, .modal-wrapper, [id*="Modal"], [id*="modal"]'
            );
            candidates.forEach(function(m) {
                const isFixed = m.style.position === 'fixed' || window.getComputedStyle(m).position === 'fixed' ||
                                m.classList.contains('modal-backdrop') || m.classList.contains('modal-overlay');
                if (isFixed) {
                    const isVisible = m.classList.contains('open') || m.classList.contains('active') || m.classList.contains('show') ||
                                      (m.style.display && m.style.display !== 'none') ||
                                      (window.getComputedStyle(m).display !== 'none');
                    if (isVisible) {
                        if (typeof closeStudentProfileModal === 'function' && m.id === 'supportStudentProfileModal') closeStudentProfileModal();
                        closeModal(m);
                    }
                }
            });
            document.body.style.overflow = '';
        }
    });
    </script>
    @stack('scripts')
</body>
</html>
