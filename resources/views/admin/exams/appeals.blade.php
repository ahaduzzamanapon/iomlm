<x-admin-layout>
    <x-slot name="title">Re-Exam Appeals (পুনরায় পরীক্ষার আপিল)</x-slot>

    <div class="page-header">
        <div class="page-header-left">
            <h1>পুনরায় পরীক্ষার আপিল আবেদনসমূহ (Re-Exam Appeals)</h1>
            <p>অনিবার্য কারণে পরীক্ষা ব্যাহত হওয়া শিক্ষার্থীদের পুনরায় পরীক্ষার আবেদন পর্যালোচনা ও অনুমোদন</p>
        </div>
    </div>

    @if(session('success'))
        <div style="background:#ecfdf5;border:1px solid #a7f3d0;color:#047857;padding:12px 16px;border-radius:8px;margin-bottom:16px;font-size:13px;font-weight:600;display:flex;align-items:center;gap:8px">
            <i class="fa-solid fa-circle-check"></i> {{ session('success') }}
        </div>
    @endif

    @if(session('info'))
        <div style="background:#eff6ff;border:1px solid #bfdbfe;color:#1e40af;padding:12px 16px;border-radius:8px;margin-bottom:16px;font-size:13px;font-weight:600;display:flex;align-items:center;gap:8px">
            <i class="fa-solid fa-circle-info"></i> {{ session('info') }}
        </div>
    @endif

    {{-- Filter Bar --}}
    <div style="display:flex;gap:10px;margin-bottom:20px;flex-wrap:wrap">
        <a href="{{ route('admin.exams.appeals.index', ['status' => 'PENDING']) }}" class="btn btn-sm {{ $status === 'PENDING' ? 'btn-primary' : 'btn-outline' }}">
            <i class="fa-regular fa-clock"></i> অপেক্ষমাণ (Pending)
        </a>
        <a href="{{ route('admin.exams.appeals.index', ['status' => 'APPROVED']) }}" class="btn btn-sm {{ $status === 'APPROVED' ? 'btn-primary' : 'btn-outline' }}">
            <i class="fa-solid fa-check"></i> অনুমোদিত (Approved)
        </a>
        <a href="{{ route('admin.exams.appeals.index', ['status' => 'REJECTED']) }}" class="btn btn-sm {{ $status === 'REJECTED' ? 'btn-primary' : 'btn-outline' }}">
            <i class="fa-solid fa-xmark"></i> বাতিল (Rejected)
        </a>
        <a href="{{ route('admin.exams.appeals.index', ['status' => 'ALL']) }}" class="btn btn-sm {{ $status === 'ALL' ? 'btn-primary' : 'btn-outline' }}">
            সকল আবেদন (All)
        </a>
    </div>

    <div class="card">
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>শিক্ষার্থী</th>
                        <th>পরীক্ষা ও বিষয়</th>
                        <th style="min-width:280px">আবেদনের কারণ</th>
                        <th>জমার তারিখ</th>
                        <th>স্ট্যাটাস</th>
                        <th style="text-align:right">অ্যাকশন</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($appeals as $appeal)
                    <tr>
                        <td>
                            <strong>{{ $appeal->student?->name ?? '—' }}</strong><br>
                            <span style="font-family:monospace;font-size:12px;color:#64748b">ID: {{ $appeal->student?->student_code ?? $appeal->student?->student_id ?? '—' }}</span>
                        </td>
                        <td>
                            <strong>{{ $appeal->exam?->title ?? '—' }}</strong><br>
                            <span style="font-size:12px;color:#64748b">{{ $appeal->exam?->subject?->name ?? '—' }}</span>
                        </td>
                        <td>
                            <div style="font-size:13px;color:#1e293b;line-height:1.4">"{{ $appeal->reason }}"</div>
                            @if($appeal->admin_remarks)
                                <div style="font-size:11px;color:#64748b;margin-top:2px">
                                    <i class="fa-solid fa-comment-dots"></i> মন্তব্য: {{ $appeal->admin_remarks }}
                                </div>
                            @endif
                        </td>
                        <td class="td-muted" style="font-size:12px">
                            {{ $appeal->created_at ? $appeal->created_at->format('d M Y, h:i A') : '—' }}
                        </td>
                        <td>
                            @if($appeal->isPending())
                                <span class="badge badge-warning no-dot" style="background:#fef3c7;color:#92400e;border:1px solid #fde68a;font-weight:700">
                                    <i class="fa-regular fa-clock"></i> PENDING
                                </span>
                            @elseif($appeal->isApproved())
                                <span class="badge badge-success no-dot" style="background:#ecfdf5;color:#047857;border:1px solid #a7f3d0;font-weight:700">
                                    <i class="fa-solid fa-check-circle"></i> APPROVED
                                </span>
                                <div style="font-size:10px;color:#047857;margin-top:2px">অনুমোদন: {{ $appeal->reviewer?->name ?? 'Admin' }}</div>
                            @else
                                <span class="badge badge-danger no-dot" style="background:#fef2f2;color:#991b1b;border:1px solid #fecaca;font-weight:700">
                                    <i class="fa-solid fa-xmark"></i> REJECTED
                                </span>
                                <div style="font-size:10px;color:#991b1b;margin-top:2px">পর্যালোচনা: {{ $appeal->reviewer?->name ?? 'Admin' }}</div>
                            @endif
                        </td>
                        <td style="text-align:right;white-space:nowrap">
                            @if($appeal->isPending())
                                <form method="POST" action="{{ route('admin.exams.appeals.approve', $appeal) }}" style="display:inline" onsubmit="return confirm('এই আপিলটি অনুমোদন করতে চান? শিক্ষার্থীর পূর্বের খাতা রিসেট হয়ে যাবে এবং নতুন করে পরীক্ষা দিতে পারবে।')">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-primary" style="background:#059669;border-color:#047857;color:#fff;font-size:12px;font-weight:700" title="অনুমোদন করে পরীক্ষা রিসেট করুন">
                                        <i class="fa-solid fa-check"></i> অনুমোদন (Approve)
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('admin.exams.appeals.reject', $appeal) }}" style="display:inline" onsubmit="return confirm('এই আপিলটি বাতিল করতে চান?')">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline" style="color:#e11d48;border-color:#fecdd3;font-size:12px" title="আপিল বাতিল করুন">
                                        <i class="fa-solid fa-xmark"></i> বাতিল
                                    </button>
                                </form>
                            @else
                                <span style="font-size:12px;color:#94a3b8">প্রক্রিয়াজাত</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" style="text-align:center;padding:36px;color:var(--text-muted)">
                            কোনো পুনরায় পরীক্ষার আবেদন পাওয়া যায়নি।
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($appeals->hasPages())
            <div style="padding:16px">
                {{ $appeals->appends(['status' => $status])->links() }}
            </div>
        @endif
    </div>
</x-admin-layout>
