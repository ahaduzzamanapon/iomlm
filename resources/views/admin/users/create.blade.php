<x-admin-layout>
    <x-slot name="title">নতুন এডমিন / স্টাফ যোগ করুন</x-slot>

    <style>
        .um-form-wrap { max-width: 980px; margin: 0 auto; font-family: 'Kalpurush', sans-serif; }
        .form-card-um { background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 24px; margin-bottom: 22px; box-shadow: 0 1px 3px rgba(0,0,0,0.03); }
        .form-section-title { font-size: 16px; font-weight: 700; color: #0f172a; margin-bottom: 6px; display: flex; align-items: center; gap: 8px; }
        .form-section-sub { font-size: 12px; color: #64748b; margin-bottom: 18px; }

        .form-grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 18px; }
        @media (max-width: 768px) { .form-grid-2 { grid-template-columns: 1fr; } }

        .form-group-um { margin-bottom: 16px; }
        .form-label-um { display: block; font-size: 13px; font-weight: 600; color: #334155; margin-bottom: 6px; }
        .form-input-um { width: 100%; height: 42px; border: 1.5px solid #cbd5e1; border-radius: 8px; padding: 0 14px; font-size: 14px; outline: none; transition: border-color 0.2s; background: #fff; }
        .form-input-um:focus { border-color: #6366f1; box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.15); }

        .preset-btn { background: #f1f5f9; border: 1px solid #cbd5e1; color: #334155; font-size: 12px; font-weight: 600; padding: 6px 12px; border-radius: 6px; cursor: pointer; transition: all 0.15s; }
        .preset-btn:hover { background: #6366f1; color: #fff; border-color: #6366f1; }

        .module-grid-um { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 12px; }
        .module-box-um { border: 1.5px solid #e2e8f0; border-radius: 10px; padding: 14px 16px; display: flex; align-items: flex-start; gap: 12px; background: #f8fafc; cursor: pointer; transition: all 0.18s; }
        .module-box-um:hover { border-color: #818cf8; background: #faf5ff; }
        .module-box-um.selected { border-color: #6366f1; background: #eef2ff; }
        .module-box-um input[type="checkbox"] { width: 18px; height: 18px; accent-color: #6366f1; cursor: pointer; margin-top: 2px; }

        .support-box { background: #fdf2f8; border: 1.5px solid #fbcfe8; border-radius: 10px; padding: 18px 20px; }
    </style>

    <div class="um-form-wrap">
        {{-- Header --}}
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;flex-wrap:wrap;gap:12px">
            <div>
                <a href="{{ route('admin.users.index') }}" style="font-size:12px;color:#6366f1;text-decoration:none;font-weight:600">
                    ← ইউজার তালিকায় ফিরুন
                </a>
                <h1 style="font-size:22px;font-weight:700;color:#0f172a;margin:4px 0 0">
                    নতুন এডমিন / স্টাফ যোগ করুন
                </h1>
            </div>
        </div>

        @if ($errors->any())
            <div style="background:#fef2f2;border:1px solid #fecaca;color:#991b1b;padding:14px 18px;border-radius:10px;margin-bottom:20px;font-size:13px">
                <strong style="display:block;margin-bottom:6px">অনুগ্রহ করে নিচের ত্রুটিগুলো সংশোধন করুন:</strong>
                <ul style="margin:0;padding-left:20px">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('admin.users.store') }}">
            @csrf

            {{-- 1. Basic Information --}}
            <div class="form-card-um">
                <div class="form-section-title">
                    <i class="fa-solid fa-user" style="color:#6366f1"></i>
                    কর্মকর্তার মৌলিক তথ্য (Account Credentials)
                </div>
                <div class="form-section-sub">অ্যাডমিন প্যানেলে লগইন করার নাম, ইমেইল ঠিকানা ও পদবী নির্ধারণ করুন</div>

                <div class="form-grid-2">
                    <div class="form-group-um">
                        <label class="form-label-um">পূর্ণ নাম (Full Name) <span style="color:#ef4444">*</span></label>
                        <input type="text" name="name" class="form-input-um" value="{{ old('name') }}" placeholder="যেমন: মো: রফিকুল ইসলাম" required>
                    </div>

                    <div class="form-group-um">
                        <label class="form-label-um">অফিসিয়াল ইমেইল ঠিকানা (Email) <span style="color:#ef4444">*</span></label>
                        <input type="email" name="email" class="form-input-um" value="{{ old('email') }}" placeholder="admin@example.com" required>
                    </div>

                    <div class="form-group-um">
                        <label class="form-label-um">লগইন পাসওয়ার্ড (Password) <span style="color:#ef4444">*</span></label>
                        <input type="password" name="password" class="form-input-um" placeholder="কমপক্ষে ৬ অক্ষরের পাসওয়ার্ড" required>
                    </div>

                    <div class="form-group-um">
                        <label class="form-label-um">পদবী / দায়িত্ব (Designation)</label>
                        <input type="text" name="designation" class="form-input-um" value="{{ old('designation') }}" placeholder="যেমন: হিসাব কর্মকর্তা, পরীক্ষা নিয়ন্ত্রক...">
                    </div>
                </div>

                <div class="form-group-um" style="margin-top:8px">
                    <label class="form-label-um">রোল ধরন (System Role) <span style="color:#ef4444">*</span></label>
                    <div style="display:flex;gap:20px;margin-top:6px;flex-wrap:wrap">
                        <label style="display:inline-flex;align-items:center;gap:8px;font-size:14px;cursor:pointer">
                            <input type="radio" name="role" value="admin" {{ old('role', 'admin') === 'admin' ? 'checked' : '' }} onchange="toggleRoleOptions(this.value)">
                            <span><strong>Admin / Staff</strong> (নির্ধারিত মডিউলে অ্যাক্সেস পাবে)</span>
                        </label>
                        <label style="display:inline-flex;align-items:center;gap:8px;font-size:14px;cursor:pointer">
                            <input type="radio" name="role" value="super_admin" {{ old('role') === 'super_admin' ? 'checked' : '' }} onchange="toggleRoleOptions(this.value)">
                            <span style="color:#b91c1c"><strong>Super Admin</strong> (সকল মডিউলে পূর্ণ অ্যাক্সেস)</span>
                        </label>
                    </div>
                </div>
            </div>

            {{-- 2. Module & Menu Permissions --}}
            <div class="form-card-um" id="permissionsSection">
                <div style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:12px;margin-bottom:14px">
                    <div>
                        <div class="form-section-title">
                            <i class="fa-solid fa-key" style="color:#6366f1"></i>
                            এডমিন প্যানেল মেনু ও মডিউল পারমিশন
                        </div>
                        <div class="form-section-sub" style="margin-bottom:0">
                            এই কর্মকর্তা এডমিন প্যানেলের কোন কোন মেনু দেখতে ও কাজ করতে পারবেন তা টিক দিন
                        </div>
                    </div>

                    {{-- Quick Presets --}}
                    <div style="display:flex;gap:6px;flex-wrap:wrap">
                        <button type="button" class="preset-btn" onclick="applyPreset('all')">সব সিলেক্ট</button>
                        <button type="button" class="preset-btn" onclick="applyPreset('accounts')">হিসাব কর্মকর্তা</button>
                        <button type="button" class="preset-btn" onclick="applyPreset('exam')">পরীক্ষা নিয়ন্ত্রক</button>
                        <button type="button" class="preset-btn" onclick="applyPreset('academic')">একাডেমিক</button>
                        <button type="button" class="preset-btn" onclick="applyPreset('clear')" style="color:#ef4444">ক্লিয়ার</button>
                    </div>
                </div>

                <div class="module-grid-um">
                    @foreach($modules as $key => $mod)
                    <label class="module-box-um" id="box-{{ $key }}">
                        <input type="checkbox" name="permissions[]" value="{{ $key }}" class="perm-check" id="perm-{{ $key }}"
                               {{ in_array($key, old('permissions', [])) ? 'checked' : '' }}
                               onchange="updateBoxStyle('{{ $key }}')">
                        <div>
                            <div style="font-weight:700;font-size:14px;color:#0f172a;display:flex;align-items:center;gap:6px">
                                <i class="fa-solid {{ $mod['icon'] }}" style="color:#6366f1;font-size:13px"></i>
                                {{ $mod['name'] }}
                            </div>
                            <div style="font-size:11px;color:#64748b;margin-top:2px;line-height:1.4">
                                {{ $mod['desc'] }}
                            </div>
                        </div>
                    </label>
                    @endforeach
                </div>
            </div>

            {{-- 3. Support Helpdesk Assignment --}}
            <div class="form-card-um support-box">
                <div class="form-section-title" style="color:#9d174d">
                    <i class="fa-solid fa-headset"></i>
                    অনলাইন সাপোর্ট ও হেল্পডেস্ক দায়িত্ব (Customer Support)
                </div>
                <div class="form-section-sub" style="color:#be185d">
                    এই কর্মকর্তা হেল্পডেস্কে সাপোর্ট প্রদান করতে পারবেন কিনা এবং কোন কোন ডিপার্টমেন্টের টিকিট পাবেন
                </div>

                <div style="margin-bottom:14px">
                    <label style="display:inline-flex;align-items:center;gap:10px;cursor:pointer;font-weight:700;font-size:14px;color:#831843">
                        <input type="checkbox" name="can_provide_support" value="1" id="supportToggle"
                               {{ old('can_provide_support') ? 'checked' : '' }}
                               onchange="toggleSupportDeptSection(this.checked)"
                               style="width:18px;height:18px;accent-color:#db2777">
                        লাইভ সাপোর্ট প্রদান করতে পারবে (Enable Support Agent Role)
                    </label>
                </div>

                <div id="supportDeptsWrap" style="display:{{ old('can_provide_support') ? 'block' : 'none' }};border-top:1px dashed #fbcfe8;padding-top:14px">
                    <label class="form-label-um" style="color:#9d174d">যে যে ডিপার্টমেন্টে সাপোর্ট দেবে (Support Departments):</label>
                    <div style="display:grid;grid-template-columns:repeat(auto-fill, minmax(200px, 1fr));gap:10px;margin-top:8px">
                        @forelse($departments as $dept)
                        <label style="display:inline-flex;align-items:center;gap:8px;background:#fff;padding:8px 12px;border:1px solid #fbcfe8;border-radius:6px;font-size:13px;cursor:pointer">
                            <input type="checkbox" name="departments[]" value="{{ $dept->id }}"
                                   {{ in_array($dept->id, old('departments', [])) ? 'checked' : '' }}
                                   style="accent-color:#db2777">
                            <span style="font-weight:600;color:#334155">{{ $dept->name }}</span>
                        </label>
                        @empty
                        <div style="font-size:12px;color:#94a3b8">কোনো ডিপার্টমেন্ট সক্রিয় নেই।</div>
                        @endforelse
                    </div>
                </div>
            </div>

            {{-- Submit --}}
            <div style="display:flex;justify-content:flex-end;gap:12px;margin-top:10px">
                <a href="{{ route('admin.users.index') }}" class="btn btn-outline" style="height:44px;padding:0 20px;font-size:14px">
                    বাতিল করুন
                </a>
                <button type="submit" class="btn btn-primary" style="height:44px;padding:0 24px;font-size:14px;font-weight:700">
                    <i class="fa-solid fa-floppy-disk"></i> এডমিন ইউজার সংরক্ষণ করুন
                </button>
            </div>
        </form>
    </div>

    <script>
    function updateBoxStyle(key) {
        const chk = document.getElementById('perm-' + key);
        const box = document.getElementById('box-' + key);
        if (chk && box) {
            if (chk.checked) box.classList.add('selected');
            else box.classList.remove('selected');
        }
    }

    function toggleRoleOptions(role) {
        const permSec = document.getElementById('permissionsSection');
        if (role === 'super_admin') {
            document.querySelectorAll('.perm-check').forEach(chk => {
                chk.checked = true;
                const key = chk.id.replace('perm-', '');
                updateBoxStyle(key);
            });
        }
    }

    function toggleSupportDeptSection(checked) {
        const wrap = document.getElementById('supportDeptsWrap');
        if (wrap) wrap.style.display = checked ? 'block' : 'none';
    }

    function applyPreset(preset) {
        const presets = {
            all: ['academic', 'admissions', 'students', 'teachers', 'classes_batches', 'exams', 'communication', 'accounts', 'support', 'settings', 'user_management'],
            accounts: ['accounts', 'students', 'communication'],
            exam: ['exams', 'students', 'academic', 'teachers'],
            academic: ['academic', 'teachers', 'classes_batches', 'students'],
            clear: []
        };

        const list = presets[preset] || [];
        document.querySelectorAll('.perm-check').forEach(chk => {
            const key = chk.id.replace('perm-', '');
            chk.checked = list.includes(key);
            updateBoxStyle(key);
        });
    }

    // Initialize box styles on load
    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('.perm-check').forEach(chk => {
            const key = chk.id.replace('perm-', '');
            updateBoxStyle(key);
        });
    });
    </script>
</x-admin-layout>
