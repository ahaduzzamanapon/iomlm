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
            --sidebar-bg: #0f172a;
            --sidebar-hover: #1e293b;
            --sidebar-active: #0284c7;
            --sidebar-text: #94a3b8;
            --sidebar-text-active: #ffffff;
        }
        body { background: #f8fafc; color: #0f172a; margin: 0; }
        .support-wrapper { display: flex; min-height: 100vh; }
        .support-sidebar {
            width: 250px; background: var(--sidebar-bg); color: #fff;
            display: flex; flex-direction: column; flex-shrink: 0;
            transition: all 0.3s ease; z-index: 100;
        }
        .sidebar-brand {
            height: 64px; padding: 0 20px; display: flex; align-items: center; gap: 12px;
            border-bottom: 1px solid #1e293b; text-decoration: none; color: #fff;
        }
        .sidebar-brand-icon {
            width: 36px; height: 36px; background: #0284c7; border-radius: 8px;
            display: flex; align-items: center; justify-content: center; font-size: 16px;
        }
        .sidebar-brand-text h2 { font-size: 15px; font-weight: 700; margin: 0; line-height: 1.2; }
        .sidebar-brand-text span { font-size: 11px; color: #0284c7; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; }

        .sidebar-menu { padding: 16px 12px; flex: 1; overflow-y: auto; }
        .menu-section-title {
            font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px;
            color: #64748b; padding: 12px 10px 6px; margin-top: 6px;
        }
        .menu-item {
            display: flex; align-items: center; gap: 12px; padding: 10px 14px;
            color: var(--sidebar-text); text-decoration: none; border-radius: 8px;
            font-size: 13px; font-weight: 600; margin-bottom: 4px; transition: all 0.2s;
        }
        .menu-item:hover { background: var(--sidebar-hover); color: #f1f5f9; }
        .menu-item.active { background: var(--sidebar-active); color: var(--sidebar-text-active); }
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
                    Active Live Chats
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
                        ✓ {{ session('success') }}
                    </div>
                @endif
                @if(session('error'))
                    <div style="background:#fef2f2;border:1px solid #fecaca;color:#991b1b;padding:12px 16px;border-radius:8px;margin-bottom:16px;font-size:13px;font-weight:600">
                        ✕ {{ session('error') }}
                    </div>
                @endif

                {{ $slot }}
            </main>
        </div>
    </div>

    <script>
    function openModal(id){
        const m = document.getElementById(id);
        if(m) { m.classList.add('open'); document.body.style.overflow='hidden'; }
    }
    function closeModal(id){
        const m = document.getElementById(id);
        if(m) { m.classList.remove('open'); document.body.style.overflow=''; }
    }
    document.addEventListener('click', function(e){
        if(e.target.classList.contains('modal-overlay')){
            e.target.classList.remove('open');
            document.body.style.overflow='';
        }
    });
    document.addEventListener('keydown', function(e){
        if(e.key === 'Escape'){
            document.querySelectorAll('.modal-overlay.open').forEach(m => {
                m.classList.remove('open');
                document.body.style.overflow='';
            });
        }
    });
    </script>
    @stack('scripts')
</body>
</html>
