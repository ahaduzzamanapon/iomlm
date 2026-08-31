<x-admin-layout>
    <x-slot name="title">Survey Responses — {{ $survey->title }}</x-slot>

    <div class="page-header" style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px; margin-bottom:20px">
        <div class="page-header-left">
            <h1 style="display:flex; align-items:center; gap:10px">
                📊 Responses: {{ $survey->title }}
            </h1>
            <p>Automated response analytics table with dynamic question columns and CSV export</p>
        </div>
        <div style="display:flex; gap:8px; align-items:center">
            <a href="{{ route('admin.surveys.responses.csv', $survey) }}" class="btn btn-primary">
                📥 Export CSV / Excel
            </a>
            <a href="{{ route('admin.surveys.builder', $survey) }}" class="btn btn-outline">
                ✏️ Edit Form Builder
            </a>
            <a href="{{ route('admin.surveys.index') }}" class="btn btn-secondary">
                ← Back to Surveys
            </a>
        </div>
    </div>

    {{-- Response Stats Cards --}}
    <div class="stats-grid" style="grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; margin-bottom: 20px;">
        <div class="stat-card">
            <div class="stat-icon green">📊</div>
            <div class="stat-info">
                <div class="stat-value" style="font-weight:800; font-size:22px">{{ $survey->responses()->count() }}</div>
                <div class="stat-label">Total Responses Received</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon blue">📝</div>
            <div class="stat-info">
                <div class="stat-value" style="font-weight:800; font-size:22px">{{ $fields->count() }}</div>
                <div class="stat-label">Questions / Form Fields</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon {{ $survey->is_active ? 'green' : 'red' }}">
                {{ $survey->is_active ? '●' : '○' }}
            </div>
            <div class="stat-info">
                <div class="stat-value" style="font-size:16px; font-weight:700; color:{{ $survey->is_active ? '#10b981' : '#ef4444' }}">
                    {{ $survey->is_active ? 'Active & Accepting' : 'Closed' }}
                </div>
                <div class="stat-label">Form Public Status</div>
            </div>
        </div>
    </div>

    {{-- AUTOMATED DYNAMIC RESPONSES TABLE --}}
    <div class="card">
        <div class="card-header" style="display:flex; justify-content:space-between; align-items:center">
            <span class="card-title">📑 All Submitted Responses</span>
            <span style="font-size:12px; color:var(--text-muted)">Columns auto-generated from survey questions</span>
        </div>
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr style="background:#f8fafc">
                        <th style="width:50px">#</th>
                        <th>Respondent Info</th>
                        <th>Submitted At</th>
                        {{-- Dynamic Columns for each survey field --}}
                        @foreach($fields as $field)
                            <th style="min-width:160px; max-width:260px">
                                {{ $field->label }}
                                <div style="font-size:10px; color:var(--text-muted); font-weight:400">
                                    Type: {{ strtoupper($field->field_type) }}
                                </div>
                            </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @forelse($responses as $resp)
                        @php
                            $answersMap = $resp->answers->keyBy('survey_field_id');
                        @endphp
                        <tr>
                            <td style="font-weight:700; color:var(--text-muted)">#{{ $resp->id }}</td>
                            <td>
                                <strong>{{ $resp->respondent_name ?? 'Anonymous' }}</strong>
                                @if($resp->respondent_email)
                                    <div style="font-size:11px; color:var(--text-muted)">{{ $resp->respondent_email }}</div>
                                @endif
                                @if($resp->user)
                                    <span class="badge badge-secondary no-dot" style="font-size:9px">User ID #{{ $resp->user_id }}</span>
                                @endif
                            </td>
                            <td class="td-muted" style="font-size:12px">
                                {{ $resp->created_at->format('d M Y, h:i A') }}<br>
                                <span style="font-size:10px; color:var(--text-muted)">IP: {{ $resp->ip_address ?? '—' }}</span>
                            </td>

                            {{-- Dynamic Answer Cells --}}
                            @foreach($fields as $field)
                                @php
                                    $answer = $answersMap->get($field->id);
                                    $rawVal = $answer ? $answer->answer_value : null;
                                @endphp
                                <td>
                                    @if(empty($rawVal))
                                        <span style="color:var(--text-muted)">—</span>
                                    @elseif(in_array($field->field_type, ['file', 'image']))
                                        <a href="{{ asset($rawVal) }}" target="_blank" class="btn btn-xs btn-outline" style="font-size:11px">
                                            {{ $field->field_type === 'image' ? '🖼️ View Image' : '📎 Download File' }}
                                        </a>
                                    @else
                                        <span style="font-size:13px; color:#1e293b">
                                            {{ $answer->formatted_answer }}
                                        </span>
                                    @endif
                                </td>
                            @endforeach
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ 3 + $fields->count() }}" style="text-align:center; padding:40px; color:var(--text-muted)">
                                <div style="font-size:24px; margin-bottom:8px">📥</div>
                                <strong>No responses submitted yet for this survey.</strong><br>
                                Share the public link to start receiving responses.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($responses->hasPages())
            <div style="padding:16px">
                {{ $responses->links() }}
            </div>
        @endif
    </div>
</x-admin-layout>
