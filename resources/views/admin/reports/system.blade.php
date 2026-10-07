<x-admin-layout>
    <x-slot name="title">System Analytics &amp; Reports</x-slot>

    <div class="page-header" style="font-family:'Kalpurush',sans-serif">
        <div class="page-header-left">
            <h1 style="display:flex;align-items:center;gap:10px">
                <i class="fa-solid fa-chart-pie" style="color:#047857"></i>
                <span>System Analytics &amp; KPI (সিস্টেম পরিসংখ্যান)</span>
            </h1>
            <p>Comprehensive statistics on enrollment, academic progress, and faculty utilization</p>
        </div>
        <div class="page-header-actions">
            <a href="{{ route('admin.reports.index') }}" class="btn btn-primary" style="font-family:'Kalpurush',sans-serif">
                <i class="fa-solid fa-table-list"></i> ভর্তি সামারি রিপোর্ট (Admission Summary)
            </a>
        </div>
    </div>

    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon" style="background:#ecfdf5;color:#047857">👥</div>
            <div class="stat-info">
                <div class="stat-value">{{ $stats['active_students'] }}</div>
                <div class="stat-label">Active Enrolled Students</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon orange">⏳</div>
            <div class="stat-info">
                <div class="stat-value">{{ $stats['pending_leads'] }}</div>
                <div class="stat-label">Pending Admissions</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background:#eff6ff;color:#2563eb">👨‍🏫</div>
            <div class="stat-info">
                <div class="stat-value">{{ $stats['active_teachers'] }}</div>
                <div class="stat-label">Faculty Members</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background:#fef3c7;color:#d97706">📚</div>
            <div class="stat-info">
                <div class="stat-value">{{ $stats['total_courses'] }}</div>
                <div class="stat-label">Active Courses</div>
            </div>
        </div>
    </div>

    <div class="grid-2" style="margin-top:24px">
        <div class="card">
            <div class="card-header"><span class="card-title">Academic Execution Summary</span></div>
            <div class="card-body">
                <table class="table" style="font-size:13px">
                    <tr><th style="color:var(--text-muted)">Active Batches:</th><td><strong>{{ $stats['active_batches'] }}</strong></td></tr>
                    <tr><th style="color:var(--text-muted)">Completed Class Sessions:</th><td><strong>{{ $stats['completed_classes'] }}</strong></td></tr>
                    <tr><th style="color:var(--text-muted)">Total Exams Evaluated:</th><td><strong>{{ $stats['total_exams'] }}</strong></td></tr>
                </table>
            </div>
        </div>
        <div class="card">
            <div class="card-header"><span class="card-title">Quick Reports &amp; Shortcuts</span></div>
            <div class="card-body" style="display:flex;flex-direction:column;gap:12px">
                <a href="{{ route('admin.reports.index') }}" class="btn btn-outline" style="text-align:left;display:flex;align-items:center;gap:10px">
                    <i class="fa-solid fa-table-list" style="color:#047857"></i>
                    <span>সেশন-ভিত্তিক ভর্তি সামারি রিপোর্ট (Admission Summary Report)</span>
                </a>
                <a href="{{ route('admin.students.index') }}" class="btn btn-outline" style="text-align:left;display:flex;align-items:center;gap:10px">
                    <i class="fa-solid fa-users" style="color:#2563eb"></i>
                    <span>শিক্ষার্থী তালিকা ও রোস্টার (Student Roster)</span>
                </a>
                <a href="{{ route('admin.accounts.reports') }}" class="btn btn-outline" style="text-align:left;display:flex;align-items:center;gap:10px">
                    <i class="fa-solid fa-file-invoice-dollar" style="color:#d97706"></i>
                    <span>ফি আদায় ও একাউন্টস রিপোর্ট (Accounts &amp; Collection Report)</span>
                </a>
            </div>
        </div>
    </div>
</x-admin-layout>
