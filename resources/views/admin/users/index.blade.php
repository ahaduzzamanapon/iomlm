<x-admin-layout>
    <x-slot name="title">ইউজার ও রোল ম্যানেজমেন্ট (Admin User & Role Management)</x-slot>

    <style>
        .um-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 22px; flex-wrap: wrap; gap: 14px; font-family: 'Kalpurush', sans-serif; }
        .um-title { display: flex; align-items: center; gap: 10px; font-size: 22px; font-weight: 700; color: #1e293b; }
        .um-title i { width: 40px; height: 40px; border-radius: 10px; background: #e0e7ff; color: #4338ca; display: inline-flex; align-items: center; justify-content: center; font-size: 18px; }
        .um-subtitle { font-size: 13px; color: #64748b; margin-top: 3px; }

        .stat-grid-um { display: grid; grid-template-columns: repeat(auto-fit, minmax(210px, 1fr)); gap: 14px; margin-bottom: 22px; font-family: 'Kalpurush', sans-serif; }
        .stat-card-um { background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 16px 20px; display: flex; align-items: center; gap: 16px; box-shadow: 0 1px 3px rgba(0,0,0,0.03); }
        .stat-icon-um { width: 46px; height: 46px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 20px; flex-shrink: 0; }
        .stat-val-um  { font-size: 24px; font-weight: 800; color: #0f172a; line-height: 1; }
        .stat-lbl-um  { font-size: 12px; color: #64748b; font-weight: 600; margin-top: 4px; }

        .filter-card-um { background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 14px 18px; margin-bottom: 20px; display: flex; gap: 12px; align-items: center; flex-wrap: wrap; font-family: 'Kalpurush', sans-serif; }
        .search-wrap-um { position: relative; flex: 1; min-width: 220px; }
        .search-wrap-um i { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #94a3b8; }
        .search-wrap-um input { width: 100%; padding-left: 36px; padding-right: 12px; height: 38px; border-radius: 8px; border: 1px solid #cbd5e1; font-size: 13px; outline: none; }
        .search-wrap-um input:focus { border-color: #6366f1; box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.15); }

        .table-card-um { background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; overflow: hidden; font-family: 'Kalpurush', sans-serif; box-shadow: 0 1px 3px rgba(0,0,0,0.03); }
        .table-um { width: 100%; border-collapse: separate; border-spacing: 0; }
        .table-um th { background: #f8fafc; font-size: 12px; font-weight: 700; text-transform: uppercase; color: #64748b; padding: 13px 16px; border-bottom: 1px solid #e2e8f0; text-align: left; }
        .table-um td { padding: 14px 16px; border-bottom: 1px solid #f1f5f9; vertical-align: middle; font-size: 13px; }
        .table-um tr:last-child td { border-bottom: none; }
        .table-um tr:hover td { background: #fbfcfe; }

        .perm-pill { display: inline-flex; align-items: center; gap: 4px; padding: 2px 8px; border-radius: 6px; font-size: 11px; font-weight: 600; background: #f1f5f9; color: #334155; margin: 2px; border: 1px solid #e2e8f0; }
        .badge-super-admin { background: #fef2f2; color: #991b1b; border: 1px solid #fecaca; padding: 3px 10px; border-radius: 20px; font-size: 11px; font-weight: 700; display: inline-flex; align-items: center; gap: 4px; }
        .badge-admin       { background: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe; padding: 3px 10px; border-radius: 20px; font-size: 11px; font-weight: 700; display: inline-flex; align-items: center; gap: 4px; }
        .badge-support     { background: #fdf2f8; color: #9d174d; border: 1px solid #fbcfe8; padding: 2px 8px; border-radius: 12px; font-size: 11px; font-weight: 600; }

        .user-avatar-um { width: 38px; height: 38px; border-radius: 50%; background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%); color: #fff; display: inline-flex; align-items: center; justify-content: center; font-weight: 700; font-size: 14px; flex-shrink: 0; }
    </style>

    {{-- Page Header --}}
    <div class="um-header">
        <div>
            <div class="um-title">
                <i class="fa-solid fa-users-gear"></i>
                এডমিন ও স্টাফ ইউজার ম্যানেজমেন্ট
            </div>
            <div class="um-subtitle">
                অ্যাডমিন প্যানেলের কর্মকর্তাদের রোল, অ্যাক্সেস মেনু পারমিশন এবং সাপোর্ট ডিপার্টমেন্ট পরিচালনা
            </div>
        </div>
        <div>
            <a href="{{ route('admin.users.create') }}" class="btn btn-primary" style="display:inline-flex;align-items:center;gap:6px">
                <i class="fa-solid fa-user-plus"></i> + নতুন এডমিন / স্টাফ যোগ করুন
            </a>
        </div>
    </div>

    @if(session('success'))
        <div style="background:#ecfdf5;border:1px solid #a7f3d0;color:#047857;padding:12px 16px;border-radius:8px;margin-bottom:18px;font-size:13px;font-weight:600;display:flex;align-items:center;gap:8px">
            <i class="fa-solid fa-circle-check"></i> {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div style="background:#fef2f2;border:1px solid #fecaca;color:#991b1b;padding:12px 16px;border-radius:8px;margin-bottom:18px;font-size:13px;font-weight:600;display:flex;align-items:center;gap:8px">
            <i class="fa-solid fa-circle-exclamation"></i> {{ session('error') }}
        </div>
    @endif

    {{-- KPI Stat Cards --}}
    <div class="stat-grid-um">
        <div class="stat-card-um">
            <div class="stat-icon-um" style="background:#e0e7ff;color:#4338ca">
                <i class="fa-solid fa-users"></i>
            </div>
            <div>
                <div class="stat-val-um">{{ $totalAdmins }}</div>
                <div class="stat-lbl-um">মোট এডমিন / স্টাফ</div>
            </div>
        </div>
        <div class="stat-card-um">
            <div class="stat-icon-um" style="background:#dcfce7;color:#15803d">
                <i class="fa-solid fa-user-check"></i>
            </div>
            <div>
                <div class="stat-val-um">{{ $activeAdmins }}</div>
                <div class="stat-lbl-um">সক্রিয় অ্যাকাউন্ট</div>
            </div>
        </div>
        <div class="stat-card-um">
            <div class="stat-icon-um" style="background:#fce7f3;color:#be185d">
                <i class="fa-solid fa-headset"></i>
            </div>
            <div>
                <div class="stat-val-um">{{ $supportAgents }}</div>
                <div class="stat-lbl-um">সাপোর্ট দায়িত্বপ্রাপ্ত</div>
            </div>
        </div>
        <div class="stat-card-um">
            <div class="stat-icon-um" style="background:#fef3c7;color:#b45309">
                <i class="fa-solid fa-shield-halved"></i>
            </div>
            <div>
                <div class="stat-val-um">{{ count($modules) }} টি</div>
                <div class="stat-lbl-um">নিয়ন্ত্রণযোগ্য মডিউল</div>
            </div>
        </div>
    </div>

    {{-- Filter Card --}}
    <div class="filter-card-um">
        <form method="GET" action="{{ route('admin.users.index') }}" style="display:contents">
            <div class="search-wrap-um">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input type="text" name="search" value="{{ $search }}" placeholder="নাম, ইমেইল বা পদবী দিয়ে খুঁজুন...">
            </div>

            <select name="role" onchange="this.form.submit()" style="height:38px;padding:0 12px;border:1px solid #cbd5e1;border-radius:8px;font-size:13px;outline:none;background:#fff">
                <option value="ALL" {{ $roleFilter === 'ALL' ? 'selected' : '' }}>সকল রোল (All Roles)</option>
                <option value="super_admin" {{ $roleFilter === 'super_admin' ? 'selected' : '' }}>Super Admin</option>
                <option value="admin" {{ $roleFilter === 'admin' ? 'selected' : '' }}>Admin / Staff</option>
                <option value="support_agent" {{ $roleFilter === 'support_agent' ? 'selected' : '' }}>Support Agent</option>
            </select>

            <select name="status" onchange="this.form.submit()" style="height:38px;padding:0 12px;border:1px solid #cbd5e1;border-radius:8px;font-size:13px;outline:none;background:#fff">
                <option value="ALL" {{ $statusFilter === 'ALL' ? 'selected' : '' }}>সকল স্ট্যাটাস</option>
                <option value="ACTIVE" {{ $statusFilter === 'ACTIVE' ? 'selected' : '' }}>সক্রিয় (Active)</option>
                <option value="INACTIVE" {{ $statusFilter === 'INACTIVE' ? 'selected' : '' }}>নিষ্ক্রিয় (Inactive)</option>
            </select>

            <button type="submit" class="btn btn-outline" style="height:38px;padding:0 14px">
                <i class="fa-solid fa-filter"></i> ফিল্টার
            </button>

            @if($search || $roleFilter !== 'ALL' || $statusFilter !== 'ALL')
                <a href="{{ route('admin.users.index') }}" class="btn btn-outline" style="height:38px;padding:0 12px;color:#ef4444" title="ফিল্টার রিসেট">
                    <i class="fa-solid fa-rotate-left"></i>
                </a>
            @endif
        </form>
    </div>

    {{-- Users Table --}}
    <div class="table-card-um">
        <table class="table-um">
            <thead>
                <tr>
                    <th>ব্যবহারকারী / কর্মকর্তা</th>
                    <th>রোল ও পদবী</th>
                    <th>মেনু / মডিউল পারমিশন</th>
                    <th>লাইভ সাপোর্ট দায়িত্ব</th>
                    <th style="text-align:center">স্ট্যাটাস</th>
                    <th style="text-align:right">অ্যাকশন</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $u)
                <tr>
                    {{-- User Details --}}
                    <td>
                        <div style="display:flex;align-items:center;gap:12px">
                            <div class="user-avatar-um">
                                {{ strtoupper(substr($u->name, 0, 1)) }}
                            </div>
                            <div>
                                <div style="font-weight:700;color:#0f172a;font-size:14px">
                                    {{ $u->name }}
                                    @if(auth()->id() === $u->id)
                                        <span style="font-size:10px;background:#fef3c7;color:#92400e;padding:1px 6px;border-radius:4px;margin-left:4px">আপনি (You)</span>
                                    @endif
                                </div>
                                <div style="font-size:12px;color:#64748b">{{ $u->email }}</div>
                            </div>
                        </div>
                    </td>

                    {{-- Role & Designation --}}
                    <td>
                        @if($u->role === 'super_admin')
                            <span class="badge-super-admin"><i class="fa-solid fa-crown"></i> Super Admin</span>
                        @elseif($u->role === 'admin')
                            <span class="badge-admin"><i class="fa-solid fa-shield"></i> Admin / Staff</span>
                        @else
                            <span class="badge badge-secondary no-dot">{{ ucfirst($u->role) }}</span>
                        @endif

                        <div style="font-size:12px;color:#475569;margin-top:4px;font-weight:600">
                            {{ $u->designation ?: 'পদবী নির্দিষ্ট নয়' }}
                        </div>
                    </td>

                    {{-- Menu Permissions --}}
                    <td style="max-width:320px">
                        @if($u->role === 'super_admin' || ($u->role === 'admin' && empty($u->admin_permissions)))
                            <span style="font-size:12px;color:#059669;font-weight:700;display:inline-flex;align-items:center;gap:4px">
                                <i class="fa-solid fa-circle-check"></i> সকল মডিউলে পূর্ণ অ্যাক্সেস (Full Access)
                            </span>
                        @else
                            @php $userPerms = (array) ($u->admin_permissions ?? []); @endphp
                            @if(empty($userPerms))
                                <span style="font-size:12px;color:#94a3b8;font-style:italic">কোনো পারমিশন বরাদ্দ নেই</span>
                            @else
                                <div style="display:flex;flex-wrap:wrap">
                                    @foreach($userPerms as $permKey)
                                        @if(isset($modules[$permKey]))
                                            <span class="perm-pill">
                                                <i class="fa-solid {{ $modules[$permKey]['icon'] }}" style="font-size:10px;color:#6366f1"></i>
                                                {{ $modules[$permKey]['name'] }}
                                            </span>
                                        @endif
                                    @endforeach
                                </div>
                            @endif
                        @endif
                    </td>

                    {{-- Support Desk Assignment --}}
                    <td>
                        @if($u->can_provide_support || $u->role === 'support_agent' || $u->supportDepartments->count() > 0)
                            <div style="margin-bottom:4px">
                                <span class="badge-support"><i class="fa-solid fa-headset"></i> সাপোর্ট সক্রিয়</span>
                            </div>
                            @forelse($u->supportDepartments as $dept)
                                <span style="font-size:10px;background:#f1f5f9;color:#475569;padding:2px 6px;border-radius:4px;border:1px solid #e2e8f0;display:inline-block;margin:1px">
                                    {{ $dept->name }}
                                </span>
                            @empty
                                <span style="font-size:11px;color:#94a3b8;font-style:italic">কোনো ডিপার্টমেন্ট নির্দিষ্ট নয়</span>
                            @endforelse
                        @else
                            <span style="font-size:12px;color:#94a3b8">❌ সাপোর্ট নিষ্ক্রিয়</span>
                        @endif
                    </td>

                    {{-- Active / Inactive Status --}}
                    <td style="text-align:center">
                        <form method="POST" action="{{ route('admin.users.toggle-status', $u) }}" style="display:inline">
                            @csrf @method('PATCH')
                            @if($u->is_active)
                                <button type="submit" class="btn btn-outline btn-sm" style="color:#059669;border-color:#a7f3d0;background:#ecfdf5" title="নিষ্ক্রিয় করতে ক্লিক করুন" {{ auth()->id() === $u->id ? 'disabled' : '' }}>
                                    <i class="fa-solid fa-circle" style="font-size:8px"></i> সক্রিয়
                                </button>
                            @else
                                <button type="submit" class="btn btn-outline btn-sm" style="color:#dc2626;border-color:#fecaca;background:#fef2f2" title="সক্রিয় করতে ক্লিক করুন" {{ auth()->id() === $u->id ? 'disabled' : '' }}>
                                    <i class="fa-solid fa-circle" style="font-size:8px"></i> নিষ্ক্রিয়
                                </button>
                            @endif
                        </form>
                    </td>

                    {{-- Actions --}}
                    <td style="text-align:right">
                        <div style="display:inline-flex;gap:6px">
                            <a href="{{ route('admin.users.edit', $u) }}" class="btn btn-outline btn-sm" title="এডিট ও পারমিশন পরিবর্তন">
                                <i class="fa-solid fa-pen-to-square"></i> এডিট
                            </a>

                            @if(auth()->id() !== $u->id)
                                <form method="POST" action="{{ route('admin.users.destroy', $u) }}" onsubmit="return confirm('আপনি কি নিশ্চিত যে এই ইউজারটিকে মুছে ফেলতে চান?')" style="display:inline">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-outline btn-sm" style="color:#dc2626" title="ইউজার মুছুন">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" style="text-align:center;padding:40px;color:#94a3b8">
                        <i class="fa-solid fa-users-slash" style="font-size:32px;margin-bottom:10px;display:block"></i>
                        কোনো এডমিন বা স্টাফ ইউজার পাওয়া যায়নি।
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>

        @if($users->hasPages())
            <div style="padding:16px;border-top:1px solid #e2e8f0">
                {{ $users->links() }}
            </div>
        @endif
    </div>

</x-admin-layout>
