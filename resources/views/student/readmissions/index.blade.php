<x-student-layout>
    <x-slot name="title">রি-এডমিশন আবেদন (Readmission Application)</x-slot>

    <style>
        .rd-header { margin-bottom: 22px; font-family: 'Kalpurush', sans-serif; }
        .rd-title { font-size: 22px; font-weight: 700; color: #1e293b; display: flex; align-items: center; gap: 10px; }
        .rd-title i { color: #047857; }
        .rd-subtitle { font-size: 13px; color: #64748b; margin-top: 4px; }

        .card-rd { background: #fff; border: 1px solid #e2e8f0; border-radius: 14px; padding: 20px 24px; margin-bottom: 22px; font-family: 'Kalpurush', sans-serif; box-shadow: 0 2px 8px rgba(0,0,0,0.03); }

        .stepper-box { display: flex; justify-content: space-between; align-items: center; margin: 20px 0; position: relative; }
        .stepper-box::before { content: ''; position: absolute; top: 18px; left: 30px; right: 30px; height: 3px; background: #e2e8f0; z-index: 1; }
        .step-item { position: relative; z-index: 2; text-align: center; flex: 1; }
        .step-circle { width: 36px; height: 36px; border-radius: 50%; background: #f1f5f9; color: #94a3b8; display: inline-flex; align-items: center; justify-content: center; font-weight: 700; font-size: 14px; border: 2px solid #cbd5e1; margin-bottom: 6px; }
        .step-item.active .step-circle { background: #047857; color: #fff; border-color: #047857; }
        .step-item.completed .step-circle { background: #10b981; color: #fff; border-color: #10b981; }
        .step-text { font-size: 12px; font-weight: 600; color: #64748b; }
        .step-item.active .step-text { color: #047857; font-weight: 700; }
        .step-item.completed .step-text { color: #10b981; }

        .grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        @media(max-width: 768px) { .grid-2 { grid-template-columns: 1fr; } }
    </style>

    {{-- Header --}}
    <div class="rd-header">
        <div class="rd-title">
            <i class="fa-solid fa-user-graduate"></i>
            রি-এডমিশন আবেদন (Readmission Application)
        </div>
        <div class="rd-subtitle">
            বর্তমান ব্যাচ থেকে পরবর্তী সেশনের নতুন ব্যাচে রি-এডমিশনের আবেদন, পর্যালোচনা ও অবস্থা ট্র্যাকিং
        </div>
    </div>

    {{-- Alerts --}}
    @if(session('success'))
        <div class="alert alert-success" style="margin-bottom:18px;font-family:'Kalpurush',sans-serif;background:#ecfdf5;border:1px solid #a7f3d0;color:#065f46">
            <i class="fa-solid fa-circle-check"></i> {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger" style="margin-bottom:18px;font-family:'Kalpurush',sans-serif;background:#fef2f2;border:1px solid #fecaca;color:#991b1b">
            <i class="fa-solid fa-circle-exclamation"></i> {{ session('error') }}
        </div>
    @endif

    {{-- ── Active Request Tracker (if any) ── --}}
    @if($activeRequest)
    <div class="card-rd" style="border-left: 5px solid {{ $activeRequest->status === 'APPROVED' ? '#047857' : '#f59e0b' }}">
        <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px;margin-bottom:14px">
            <div>
                <span style="font-size:11px;font-weight:700;text-transform:uppercase;color:#64748b;letter-spacing:1px">চলমান আবেদন (Current Request)</span>
                <div style="font-size:17px;font-weight:800;color:#0f172a;margin-top:2px">
                    {{ $activeRequest->course?->name }} &bull; বর্তমান ব্যাচ: <span style="color:#0284c7">{{ $activeRequest->fromBatch?->name ?? 'N/A' }}</span>
                    @if($activeRequest->toBatch)
                        &rarr; কাঙ্ক্ষিত ব্যাচ: <span style="color:#047857">{{ $activeRequest->toBatch->name }}</span>
                    @endif
                </div>
            </div>
            <div>
                @if($activeRequest->status === 'PENDING')
                    <span class="badge" style="background:#fef3c7;color:#92400e;padding:6px 14px;border-radius:20px;font-size:12px;font-weight:700">
                        <i class="fa-solid fa-clock"></i> কর্তৃপক্ষের পর্যালোচনার অপেক্ষায়
                    </span>
                @elseif($activeRequest->status === 'APPROVED')
                    <span class="badge" style="background:#dcfce7;color:#15803d;padding:6px 14px;border-radius:20px;font-size:12px;font-weight:700">
                        <i class="fa-solid fa-circle-check"></i> রি-এডমিশন অনুমোদিত
                    </span>
                @endif
            </div>
        </div>

        {{-- Progress Stepper --}}
        <div class="stepper-box">
            <div class="step-item completed">
                <div class="step-circle"><i class="fa-solid fa-check"></i></div>
                <div class="step-text">১. আবেদন জমা</div>
            </div>
            <div class="step-item {{ $activeRequest->status === 'APPROVED' ? 'completed' : 'active' }}">
                <div class="step-circle">
                    @if($activeRequest->status === 'APPROVED')
                        <i class="fa-solid fa-check"></i>
                    @else
                        ২
                    @endif
                </div>
                <div class="step-text">২. পর্যালোচনা ও অনুমোদন</div>
            </div>
            <div class="step-item {{ $activeRequest->status === 'APPROVED' ? 'completed' : '' }}">
                <div class="step-circle">৩</div>
                <div class="step-text">৩. ফি নির্ধারণ ও নতুন ব্যাচ</div>
            </div>
            <div class="step-item {{ $activeRequest->status === 'APPROVED' ? 'completed' : '' }}">
                <div class="step-circle">৪</div>
                <div class="step-text">৪. রি-এডমিশন কার্যকর</div>
            </div>
        </div>

        @if($activeRequest->notes)
        <div style="background:#f8fafc;padding:12px 16px;border-radius:8px;border:1px solid #e2e8f0;font-size:13px;color:#475569;margin-top:14px">
            <strong>আপনার আবেদনের বিবরণ:</strong> {{ $activeRequest->notes }}
        </div>
        @endif

        @if($activeRequest->status === 'PENDING')
        <div style="margin-top:16px;display:flex;justify-content:flex-end">
            <form method="POST" action="{{ route('student.readmissions.cancel', $activeRequest) }}" onsubmit="return confirm('আপনি কি নিশ্চিত যে রি-এডমিশন আবেদনটি প্রত্যাহার করতে চান?')">
                @csrf @method('DELETE')
                <button type="submit" class="btn btn-outline btn-sm text-red" style="font-size:12px">
                    <i class="fa-solid fa-xmark"></i> আবেদন বাতিল করুন
                </button>
            </form>
        </div>
        @endif
    </div>
    @endif

    {{-- ── Main Two-Column Layout (Form & Info) ── --}}
    <div class="grid-2">
        {{-- Application Form --}}
        <div class="card-rd">
            <h3 style="font-size:16px;font-weight:700;color:#1e293b;margin-bottom:16px;display:flex;align-items:center;gap:8px">
                <i class="fa-solid fa-file-pen" style="color:#047857"></i>
                নতুন রি-এডমিশন আবেদন জমা দিন
            </h3>

            @if(!$activeEnrollment)
                <div style="padding:20px;text-align:center;color:#64748b">
                    <i class="fa-solid fa-triangle-exclamation" style="font-size:30px;color:#f59e0b;margin-bottom:10px;display:block"></i>
                    আপনার বর্তমানে কোনো সক্রিয় কোর্স নেই।
                </div>
            @elseif($activeRequest && $activeRequest->status === 'PENDING')
                <div style="padding:20px;text-align:center;color:#64748b;background:#f8fafc;border-radius:8px;border:1px dashed #cbd5e1">
                    <i class="fa-solid fa-circle-check" style="font-size:30px;color:#047857;margin-bottom:10px;display:block"></i>
                    আপনার একটি রি-এডমিশন আবেদন ইতোমধ্যে পর্যালোচনায় রয়েছে। অনুগ্রহ করে কর্তৃপক্ষের সিদ্ধান্তের জন্য অপেক্ষা করুন।
                </div>
            @else
                <form method="POST" action="{{ route('student.readmissions.store') }}">
                    @csrf

                    <div class="form-group" style="margin-bottom:14px">
                        <label style="font-size:13px;font-weight:700;color:#334155;display:block;margin-bottom:6px">বর্তমান কোর্স (Current Course)</label>
                        <input type="text" class="form-control" value="{{ $activeEnrollment->course?->name }}" readonly style="background:#f8fafc;cursor:not-allowed;font-weight:600">
                    </div>

                    <div class="form-group" style="margin-bottom:14px">
                        <label style="font-size:13px;font-weight:700;color:#334155;display:block;margin-bottom:6px">বর্তমান ব্যাচ (Current Batch)</label>
                        <input type="text" class="form-control" value="{{ $activeEnrollment->batch?->name ?? 'N/A' }}" readonly style="background:#f8fafc;cursor:not-allowed">
                    </div>

                    <div class="form-group" style="margin-bottom:14px">
                        <label style="font-size:13px;font-weight:700;color:#334155;display:block;margin-bottom:6px">কাঙ্ক্ষিত নতুন ব্যাচ (Target Batch - যদি নির্দিষ্ট থাকে)</label>
                        <select name="to_batch_id" class="form-control">
                            <option value="">-- পরবর্তী উন্মুক্ত সেশন / কর্তৃপক্ষ নির্ধারণ করবেন --</option>
                            @foreach($availableBatches as $ab)
                                <option value="{{ $ab->id }}">{{ $ab->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group" style="margin-bottom:18px">
                        <label style="font-size:13px;font-weight:700;color:#334155;display:block;margin-bottom:6px">
                            রি-এডমিশনের কারণ ও বিস্তারিত বিবরণ <span style="color:#ef4444">*</span>
                        </label>
                        <textarea name="reason" class="form-control" rows="4" placeholder="কেন রি-এডমিশন নিতে চান? (যেমন: অসুস্থতার কারণে পরীক্ষা দিতে পারিনি / স্থগিত সেশন পুনরায় শুরু করতে চাই)" required style="resize:vertical">{{ old('reason') }}</textarea>
                    </div>

                    <button type="submit" class="btn btn-primary" style="width:100%;padding:12px;font-size:14px;font-weight:700;background:#047857;border:none">
                        <i class="fa-solid fa-paper-plane"></i> আবেদন জমা দিন (Submit Application)
                    </button>
                </form>
            @endif
        </div>

        {{-- Instructions & Policy Card --}}
        <div class="card-rd">
            <h3 style="font-size:16px;font-weight:700;color:#1e293b;margin-bottom:16px;display:flex;align-items:center;gap:8px">
                <i class="fa-solid fa-circle-info" style="color:#0284c7"></i>
                রি-এডমিশন সংক্রান্ত নিয়মাবলী
            </h3>

            <div style="font-size:13.5px;line-height:1.8;color:#334155">
                <p style="margin-bottom:12px">
                    <strong>১. কারা আবেদন করতে পারবেন:</strong> কোনো অনিবার্য কারণে ক্লাস বা পরীক্ষায় অংশ নিতে না পারলে, অথবা সেমিস্টার ড্রপ হলে পরবর্তী ব্যাচে রি-এডমিশনের জন্য আবেদন করা যাবে।
                </p>
                <p style="margin-bottom:12px">
                    <strong>২. আইডি ও রোল পরিবর্তন:</strong> নতুন ব্যাচে রি-এডমিশন কার্যকর হলে শিক্ষার্থীর রোল নম্বরের ব্যাচ কোড স্বয়ংক্রিয়ভাবে হালনাগাদ হবে।
                </p>
                <p style="margin-bottom:12px">
                    <strong>৩. ফলাফল ও হিস্ট্রি:</strong> পূর্ববর্তী সেশনের সম্পন্নকৃত অ্যাকাডেমিক হিস্ট্রি অপরিবর্তিত থাকবে এবং নতুন ব্যাচের রুটিন ও পরীক্ষায় অংশ নেওয়া যাবে।
                </p>
                <p style="margin-bottom:0">
                    <strong>৪. ফি নির্ধারণ:</strong> আবেদন অনুমোদনের পর কর্তৃপক্ষ কর্তৃক প্রযোজ্য রি-এডমিশন ফি নির্ধারণ করা হলে পোর্টালে নোটিফিকেশন প্রদান করা হবে।
                </p>
            </div>
        </div>
    </div>

    {{-- ── Past Readmission Applications History ── --}}
    @if($readmissions->isNotEmpty())
    <div class="card-rd" style="margin-top:10px">
        <h3 style="font-size:16px;font-weight:700;color:#1e293b;margin-bottom:16px;display:flex;align-items:center;gap:8px">
            <i class="fa-solid fa-clock-rotate-left" style="color:#64748b"></i>
            আবেদনের ইতিহাস (Application History)
        </h3>

        <div style="overflow-x:auto">
            <table class="table" style="width:100%;font-size:13px">
                <thead>
                    <tr style="background:#f8fafc">
                        <th>তারিখ</th>
                        <th>কোর্স</th>
                        <th>পূর্বের ব্যাচ</th>
                        <th>নতুন ব্যাচ</th>
                        <th>স্ট্যাটাস</th>
                        <th>অনুমোদনকারী</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($readmissions as $rd)
                    <tr>
                        <td>{{ $rd->created_at->format('d M, Y') }}</td>
                        <td style="font-weight:600">{{ $rd->course?->name }}</td>
                        <td>{{ $rd->fromBatch?->name ?? '—' }}</td>
                        <td style="color:#047857;font-weight:600">{{ $rd->toBatch?->name ?? 'কর্তৃপক্ষ নির্ধারিত' }}</td>
                        <td>
                            @if($rd->status === 'APPROVED')
                                <span class="badge badge-success no-dot">অনুমোদিত</span>
                            @elseif($rd->status === 'PENDING')
                                <span class="badge badge-warning no-dot">বিবেচনাধীন</span>
                            @elseif($rd->status === 'CANCELLED')
                                <span class="badge badge-secondary no-dot">বাতিলকৃত</span>
                            @else
                                <span class="badge badge-secondary no-dot">{{ $rd->status }}</span>
                            @endif
                        </td>
                        <td>{{ $rd->decidedBy?->name ?? '—' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif
</x-student-layout>
