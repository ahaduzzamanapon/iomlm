<x-app-layout>
    <x-slot name="title">Survey &amp; Dynamic Form Builder</x-slot>

    <div class="page-header" style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px">
        <div class="page-header-left">
            <h1 style="display:flex; align-items:center; gap:8px">
                📋 Survey &amp; Dynamic Forms
            </h1>
            <p>Create Google Forms-like dynamic surveys, collect responses, and view automated dynamic tables</p>
        </div>
        <button class="btn btn-primary" onclick="openModal('createSurveyModal')">
            ➕ Create New Survey Form
        </button>
    </div>

    {{-- Filter & Search Bar --}}
    <div class="card" style="margin-bottom:20px; padding:16px">
        <form method="GET" action="{{ route('admin.surveys.index') }}" style="display:flex; gap:12px; flex-wrap:wrap; align-items:center">
            <div style="flex:1; min-width:240px">
                <input type="text" name="search" class="form-control" placeholder="🔍 Search survey forms by title or description..." value="{{ request('search') }}">
            </div>
            <button type="submit" class="btn btn-primary">Search</button>
            @if(request('search'))
                <a href="{{ route('admin.surveys.index') }}" class="btn btn-outline">Clear</a>
            @endif
        </form>
    </div>

    {{-- Survey Cards List --}}
    <div class="card">
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th style="width:50px">#</th>
                        <th>Survey Title &amp; Details</th>
                        <th style="text-align:center">Questions</th>
                        <th style="text-align:center">Responses</th>
                        <th style="text-align:center">Status</th>
                        <th>Created Date</th>
                        <th style="text-align:right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($surveys as $survey)
                    <tr>
                        <td style="font-weight:700; color:var(--text-muted)">{{ $loop->iteration }}</td>
                        <td>
                            <strong style="font-size:14px; color:#1e293b">{{ $survey->title }}</strong>
                            @if($survey->description)
                                <div style="font-size:12px; color:var(--text-muted); margin-top:2px">
                                    {{ Str::limit($survey->description, 80) }}
                                </div>
                            @endif
                            <div style="margin-top:6px; display:flex; align-items:center; gap:8px">
                                <span class="badge badge-secondary no-dot" style="font-size:10px">
                                    🔗 /surveys/{{ $survey->slug }}
                                </span>
                                <button type="button" class="btn btn-xs btn-outline" onclick="copyPublicLink('{{ url('/surveys/' . $survey->slug) }}')">
                                    📋 Copy Link
                                </button>
                                <a href="{{ url('/surveys/' . $survey->slug) }}" target="_blank" style="font-size:11px; color:#2563eb; text-decoration:none; font-weight:600">
                                    Preview Form ↗
                                </a>
                            </div>
                        </td>
                        <td style="text-align:center">
                            <span class="badge badge-secondary no-dot" style="font-weight:700; font-size:12px">
                                {{ $survey->fields_count }} Fields
                            </span>
                        </td>
                        <td style="text-align:center">
                            <a href="{{ route('admin.surveys.responses', $survey) }}" class="badge badge-primary no-dot" style="font-weight:700; font-size:12px; text-decoration:none">
                                📊 {{ $survey->responses_count }} Responses
                            </a>
                        </td>
                        <td style="text-align:center">
                            <form method="POST" action="{{ route('admin.surveys.toggle-status', $survey) }}" style="display:inline">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="badge {{ $survey->is_active ? 'badge-success' : 'badge-danger' }} no-dot" style="border:none; cursor:pointer; font-size:11px" title="Click to toggle status">
                                    {{ $survey->is_active ? '● Active' : '○ Closed' }}
                                </button>
                            </form>
                        </td>
                        <td class="td-muted" style="font-size:12px">
                            {{ $survey->created_at->format('d M Y, h:i A') }}
                        </td>
                        <td style="text-align:right">
                            <div style="display:flex; justify-content:flex-end; gap:6px">
                                <a href="{{ route('admin.surveys.builder', $survey) }}" class="btn btn-primary btn-sm" title="Edit Form Structure">
                                    ✏️ Form Builder
                                </a>
                                <a href="{{ route('admin.surveys.responses', $survey) }}" class="btn btn-outline btn-sm" title="View Responses">
                                    📊 Responses
                                </a>
                                <form method="POST" action="{{ route('admin.surveys.destroy', $survey) }}" onsubmit="return confirm('Are you sure you want to delete this survey form?')" style="display:inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline btn-sm danger" title="Delete Survey">
                                        🗑️
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" style="text-align:center; padding:40px; color:var(--text-muted)">
                            <div style="font-size:24px; margin-bottom:8px">📋</div>
                            <strong>No survey forms created yet.</strong><br>
                            Click <em>"Create New Survey Form"</em> above to build your first dynamic form.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($surveys->hasPages())
            <div style="padding:16px">
                {{ $surveys->links() }}
            </div>
        @endif
    </div>

    {{-- Create New Survey Modal --}}
    <div class="modal-overlay" id="createSurveyModal">
        <div class="modal-card" style="max-width:550px">
            <div class="modal-header">
                <h3>📋 Create New Survey Form</h3>
                <button class="modal-close" onclick="closeModal('createSurveyModal')">&times;</button>
            </div>
            <form method="POST" action="{{ route('admin.surveys.store') }}" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="form-group" style="margin-bottom:16px">
                        <label class="form-label required">Survey Title</label>
                        <input type="text" name="title" class="form-control" placeholder="e.g., Student Course Feedback Survey 2026" required>
                    </div>

                    <div class="form-group" style="margin-bottom:16px">
                        <label class="form-label">Description / Instructions</label>
                        <textarea name="description" class="form-control" rows="3" placeholder="Provide a brief welcome message or instructions for respondents..."></textarea>
                    </div>

                    <div class="form-group" style="margin-bottom:16px">
                        <label class="form-label">Header Banner Image (Optional)</label>
                        <input type="file" name="banner" class="form-control" accept="image/*">
                        <div style="font-size:11px; color:var(--text-muted); margin-top:4px">Upload a cover banner photo for the public survey form header.</div>
                    </div>

                    <div class="form-group" style="margin-bottom:16px">
                        <label style="display:flex; align-items:center; gap:8px; cursor:pointer">
                            <input type="checkbox" name="allow_multiple_responses" value="1">
                            <span style="font-weight:600; font-size:13px">Allow multiple submissions per user / IP</span>
                        </label>
                    </div>
                </div>
                <div class="modal-footer" style="display:flex; justify-content:flex-end; gap:8px">
                    <button type="button" class="btn btn-outline" onclick="closeModal('createSurveyModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create &amp; Open Builder →</button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
    function copyPublicLink(url) {
        navigator.clipboard.writeText(url).then(() => {
            alert('Public link copied to clipboard:\n' + url);
        }).catch(err => {
            prompt('Copy this link:', url);
        });
    }
    </script>
    @endpush
</x-app-layout>
