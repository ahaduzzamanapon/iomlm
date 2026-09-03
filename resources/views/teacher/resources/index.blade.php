<x-teacher-layout>
    <x-slot name="title">Learning Resources</x-slot>

    <div class="page-header">
        <div class="page-header-left">
            <h1>Learning Resources & Materials</h1>
            <p>Upload lecture notes, video recordings, PDFs, and assignments for subject modules</p>
        </div>
        <div class="page-header-actions">
            <button class="btn btn-primary" onclick="openModal('addResourceModal')">
                <i class="fa-solid fa-cloud-arrow-up"></i>
                Upload Resource
            </button>
        </div>
    </div>

    @if(session('success'))
        <div style="background:#ecfdf5;border:1px solid #a7f3d0;color:#047857;padding:12px 16px;border-radius:8px;margin-bottom:16px;font-size:13px;font-weight:600">
            ✓ {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div style="background:#fef2f2;border:1px solid #fecaca;color:#b91c1c;padding:12px 16px;border-radius:8px;margin-bottom:16px;font-size:13px">
            <strong><i class="fa-solid fa-triangle-exclamation"></i> কিছু ত্রুটি পাওয়া গেছে:</strong>
            <ul style="margin:6px 0 0 16px;padding:0">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card">
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Resource Title</th>
                        <th>Subject & Module</th>
                        <th>Type</th>
                        <th>Link / Preview</th>
                        <th>Uploaded On</th>
                        <th style="text-align:center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($resources as $res)
                    <tr>
                        <td class="td-primary"><strong>{{ $res->title }}</strong></td>
                        <td>
                            {{ $res->module->subject->name ?? '—' }}<br>
                            <span class="td-muted">Module: {{ $res->module->title ?? '—' }}</span>
                        </td>
                        <td><span class="badge badge-secondary no-dot">{{ $res->type }}</span></td>
                        <td>
                            @if($res->url && $res->url !== '#')
                                <a href="{{ $res->url }}" target="_blank" style="color:var(--blue);font-weight:600">Open Material <i class="fa-solid fa-arrow-up-right-from-square"></i></a>
                            @else
                                <span class="td-muted">Text / Attached</span>
                            @endif
                        </td>
                        <td class="td-muted">{{ $res->created_at->format('d M Y') }}</td>
                        <td style="text-align:center">
                            <form method="POST" action="{{ route('teacher.resources.destroy', $res) }}" onsubmit="return confirm('Delete this learning resource?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-outline btn-sm" style="color:#dc2626;border-color:#fca5a5;padding:4px 8px;font-size:11px"><i class="fa-solid fa-trash"></i> Delete</button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" style="text-align:center;padding:30px;color:var(--text-muted)">No learning resources uploaded yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Create Resource Modal -->
    <div class="modal-overlay" id="addResourceModal">
        <div class="modal">
            <div class="modal-header">
                <span class="modal-title">Upload Learning Resource</span>
                <button class="modal-close" onclick="closeModal('addResourceModal')">&times;</button>
            </div>
            <form method="POST" action="{{ route('teacher.resources.store') }}">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label>Select Subject Module <span class="required">*</span></label>
                        <select name="module_id" class="form-control" required>
                            <option value="">-- Choose Module --</option>
                            @foreach($modules->groupBy(fn($m) => ($m->subject?->name ?? 'Other Subjects') . ' (' . ($m->subject?->code ?? '') . ')') as $subjectGroup => $subModules)
                                <optgroup label="📚 {{ $subjectGroup }}">
                                    @foreach($subModules as $m)
                                        <option value="{{ $m->id }}">{{ $m->sequence_no ? 'Module ' . $m->sequence_no . ': ' : '' }}{{ $m->title }}</option>
                                    @endforeach
                                </optgroup>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Resource Title <span class="required">*</span></label>
                        <input type="text" name="title" class="form-control" placeholder="e.g. Chapter 1 PDF Lecture Notes" required>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Resource Type <span class="required">*</span></label>
                            <select name="type" class="form-control" required>
                                <option value="PDF">PDF Document</option>
                                <option value="VIDEO">Video Link</option>
                                <option value="NOTES">Lecture Notes / Summary</option>
                                <option value="SLIDES">Slides</option>
                                <option value="AUDIO">Audio</option>
                                <option value="LINK">External Resource Link</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>URL / File Link</label>
                            <input type="url" name="url" class="form-control" placeholder="https://drive.google.com/...">
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Content Summary / Notes</label>
                        <textarea name="content" class="form-control" placeholder="Brief outline or text content..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline" onclick="closeModal('addResourceModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Resource</button>
                </div>
            </form>
        </div>
    </div>
</x-teacher-layout>
