<x-admin-layout>
    <x-slot name="title">Survey &amp; Dynamic Form Builder</x-slot>

    <style>
        .survey-header-card {
            background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
            border-radius: 16px;
            padding: 28px 32px;
            color: #ffffff;
            margin-bottom: 24px;
            box-shadow: 0 10px 30px rgba(15, 23, 42, 0.15);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 16px;
        }
        .btn-gradient-primary {
            background: linear-gradient(135deg, #2563eb 0%, #3b82f6 100%);
            color: #ffffff !important;
            font-weight: 700;
            border: none;
            padding: 11px 24px;
            border-radius: 10px;
            box-shadow: 0 4px 14px rgba(37, 99, 235, 0.35);
            transition: all .2s cubic-bezier(0.4, 0, 0.2, 1);
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
            cursor: pointer;
        }
        .btn-gradient-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(37, 99, 235, 0.45);
        }
        .survey-table-card {
            background: var(--card-bg, #ffffff);
            border-radius: 16px;
            border: 1px solid var(--card-border, #e2e8f0);
            box-shadow: 0 4px 20px rgba(0,0,0,0.03);
            overflow: hidden;
        }
        .survey-link-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 3px 10px;
            background: rgba(59, 130, 246, 0.08);
            color: #2563eb;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
            border: 1px solid rgba(59, 130, 246, 0.2);
        }
        /* Glassmorphism Modal */
        .modal-overlay {
            background: rgba(15, 23, 42, 0.65) !important;
            backdrop-filter: blur(8px) !important;
        }
        .modal-card-premium {
            background: #ffffff;
            border-radius: 20px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            border: 1px solid rgba(226, 232, 240, 0.8);
            overflow: hidden;
        }
        .modal-header-premium {
            background: linear-gradient(135deg, #1e293b, #0f172a);
            color: #ffffff;
            padding: 20px 28px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .modal-header-premium h3 {
            margin: 0;
            font-size: 18px;
            font-weight: 700;
            letter-spacing: -0.01em;
            color: #ffffff;
        }
        .modal-body-premium {
            padding: 28px;
        }
        .modal-footer-premium {
            padding: 16px 28px;
            background: #f8fafc;
            border-top: 1px solid #e2e8f0;
            display: flex;
            justify-content: flex-end;
            gap: 12px;
        }
    </style>

    {{-- Premium Header Banner --}}
    <div class="survey-header-card">
        <div>
            <h1 style="margin:0 0 6px; font-size:24px; font-weight:800; color:#ffffff; display:flex; align-items:center; gap:10px">
                📋 Survey &amp; Dynamic Form Builder
            </h1>
            <p style="margin:0; font-size:13.5px; color:#94a3b8">
                Build Google Forms-like custom surveys, share public links, and view automated dynamic response tables
            </p>
        </div>
        <button class="btn-gradient-primary" onclick="openModal('createSurveyModal')">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
            Create New Survey Form
        </button>
    </div>

    {{-- Search & Filter Bar --}}
    <div class="card" style="margin-bottom:20px; padding:16px; border-radius:14px">
        <form method="GET" action="{{ route('admin.surveys.index') }}" style="display:flex; gap:12px; flex-wrap:wrap; align-items:center">
            <div style="flex:1; min-width:260px; position:relative">
                <input type="text" name="search" class="form-control" placeholder="🔍 Search survey forms by title or description..." value="{{ request('search') }}" style="padding-left:14px">
            </div>
            <button type="submit" class="btn btn-primary" style="font-weight:600">Search</button>
            @if(request('search'))
                <a href="{{ route('admin.surveys.index') }}" class="btn btn-outline">Reset Filter</a>
            @endif
        </form>
    </div>

    {{-- Survey Table List --}}
    <div class="survey-table-card">
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr style="background:#f8fafc">
                        <th style="width:50px">#</th>
                        <th>Survey Title &amp; Public Link</th>
                        <th style="text-align:center">Questions</th>
                        <th style="text-align:center">Responses</th>
                        <th style="text-align:center">Form Status</th>
                        <th>Created Date</th>
                        <th style="text-align:right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($surveys as $survey)
                    <tr>
                        <td style="font-weight:700; color:var(--text-muted)">{{ $loop->iteration }}</td>
                        <td>
                            <strong style="font-size:15px; color:#0f172a">{{ $survey->title }}</strong>
                            @if($survey->description)
                                <div style="font-size:12.5px; color:#64748b; margin-top:3px; line-height:1.4">
                                    {{ Str::limit($survey->description, 90) }}
                                </div>
                            @endif
                            <div style="margin-top:8px; display:flex; align-items:center; gap:8px; flex-wrap:wrap">
                                <span class="survey-link-pill">
                                    🔗 /surveys/{{ $survey->slug }}
                                </span>
                                <button type="button" class="btn btn-xs btn-outline" style="border-radius:6px; font-weight:600" onclick="copyPublicLink('{{ url('/surveys/' . $survey->slug) }}')">
                                    📋 Copy Link
                                </button>
                                <a href="{{ url('/surveys/' . $survey->slug) }}" target="_blank" style="font-size:11.5px; color:#2563eb; text-decoration:none; font-weight:700">
                                    Preview Form ↗
                                </a>
                            </div>
                        </td>
                        <td style="text-align:center">
                            <span class="badge badge-secondary no-dot" style="font-weight:700; font-size:12px; padding:5px 12px; border-radius:20px">
                                {{ $survey->fields_count }} Fields
                            </span>
                        </td>
                        <td style="text-align:center">
                            <a href="{{ route('admin.surveys.responses', $survey) }}" class="badge badge-primary no-dot" style="font-weight:700; font-size:12px; padding:5px 12px; border-radius:20px; text-decoration:none; background:linear-gradient(135deg,#2563eb,#3b82f6)">
                                📊 {{ $survey->responses_count }} Responses
                            </a>
                        </td>
                        <td style="text-align:center">
                            <form method="POST" action="{{ route('admin.surveys.toggle-status', $survey) }}" style="display:inline">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="badge {{ $survey->is_active ? 'badge-success' : 'badge-danger' }} no-dot" style="border:none; cursor:pointer; font-size:11px; padding:5px 12px; border-radius:20px; font-weight:700" title="Click to toggle active status">
                                    {{ $survey->is_active ? '● Active' : '○ Closed' }}
                                </button>
                            </form>
                        </td>
                        <td class="td-muted" style="font-size:12px">
                            {{ $survey->created_at->format('d M Y, h:i A') }}
                        </td>
                        <td style="text-align:right">
                            <div style="display:flex; justify-content:flex-end; gap:6px">
                                <a href="{{ route('admin.surveys.builder', $survey) }}" class="btn btn-primary btn-sm" style="font-weight:600" title="Form Builder">
                                    ✏️ Form Builder
                                </a>
                                <a href="{{ route('admin.surveys.responses', $survey) }}" class="btn btn-outline btn-sm" style="font-weight:600" title="View Responses">
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
                        <td colspan="7" style="text-align:center; padding:50px 20px; color:var(--text-muted)">
                            <div style="font-size:36px; margin-bottom:12px">📋</div>
                            <strong style="font-size:16px; color:#1e293b">No survey forms created yet.</strong><br>
                            <span style="font-size:13px; color:#64748b">Click <em>"Create New Survey Form"</em> above to build your first dynamic form.</span>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($surveys->hasPages())
            <div style="padding:16px; border-top:1px solid #e2e8f0">
                {{ $surveys->links() }}
            </div>
        @endif
    </div>

    {{-- Premium Glassmorphism Modal --}}
    <div class="modal-overlay" id="createSurveyModal">
        <div class="modal-card modal-card-premium" style="max-width:560px; margin:auto">
            <div class="modal-header-premium">
                <h3>📋 Create New Survey Form</h3>
                <button type="button" class="modal-close" style="color:#ffffff; opacity:0.8" onclick="closeModal('createSurveyModal')">&times;</button>
            </div>
            <form method="POST" action="{{ route('admin.surveys.store') }}" enctype="multipart/form-data">
                @csrf
                <div class="modal-body-premium">
                    <div class="form-group" style="margin-bottom:18px">
                        <label class="form-label required" style="font-weight:700; font-size:13.5px; color:#0f172a">Survey Title</label>
                        <input type="text" name="title" class="form-control" placeholder="e.g., Student Course Feedback Survey 2026" style="padding:11px 14px; font-size:14px; font-weight:600" required>
                    </div>

                    <div class="form-group" style="margin-bottom:18px">
                        <label class="form-label" style="font-weight:600; font-size:13px; color:#334155">Description / Instructions (Optional)</label>
                        <textarea name="description" class="form-control" rows="3" placeholder="Provide a brief welcome message or instructions for respondents..." style="padding:11px 14px; font-size:13.5px"></textarea>
                    </div>

                    <div class="form-group" style="margin-bottom:18px">
                        <label class="form-label" style="font-weight:600; font-size:13px; color:#334155">Header Banner Image (Optional)</label>
                        <input type="file" name="banner" class="form-control" accept="image/*" style="padding:8px">
                        <div style="font-size:11.5px; color:#64748b; margin-top:5px">Upload a cover banner photo for the public survey form header.</div>
                    </div>

                    <div class="form-group" style="margin-bottom:6px">
                        <label style="display:flex; align-items:center; gap:10px; cursor:pointer; background:#f8fafc; padding:12px 16px; border-radius:10px; border:1px solid #e2e8f0">
                            <input type="checkbox" name="allow_multiple_responses" value="1" style="width:16px; height:16px; accent-color:#2563eb">
                            <span style="font-weight:600; font-size:13px; color:#1e293b">Allow multiple submissions per user / IP</span>
                        </label>
                    </div>
                </div>
                <div class="modal-footer-premium">
                    <button type="button" class="btn btn-outline" style="padding:9px 20px; font-weight:600" onclick="closeModal('createSurveyModal')">Cancel</button>
                    <button type="submit" class="btn-gradient-primary" style="padding:9px 22px">
                        Create &amp; Open Builder →
                    </button>
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
</x-admin-layout>
