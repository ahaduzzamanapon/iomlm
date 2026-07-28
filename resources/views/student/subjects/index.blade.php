<x-student-layout>
    <x-slot name="title">My Subjects</x-slot>

    <div class="page-header">
        <div class="page-header-left">
            <h1>My Enrolled Subjects</h1>
            <p>Course outline and sequential modules for your active program</p>
        </div>
    </div>

    <div class="grid-2">
        @forelse($enrollments as $enr)
        @foreach($enr->course->subjects as $subj)
        <div class="card">
            <div class="card-header">
                <div>
                    <span class="badge badge-secondary no-dot">{{ $subj->code }}</span>
                    <strong style="font-size:15px;margin-left:6px">{{ $subj->name }}</strong>
                </div>
                <span class="badge badge-active no-dot">{{ $subj->credit }} Credit</span>
            </div>
            <div class="card-body">
                <div class="module-list">
                    @foreach($subj->modules as $mod)
                        <div class="module-item" style="padding:8px 12px;font-size:13px">
                            <strong>Module {{ $mod->sequence_no }}:</strong> {{ $mod->title }}
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endforeach
        @empty
        <div class="empty-state" style="grid-column:1/-1">
            <p>No active subject enrollments found.</p>
        </div>
        @endforelse
    </div>
</x-student-layout>
