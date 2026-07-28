<x-student-layout>
    <x-slot name="title">Upcoming Exams</x-slot>

    <div class="page-header">
        <div class="page-header-left">
            <h1>Upcoming Exams & Admit Cards</h1>
            <p>View exam schedules and download digital admit cards</p>
        </div>
    </div>

    <div class="card">
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Admit Card No.</th>
                        <th>Subject & Exam Title</th>
                        <th>Type</th>
                        <th>Exam Date</th>
                        <th>Marks</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($attendees as $att)
                    <tr>
                        <td><span class="badge badge-active no-dot"><strong>{{ $att->admit_card_no ?? 'ADM-PENDING' }}</strong></span></td>
                        <td class="td-primary">
                            <strong>{{ $att->exam->subject->name ?? '—' }}</strong><br>
                            <span class="td-muted">{{ $att->exam->title ?? '—' }}</span>
                        </td>
                        <td><span class="badge badge-secondary no-dot">{{ $att->exam->type ?? 'FINAL' }}</span></td>
                        <td class="td-muted">{{ $att->exam->exam_date ? \Carbon\Carbon::parse($att->exam->exam_date)->format('d M Y') : 'TBD' }}</td>
                        <td>{{ $att->exam->full_marks ?? 100 }} Marks</td>
                    </tr>
                    @empty
                    <tr><td colspan="5" style="text-align:center;padding:30px;color:var(--text-muted)">No upcoming exam registrations found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-student-layout>
