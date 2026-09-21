<x-student-layout>
    <x-slot name="title">আমার অ্যাসাইনমেন্ট — My Assignments</x-slot>

    <div class="page-header">
        <div class="page-header-left">
            <h1 style="font-family:'Kalpurush', sans-serif">আমার অ্যাসাইনমেন্টসমূহ (Assignments)</h1>
            <p>আপনার রানিং কোর্সের সক্রিয় ও পূর্ববর্তী সকল বাড়ির কাজ এবং খাতার মূল্যায়ন ফলাফল</p>
        </div>
    </div>

    @if(session('success'))
        <div style="background:#ecfdf5;border:1px solid #a7f3d0;color:#047857;padding:12px 16px;border-radius:8px;margin-bottom:16px;font-size:13px;font-weight:600">
            {{ session('success') }}
        </div>
    @endif

    <div class="card">
        <div class="card-header" style="display:flex;justify-content:space-between;align-items:center">
            <span class="card-title">অ্যাসাইনমেন্ট তালিকা</span>
            <span class="badge badge-secondary no-dot">{{ $assignments->count() }}টি অ্যাসাইনমেন্ট</span>
        </div>
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>অ্যাসাইনমেন্ট শিরোনাম</th>
                        <th>বিষয়</th>
                        <th>পূর্ণমান</th>
                        <th>জমার শেষ সময়</th>
                        <th>আমার জমার স্ট্যাটাস</th>
                        <th>প্রাপ্ত ফলাফল / নম্বর</th>
                        <th style="text-align:right">অ্যাকশন</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($assignments as $asgn)
                    @php
                        $mySub = $asgn->submissions->first();
                        $isExpired = $asgn->isExpired();
                    @endphp
                    <tr>
                        <td class="td-primary">
                            <a href="{{ route('student.assignments.show', $asgn) }}" style="font-weight:700;color:var(--blue);font-size:14px">
                                {{ $asgn->title }}
                            </a>
                            @if($asgn->file_path)
                                <div style="margin-top:2px">
                                    <a href="{{ asset('storage/' . $asgn->file_path) }}" target="_blank" style="font-size:11px;color:#64748b;text-decoration:none">
                                        <i class="fa-solid fa-paperclip"></i> প্রশ্নপত্র সংযুক্ত
                                    </a>
                                </div>
                            @endif
                        </td>
                        <td>
                            <span class="badge badge-secondary no-dot">
                                {{ $asgn->subject?->code }} - {{ $asgn->subject?->name }}
                            </span>
                        </td>
                        <td><strong>{{ $asgn->total_marks }}</strong> নম্বর</td>
                        <td>
                            @if($isExpired)
                                <span class="badge" style="background:#fee2e2;color:#991b1b;font-size:11px;font-weight:700">
                                    {{ \Carbon\Carbon::parse($asgn->due_datetime)->format('d M Y, h:i A') }} (সময় শেষ)
                                </span>
                            @else
                                <span class="badge" style="background:#ecfdf5;color:#047857;font-size:11px;font-weight:700">
                                    {{ \Carbon\Carbon::parse($asgn->due_datetime)->format('d M Y, h:i A') }}
                                </span>
                            @endif
                        </td>
                        <td>
                            @if($mySub)
                                @if($mySub->status === 'GRADED')
                                    <span class="badge badge-active no-dot"><i class="fa-solid fa-circle-check"></i> মূল্যায়িত (Graded)</span>
                                @elseif($mySub->status === 'LATE')
                                    <span class="badge" style="background:#fee2e2;color:#991b1b"><i class="fa-solid fa-clock"></i> বিলম্বিত জমা (Late)</span>
                                @else
                                    <span class="badge badge-scheduled no-dot"><i class="fa-solid fa-inbox"></i> জমা দেওয়া হয়েছে</span>
                                @endif
                            @else
                                @if($isExpired)
                                    <span class="badge" style="background:#fee2e2;color:#991b1b"><i class="fa-solid fa-circle-xmark"></i> জমা দেননি</span>
                                @else
                                    <span class="badge" style="background:#fef3c7;color:#92400e"><i class="fa-solid fa-hourglass-half"></i> জমা দেওয়া বাকি</span>
                                @endif
                            @endif
                        </td>
                        <td>
                            @if($mySub && $mySub->status === 'GRADED')
                                <strong style="font-size:15px;color:#059669">{{ $mySub->obtained_marks }}</strong> / {{ $asgn->total_marks }}
                            @else
                                <span class="td-muted">—</span>
                            @endif
                        </td>
                        <td style="text-align:right">
                            <a href="{{ route('student.assignments.show', $asgn) }}" class="btn btn-primary btn-sm">
                                {{ $mySub ? 'বিস্তারিত দেখুন' : 'খাতা জমা দিন' }}
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" style="text-align:center;padding:40px;color:var(--text-muted)">
                            বর্তমানে আপনার জন্য কোনো সক্রিয় অ্যাসাইনমেন্ট নেই।
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-student-layout>
