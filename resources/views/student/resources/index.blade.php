<x-student-layout>
    <x-slot name="title">Learning Materials</x-slot>

    <div class="page-header">
        <div class="page-header-left">
            <h1>Learning Materials & Resources</h1>
            <p>Access downloadable PDFs, class recordings, and lecture notes</p>
        </div>
    </div>

    <div class="card">
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Material Title</th>
                        <th>Subject & Module</th>
                        <th>Type</th>
                        <th>Action</th>
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
                                <a href="{{ $res->url }}" target="_blank" class="btn btn-outline btn-sm">Download / Open ↗</a>
                            @else
                                <span class="td-muted">Text Note</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="4" style="text-align:center;padding:30px;color:var(--text-muted)">No learning resources available yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-student-layout>
