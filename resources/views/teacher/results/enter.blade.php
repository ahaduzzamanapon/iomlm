<x-teacher-layout>
    <x-slot name="title">Enter Marks — {{ $exam->title }}</x-slot>

    <div class="page-header">
        <div class="page-header-left">
            <div style="font-size:12px;color:var(--text-muted);margin-bottom:4px">
                <a href="{{ route('teacher.results.index') }}">← Back to Results List</a>
            </div>
            <h1>Marks Entry: {{ $exam->title }}</h1>
            <p>Subject: {{ $exam->subject->name ?? '—' }} · Full Marks: {{ $exam->full_marks }} · Pass Marks: {{ $exam->pass_marks }}</p>
        </div>
    </div>

    <form method="POST" action="{{ route('teacher.results.store', $exam) }}">
        @csrf
        <div class="card">
            <div class="card-header">
                <span class="card-title">Student Evaluation Sheet</span>
            </div>
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>Student Code</th>
                            <th>Student Name</th>
                            <th>Obtained Marks (Max {{ $exam->full_marks }})</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($students as $st)
                        @php
                            $existingRes = $exam->results->where('student_id', $st->id)->first();
                        @endphp
                        <tr>
                            <td><span class="badge badge-secondary no-dot">{{ $st->student_code ?? 'N/A' }}</span></td>
                            <td class="td-primary"><strong>{{ $st->name }}</strong></td>
                            <td>
                                <input type="number" step="0.5" name="marks[{{ $st->id }}]" class="form-control" style="width:160px" value="{{ $existingRes->marks ?? '' }}" placeholder="e.g. 75">
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="3" style="text-align:center;padding:20px;color:var(--text-muted)">No active students found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-footer" style="text-align:right">
                <button type="submit" class="btn btn-success btn-lg">✓ Save Results & Publish Grades</button>
            </div>
        </div>
    </form>
</x-teacher-layout>
