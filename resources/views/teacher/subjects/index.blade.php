<x-teacher-layout>
    <x-slot name="title">My Subjects</x-slot>

    <div class="page-header">
        <div class="page-header-left">
            <h1>My Assigned Subjects & Curriculum</h1>
            <p>View course outlines and module structures for your assigned teaching subjects</p>
        </div>
    </div>

    <div class="grid-2">
        @forelse($assignments as $asgn)
        @php $subj = $asgn->subject; @endphp
        <div class="card">
            <div class="card-header">
                <div>
                    <span class="badge badge-secondary no-dot">{{ $subj->code }}</span>
                    <strong style="font-size:15px;margin-left:6px">{{ $subj->name }}</strong>
                </div>
                <span class="badge badge-active no-dot">{{ $subj->credit }} Credit</span>
            </div>
            <div class="card-body">
                <p style="font-size:12px;color:var(--text-muted);margin-bottom:12px">
                    Full Marks: {{ $subj->full_marks }} · Pass Marks: {{ $subj->pass_marks }} · {{ $subj->modules->count() }} Modules
                </p>
                <div class="module-list">
                    @foreach($subj->modules as $mod)
                        <div class="module-item" style="padding:8px 12px;font-size:13px">
                            <strong>Module {{ $mod->sequence_no }}:</strong> {{ $mod->title }}
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
        @empty
        <div class="empty-state" style="grid-column:1/-1">
            <p>No subjects assigned to your faculty profile yet.</p>
        </div>
        @endforelse
    </div>
</x-teacher-layout>
