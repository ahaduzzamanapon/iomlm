<x-student-layout>
    <x-slot name="title">{{ $assignment->title }} — Submission</x-slot>

    <div class="page-header">
        <div class="page-header-left">
            <div style="font-size:12px;color:var(--text-muted);margin-bottom:4px">
                <a href="{{ route('student.assignments.index') }}"><i class="fa-solid fa-arrow-left"></i> সকল অ্যাসাইনমেন্টে ফিরে যান</a>
            </div>
            <h1 style="font-family:'Kalpurush', sans-serif">{{ $assignment->title }}</h1>
            <p>
                বিষয়: <strong>{{ $assignment->subject?->name }} ({{ $assignment->subject?->code }})</strong> &middot;
                পূর্ণমান: <strong>{{ $assignment->total_marks }}</strong> নম্বর &middot;
                জমার শেষ সময়: <strong>{{ \Carbon\Carbon::parse($assignment->due_datetime)->format('d M Y, h:i A') }}</strong>
            </p>
        </div>
        <div class="page-header-actions">
            @if($assignment->file_path)
                <a href="{{ asset('storage/' . $assignment->file_path) }}" target="_blank" class="btn btn-outline" style="display:inline-flex;align-items:center;gap:6px">
                    <i class="fa-solid fa-download"></i> প্রশ্নপত্র ডাউনলোড করুন
                </a>
            @endif
        </div>
    </div>

    @if(session('success'))
        <div style="background:#ecfdf5;border:1px solid #a7f3d0;color:#047857;padding:12px 16px;border-radius:8px;margin-bottom:16px;font-size:13px;font-weight:600">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div style="background:#fef2f2;border:1px solid #fecaca;color:#b91c1c;padding:12px 16px;border-radius:8px;margin-bottom:16px;font-size:13px">
            {{ session('error') }}
        </div>
    @endif

    <div style="display:grid;grid-template-columns:1fr 380px;gap:20px">

        {{-- Left: Assignment Instructions & Details --}}
        <div>
            <div class="card" style="margin-bottom:20px">
                <div class="card-header" style="background:#f8fafc">
                    <span class="card-title"><i class="fa-solid fa-circle-info" style="color:#6366f1"></i> অ্যাসাইনমেন্টের নির্দেশনা ও বিবরণ</span>
                </div>
                <div style="padding:18px">
                    @if($assignment->instructions)
                        <div style="font-size:14px;color:#334155;line-height:1.7;white-space:pre-line">
                            {{ $assignment->instructions }}
                        </div>
                    @else
                        <p style="color:#94a3b8;font-size:13px;margin:0">কোনো বিশেষ নির্দেশনা নেই। প্রশ্নপত্র অনুযায়ী সমাধান করে জমা দিন।</p>
                    @endif

                    @if($assignment->file_path)
                        <div style="margin-top:20px;padding:12px;background:#f0fdf4;border:1px solid #bbf7d0;border-radius:8px;display:flex;align-items:center;justify-content:space-between">
                            <div style="display:flex;align-items:center;gap:10px">
                                <i class="fa-solid fa-file-pdf" style="font-size:24px;color:#ef4444"></i>
                                <div>
                                    <strong style="font-size:13px;color:#166534">অ্যাসাইনমেন্টের প্রশ্নপত্র বা রেফারেন্স ফাইল</strong>
                                    <div style="font-size:11px;color:#64748b">ফাইলটি ডাউনলোড করে প্রশ্নগুলো সমাধান করুন</div>
                                </div>
                            </div>
                            <a href="{{ asset('storage/' . $assignment->file_path) }}" target="_blank" class="btn btn-primary btn-sm" style="background:#16a34a;border-color:#16a34a">
                                <i class="fa-solid fa-download"></i> ডাউনলোড
                            </a>
                        </div>
                    @endif
                </div>
            </div>

            {{-- If Graded: Show Feedback & Score card --}}
            @if($submission && $submission->status === 'GRADED')
                <div class="card" style="border-left:4px solid #10b981">
                    <div class="card-header" style="background:#ecfdf5">
                        <span class="card-title" style="color:#065f46"><i class="fa-solid fa-award"></i> শিক্ষকের মূল্যায়ন ও ফলাফল</span>
                    </div>
                    <div style="padding:18px">
                        <div style="display:flex;align-items:center;gap:20px;margin-bottom:14px">
                            <div>
                                <span style="font-size:12px;color:#64748b">আপনার প্রাপ্ত নম্বর:</span>
                                <div style="font-size:32px;font-weight:800;color:#059669">
                                    {{ $submission->obtained_marks }} <span style="font-size:16px;font-weight:normal;color:#64748b">/ {{ $assignment->total_marks }}</span>
                                </div>
                            </div>
                        </div>
                        @if($submission->teacher_feedback)
                            <div style="background:#fff;border:1px solid #e2e8f0;border-radius:8px;padding:12px">
                                <strong style="font-size:12px;color:#334155">শিক্ষকের মন্তব্য / ফিডব্যাক:</strong>
                                <p style="margin:4px 0 0;font-size:13px;color:#475569">{{ $submission->teacher_feedback }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            @endif
        </div>

        {{-- Right: Submission Box --}}
        <div>
            <div class="card">
                <div class="card-header" style="background:#f8fafc">
                    <span class="card-title"><i class="fa-solid fa-upload" style="color:#059669"></i> অ্যাসাইনমেন্ট জমা বক্স</span>
                </div>
                <div style="padding:18px">
                    @if($submission)
                        {{-- Already Submitted Information --}}
                        <div style="margin-bottom:16px">
                            <span class="badge badge-active no-dot" style="margin-bottom:8px;display:inline-block">
                                <i class="fa-solid fa-check"></i> জমা সম্পন্ন হয়েছে
                            </span>
                            <div style="font-size:12px;color:#64748b;margin-bottom:12px">
                                জমার তারিখ: <strong>{{ \Carbon\Carbon::parse($submission->submitted_at)->format('d M Y, h:i A') }}</strong>
                            </div>

                            @if($submission->submission_file)
                                <div style="padding:10px;background:#f1f5f9;border-radius:8px;margin-bottom:12px;display:flex;align-items:center;justify-content:space-between">
                                    <span style="font-size:12px;color:#334155;font-weight:600"><i class="fa-solid fa-file"></i> আপনার জমাকৃত ফাইল</span>
                                    <a href="{{ asset('storage/' . $submission->submission_file) }}" target="_blank" class="btn btn-outline btn-sm" style="padding:2px 8px;font-size:11px">
                                        দেখুন
                                    </a>
                                </div>
                            @endif

                            @if($submission->student_note)
                                <div style="font-size:12px;color:#475569;margin-bottom:14px">
                                    <strong>আপনার নোট:</strong> {{ $submission->student_note }}
                                </div>
                            @endif
                        </div>

                        {{-- Allow re-upload / update submission before grading --}}
                        @if($submission->status !== 'GRADED')
                            <hr style="margin:16px 0;border:0;border-top:1px solid #e2e8f0">
                            <strong style="font-size:13px;color:#334155;display:block;margin-bottom:8px">খাতা পুনরায় জমা দিন (Re-submit):</strong>
                        @endif
                    @endif

                    @if(!$submission || $submission->status !== 'GRADED')
                        <form method="POST" action="{{ route('student.assignments.submit', $assignment) }}" enctype="multipart/form-data">
                            @csrf
                            <div class="form-group">
                                <label>সমাধান ফাইল আপলোড করুন <span class="required">*</span></label>
                                <input type="file" name="submission_file" class="form-control" required accept=".pdf,.doc,.docx,.zip,.jpg,.jpeg,.png">
                                <small style="font-size:11px;color:#64748b">PDF, Word Document, Zip বা ইমেজ (সর্বোচ্চ ৫০MB)</small>
                            </div>

                            <div class="form-group">
                                <label>কোনো নোট বা বক্তব্য (ঐচ্ছিক)</label>
                                <textarea name="student_note" class="form-control" rows="2" placeholder="শিক্ষকের উদ্দেশ্যে কোনো বার্তা থাকলে লিখুন..."></textarea>
                            </div>

                            <button type="submit" class="btn btn-primary" style="width:100%;margin-top:10px;display:flex;align-items:center;justify-content:center;gap:6px">
                                <i class="fa-solid fa-paper-plane"></i> {{ $submission ? 'পুনরায় জমা দিন' : 'অ্যাসাইনমেন্ট জমা দিন' }}
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>

    </div>
</x-student-layout>
