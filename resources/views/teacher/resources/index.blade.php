<x-teacher-layout>
    <x-slot name="title">Learning Resources</x-slot>

    <div class="page-header">
        <div class="page-header-left">
            <h1>Learning Resources & Materials</h1>
            <p>Upload lecture notes, video recordings, PDFs, and assignments for subject modules</p>
        </div>
        <div class="page-header-actions">
            <button class="btn btn-primary" onclick="openModal('addResourceModal')">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                Upload Resource
            </button>
        </div>
    </div>

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
                            @if($res->url)
                                <a href="{{ $res->url }}" target="_blank" style="color:var(--blue);font-weight:600">Open Material ↗</a>
                            @else
                                <span class="td-muted">Text Content</span>
                            @endif
                        </td>
                        <td class="td-muted">{{ $res->created_at->format('d M Y') }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="5" style="text-align:center;padding:30px;color:var(--text-muted)">No learning resources uploaded yet.</td></tr>
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
                        <label>Select Module <span class="required">*</span></label>
                        <select name="subject_module_id" class="form-control" required>
                            <option value="">-- Choose Module --</option>
                            @foreach($modules as $m)
                                <option value="{{ $m->id }}">{{ $m->subject->code ?? '' }}: {{ $m->title }}</option>
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
                                <option value="RECORDING">Class Recording</option>
                                <option value="NOTES">Lecture Notes</option>
                                <option value="ASSIGNMENT">Assignment</option>
                                <option value="QUIZ">Quiz</option>
                                <option value="PRACTICAL">Practical Lab</option>
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
