<x-student-layout>
    <x-slot name="title">My Documents</x-slot>

    <div class="page-header">
        <div class="page-header-left">
            <h1>Academic Documents & Certificates</h1>
            <p>Generate digital transcripts, certificates, marksheets, and completion letters</p>
        </div>
    </div>

    <!-- Quick Document Generation Buttons -->
    <div class="card" style="margin-bottom:24px">
        <div class="card-header"><span class="card-title">Request / Generate Official Document</span></div>
        <div class="card-body" style="display:flex;gap:12px;flex-wrap:wrap">
            <form method="POST" action="{{ route('student.documents.generate', 'certificate') }}">
                @csrf
                <button type="submit" class="btn btn-outline">Course Certificate</button>
            </form>
            <form method="POST" action="{{ route('student.documents.generate', 'transcript') }}">
                @csrf
                <button type="submit" class="btn btn-outline">Academic Transcript</button>
            </form>
            <form method="POST" action="{{ route('student.documents.generate', 'marksheet') }}">
                @csrf
                <button type="submit" class="btn btn-outline">Semester Marksheet</button>
            </form>
            <form method="POST" action="{{ route('student.documents.generate', 'completion_letter') }}">
                @csrf
                <button type="submit" class="btn btn-outline">Completion Letter</button>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header"><span class="card-title">Generated Documents History</span></div>
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Document Number</th>
                        <th>Document Type</th>
                        <th>Generated On</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($documents as $doc)
                    <tr>
                        <td><span class="badge badge-active no-dot"><strong>{{ $doc->document_number }}</strong></span></td>
                        <td>{{ str_replace('_', ' ', $doc->type) }}</td>
                        <td class="td-muted">{{ \Carbon\Carbon::parse($doc->generated_at)->format('d M Y, h:i A') }}</td>
                        <td>
                            <button class="btn btn-outline btn-sm" onclick="alert('Viewing document preview for {{ $doc->document_number }}')">View PDF</button>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="4" style="text-align:center;padding:30px;color:var(--text-muted)">No documents generated yet. Click any button above to generate.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-student-layout>
