<x-student-layout>
    <x-slot name="title">My Attendance</x-slot>

    <div class="page-header">
        <div class="page-header-left">
            <h1>My Attendance Record</h1>
            <p>Module-by-module attendance audit and total percentage</p>
        </div>
    </div>

    <div class="stats-grid" style="grid-template-columns: repeat(3, 1fr);margin-bottom:24px">
        <div class="stat-card">
            
            <div class="stat-info">
                <div class="stat-value">{{ $percentage }}%</div>
                <div class="stat-label">Overall Attendance</div>
            </div>
        </div>
        <div class="stat-card">
            
            <div class="stat-info">
                <div class="stat-value">{{ $present }}</div>
                <div class="stat-label">Classes Attended</div>
            </div>
        </div>
        <div class="stat-card">
            
            <div class="stat-info">
                <div class="stat-value">{{ $total }}</div>
                <div class="stat-label">Total Conducted Sessions</div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Subject & Module</th>
                        <th>Class Date</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($attendances as $att)
                    <tr>
                        <td class="td-primary">
                            <strong>{{ $att->classSession?->subject?->name ?? '—' }}</strong><br>
                            @if($att->classSession?->moduleCovered)
                                <span class="td-muted">{{ $att->classSession->moduleCovered->title }}</span>
                            @endif
                        </td>
                        <td class="td-muted">{{ $att->classSession?->session_date?->format('d M Y (D)') ?? 'TBA' }}</td>
                        <td><span class="badge badge-{{ strtolower($att->status) }}">{{ ucfirst(strtolower($att->status)) }}</span></td>
                    </tr>
                    @empty
                    <tr><td colspan="3" style="text-align:center;padding:30px;color:var(--text-muted)">No attendance records logged yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-student-layout>
