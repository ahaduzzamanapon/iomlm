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
                    <div style="display:flex;align-items:center;gap:12px;flex:1;min-width:0">
                        <div class="module-seq">{{ $mod->sequence_no }}</div>
                        <div style="flex:1;min-width:0">
                            <div style="display:flex;align-items:center;gap:8px">
                                @if($mod->category)
                                    <span class="badge badge-secondary no-dot" style="font-size:11px;background:rgba(59,130,246,.1);color:var(--blue)">📁 {{ $mod->category }}</span>
                                @endif
                                <span class="module-title">{{ $mod->title }}</span>
                            </div>
                            @if($mod->description)
                            <div class="module-sub" style="margin-top:3px;color:var(--text-muted);font-size:12px">
                                {{ $mod->description }}
                            </div>
                            @else
                            <div class="module-sub" style="margin-top:3px;color:var(--text-muted);font-size:12px;font-style:italic">No description</div>
                            @endif
                        </div>
                    </div>
                    <div style="display:flex;align-items:center;gap:6px;flex-shrink:0;margin-left:12px">
                        <button type="button" class="btn btn-outline btn-sm"
                            onclick="openEditModal({{ $mod->id }}, '{{ addslashes($mod->title) }}', '{{ addslashes($mod->category ?? '') }}', {{ $mod->sequence_no }}, '{{ addslashes($mod->description ?? '') }}')">
                            Edit
                        </button>
                        <form method="POST" action="{{ route('admin.modules.destroy', $mod) }}" style="display:inline" onsubmit="return confirm('Delete module?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-ghost btn-sm text-red">Delete</button>
                        </form>
                    </div>
                </div>
                @empty
                <div class="empty-state">
                    <p>No modules created yet. Subject modules are the engine of IOM ERP!</p>
                </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- ── Add Module Modal ── -->
    <div class="modal-overlay" id="addModuleModal">
        <div class="modal">
            <div class="modal-header">
                <span class="modal-title">Add Module to {{ $subject->code }}</span>
                <button class="modal-close" onclick="closeModal('addModuleModal')">&times;</button>
            </div>
            <form method="POST" action="{{ route('admin.subjects.modules.store', $subject) }}">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label>Category / Topic Grouping</label>
                        <input type="text" name="category" class="form-control" placeholder="e.g. ফিক্বহ, আক্বিদা, তাজবীদ">
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Sequence No. <span class="required">*</span></label>
                            <input type="number" name="sequence_no" class="form-control" value="{{ $subject->modules->count() + 1 }}" min="1" required>
                        </div>
                        <div class="form-group">
                            <label>Module Title <span class="required">*</span></label>
                            <input type="text" name="title" class="form-control" placeholder="e.g. DWH-1: দাওয়াহর মূলনীতি" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Description</label>
                        <textarea name="description" class="form-control" rows="3" placeholder="Brief outline of module topics..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline" onclick="closeModal('addModuleModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Module</button>
                </div>
            </form>
        </div>
    </div>

    <!-- ── Edit Module Modal ── -->
    <div class="modal-overlay" id="editModuleModal">
        <div class="modal">
            <div class="modal-header">
                <span class="modal-title">Edit Module</span>
                <button class="modal-close" onclick="closeModal('editModuleModal')">&times;</button>
            </div>
            <form method="POST" id="editModuleForm" action="">
                @csrf @method('PUT')
                <div class="modal-body">
                    <div class="form-group">
                        <label>Category / Topic Grouping</label>
                        <input type="text" name="category" id="edit_category" class="form-control" placeholder="e.g. ফিক্বহ, আক্বিদা">
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Sequence No. <span class="required">*</span></label>
                            <input type="number" name="sequence_no" id="edit_sequence_no" class="form-control" min="1" required>
                        </div>
                        <div class="form-group">
                            <label>Module Title <span class="required">*</span></label>
                            <input type="text" name="title" id="edit_title" class="form-control" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Description</label>
                        <textarea name="description" id="edit_description" class="form-control" rows="4" placeholder="Brief outline of module topics..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline" onclick="closeModal('editModuleModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Module</button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
    function openEditModal(id, title, category, seqNo, description) {
        document.getElementById('editModuleForm').action = '/admin/modules/' + id;
        document.getElementById('edit_title').value       = title;
        document.getElementById('edit_category').value    = category;
        document.getElementById('edit_sequence_no').value = seqNo;
        document.getElementById('edit_description').value = description;
        openModal('editModuleModal');
    }
    </script>
    @endpush

</x-admin-layout>
