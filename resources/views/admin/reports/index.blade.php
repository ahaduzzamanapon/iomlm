<x-admin-layout>
    <x-slot name="title">Reports & Analytics</x-slot>

    <div class="page-header">
        <div class="page-header-left">
            <h1>Reports & System Analytics</h1>
            <p>Comprehensive statistics on enrollment, academic progress, and faculty utilization</p>
        </div>
    </div>

    <div class="stats-grid">
        <div class="stat-card">
            
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
            
            <div class="stat-info">
                <div class="stat-value">{{ $stats['active_teachers'] }}</div>
                <div class="stat-label">Faculty Members</div>
            </div>
        </div>
        <div class="stat-card">
            
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
            <div class="card-header"><span class="card-title">Quick Export Actions</span></div>
            <div class="card-body" style="display:flex;flex-direction:column;gap:12px">
                <button class="btn btn-outline" onclick="alert('Exporting student roster to CSV...')">Export Active Students Roster (CSV)</button>
                <button class="btn btn-outline" onclick="alert('Exporting attendance report...')">Export Class Attendance Report (PDF)</button>
                <button class="btn btn-outline" onclick="alert('Exporting exam results...')">Export Examination Results Summary</button>
            </div>
        </div>
    </div>
</x-admin-layout>
