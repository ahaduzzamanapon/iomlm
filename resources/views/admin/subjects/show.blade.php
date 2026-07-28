<x-admin-layout>
    <x-slot name="title">{{ $subject->name }} — Modules</x-slot>

    <div class="page-header">
        <div class="page-header-left">
            <div style="font-size:12px;color:var(--text-muted);margin-bottom:4px">
                <a href="{{ route('admin.subjects.index') }}">← Back to Subjects</a>
            </div>
            <h1>{{ $subject->code }}: {{ $subject->name }}</h1>
            <p>Credit: {{ $subject->credit }} · Full Marks: {{ $subject->full_marks }} · Pass Marks: {{ $subject->pass_marks }}</p>
        </div>
        <div class="page-header-actions">
            <button class="btn btn-primary" onclick="openModal('addModuleModal')">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                Add Module
            </button>
        </div>
    </div>

    <!-- Modules List -->
    <div class="card">
        <div class="card-header">
            <span class="card-title">Subject Modules (Sequential Learning Engine)</span>
            <span class="badge badge-secondary no-dot">{{ $subject->modules->count() }} Modules Configured</span>
        </div>
        <div style="padding:16px">
            <div class="module-list">
                @forelse($subject->modules as $mod)
                <div class="module-item" style="display:flex;align-items:center;justify-content:space-between">
                    <div style="display:flex;align-items:center;gap:12px">
                        <div class="module-seq">{{ $mod->sequence_no }}</div>
                        <div>
                            <div class="module-title">{{ $mod->title }}</div>
                            <div class="module-sub">
                                {{ $mod->description ?? 'No description' }}
                                @if($mod->is_locked_until_previous)
                                    · <span style="color:var(--orange)">🔒 Locked until Module {{ $mod->sequence_no - 1 }} completed</span>
                                @else
                                    · <span style="color:var(--green)">🔓 Unlocked (Can schedule independently)</span>
                                @endif
                            </div>
                        </div>
                    </div>
                    <form method="POST" action="{{ route('admin.modules.destroy', $mod) }}" style="display:inline" onsubmit="return confirm('Delete module?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-ghost btn-sm text-red">Delete</button>
                    </form>
                </div>
                @empty
                <div class="empty-state">
                    <p>No modules created yet. Subject modules are the engine of Learning Plus ERP!</p>
                </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Add Module Modal -->
    <div class="modal-overlay" id="addModuleModal">
        <div class="modal">
            <div class="modal-header">
                <span class="modal-title">Add Module to {{ $subject->code }}</span>
                <button class="modal-close" onclick="closeModal('addModuleModal')">&times;</button>
            </div>
            <form method="POST" action="{{ route('admin.subjects.modules.store', $subject) }}">
                @csrf
                <div class="modal-body">
                    <div class="form-row">
                        <div class="form-group">
                            <label>Sequence No. <span class="required">*</span></label>
                            <input type="number" name="sequence_no" class="form-control" value="{{ $subject->modules->count() + 1 }}" min="1" required>
                        </div>
                        <div class="form-group">
                            <label>Module Title <span class="required">*</span></label>
                            <input type="text" name="title" class="form-control" placeholder="e.g. Introduction to Variables" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Description</label>
                        <textarea name="description" class="form-control" placeholder="Brief outline of module topics..."></textarea>
                    </div>
                    <label class="form-check">
                        <input type="checkbox" name="is_locked_until_previous" value="1" checked> Lock until previous module is COMPLETED
                    </label>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline" onclick="closeModal('addModuleModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Module</button>
                </div>
            </form>
        </div>
    </div>
</x-admin-layout>
