<x-student-layout>
    <x-slot name="title">My Results</x-slot>

    <div class="page-header">
        <div class="page-header-left">
            <h1>My Examination Results & Transcript</h1>
            <p>Subject-wise marks, letter grades, and attempt history</p>
        </div>
    </div>

    <div class="card">
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Subject</th>
                        <th>Exam Title</th>
                        <th>Attempt No.</th>
                        <th>Obtained Marks</th>
                        <th>Grade</th>
                        <th>Result</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($results as $res)
                    <tr>
                        <td class="td-primary"><strong>{{ $res->subject->name ?? '—' }}</strong></td>
                        <td>{{ $res->exam->title ?? '—' }}</td>
                        <td><span class="badge badge-secondary no-dot">Attempt #{{ $res->attempt_no }}</span></td>
                        <td><strong>{{ $res->marks }}</strong> / {{ $res->exam->full_marks ?? 100 }}</td>
                        <td><span class="badge badge-active no-dot" style="font-size:13px">{{ $res->grade ?? 'A' }}</span></td>
                        <td>
                            @if($res->status === 'PASS')
                                <span class="badge badge-active">Passed</span>
                            @else
                                <span class="badge badge-cancelled">Failed (Retake Required)</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" style="text-align:center;padding:30px;color:var(--text-muted)">No published examination results yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-student-layout>
