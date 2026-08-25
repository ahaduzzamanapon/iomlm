<x-student-layout>
    <x-slot name="title">Online Support & Helpdesk</x-slot>

    <div class="page-header">
        <div class="page-header-left">
            <h1>🎧 অনলাইন সাপোর্ট (Online Support)</h1>
            <p>আপনার যেকোনো শিক্ষা বা টেকনিক্যাল সহায়তার জন্য সাপোর্ট টিকিট জমা দিন বা মেসেজ করুন</p>
        </div>
    </div>

    @if(session('success'))
        <div style="background:#ecfdf5;border:1px solid #a7f3d0;color:#047857;padding:12px 16px;border-radius:8px;margin-bottom:16px;font-size:13px;font-weight:600">
            ✓ {{ session('success') }}
        </div>
    @endif

    <div class="grid-2">
        {{-- Student Support Form --}}
        <div class="card">
            <div class="card-header" style="background:#0f172a;color:#fff;border-radius:12px 12px 0 0">
                <span class="card-title">✍️ নতুন সাপোর্ট টিকিট খুলুন</span>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('online-support.store') }}">
                    @csrf

                    <div class="form-group">
                        <label>ডিপার্টমেন্ট নির্বাচন করুন <span class="required">*</span></label>
                        <select name="department_id" class="form-control" required>
                            <option value="">-- ডিপার্টমেন্ট সিলেক্ট করুন --</option>
                            @foreach($departments as $dept)
                                <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>আপনার নাম</label>
                            <input type="text" name="name" class="form-control" value="{{ $student?->name ?? $user->name }}" readonly style="background:#f1f5f9">
                        </div>
                        <div class="form-group">
                            <label>আপনার ফোন</label>
                            <input type="text" name="phone" class="form-control" value="{{ $student?->phone ?? '' }}" required style="background:#f1f5f9">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>ইমেইল এড্রেস</label>
                            <input type="email" name="email" class="form-control" value="{{ $user->email }}" readonly style="background:#f1f5f9">
                        </div>
                        <div class="form-group">
                            <label>জেন্ডার</label>
                            <select name="gender" class="form-control" required>
                                <option value="MALE" {{ strtoupper($student?->gender ?? 'MALE') === 'MALE' ? 'selected' : '' }}>Male / ভাই</option>
                                <option value="FEMALE" {{ strtoupper($student?->gender ?? 'MALE') === 'FEMALE' ? 'selected' : '' }}>Female / বোন</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>স্টুডেন্ট আইডি (Roll No)</label>
                        <input type="text" name="student_id" class="form-control" value="{{ $student?->student_id ?? '' }}" readonly style="background:#f1f5f9">
                    </div>

                    <div class="form-group">
                        <label>বিষয় / Subject <span class="required">*</span></label>
                        <input type="text" name="subject" class="form-control" placeholder="কী বিষয়ে সাহায্য চান?" required>
                    </div>

                    <div class="form-group">
                        <label>সমস্যার বিস্তারিত বর্ণনা <span class="required">*</span></label>
                        <textarea name="problem_details" class="form-control" rows="4" placeholder="আপনার সমস্যাটি বিস্তারিত লিখুন..." required></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary btn-lg" style="width:100%">
                        ✉️ সাপোর্ট অনুরোধ জমা দিন ও চ্যাট শুরু করুন
                    </button>
                </form>
            </div>
        </div>

        {{-- My Support Tickets History --}}
        <div class="card">
            <div class="card-header">
                <span class="card-title">📋 আমার পূর্বের সাপোর্ট টিকিটসমূহ ({{ $myTickets->count() }})</span>
            </div>
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>Ticket No</th>
                            <th>Subject & Dept</th>
                            <th>Status</th>
                            <th style="text-align:right">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($myTickets as $t)
                        <tr>
                            <td>
                                <strong style="color:#0284c7">{{ $t->ticket_no }}</strong>
                                <div style="font-size:11px;color:#64748b">{{ $t->created_at->format('d M Y') }}</div>
                            </td>
                            <td>
                                <strong>{{ $t->subject }}</strong>
                                <div style="font-size:11px;color:#64748b">🏢 {{ $t->department->name ?? '—' }}</div>
                            </td>
                            <td>
                                @if($t->status === 'PENDING')
                                    <span class="badge badge-pending">⏳ Pending Queue</span>
                                @elseif($t->status === 'IN_PROGRESS')
                                    <span class="badge badge-running">🟢 Live Chat Active</span>
                                @else
                                    <span class="badge badge-secondary">🔒 Closed</span>
                                @endif
                            </td>
                            <td style="text-align:right">
                                <a href="{{ route('online-support.chat', $t->uuid) }}" class="btn btn-outline btn-sm">
                                    💬 চ্যাট খুলুন →
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" style="text-align:center;padding:30px;color:#94a3b8">
                                আপনার কোনো পূর্ববর্তী সাপোর্ট টিকিট নেই।
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-student-layout>
