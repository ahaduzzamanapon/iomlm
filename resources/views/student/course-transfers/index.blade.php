<x-student-layout>
    <x-slot name="title">কোর্স পরিবর্তন ও স্থানান্তর (Course Transfer)</x-slot>

    <style>
        .st-header { margin-bottom: 22px; font-family: 'Kalpurush', sans-serif; }
        .st-title { font-size: 22px; font-weight: 700; color: #1e293b; display: flex; align-items: center; gap: 10px; }
        .st-title i { color: #047857; }
        .st-subtitle { font-size: 13px; color: #64748b; margin-top: 4px; }

        .card-st { background: #fff; border: 1px solid #e2e8f0; border-radius: 14px; padding: 20px 24px; margin-bottom: 22px; font-family: 'Kalpurush', sans-serif; box-shadow: 0 2px 8px rgba(0,0,0,0.03); }

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
    <div class="st-header">
        <div class="st-title">
            <i class="fa-solid fa-arrow-right-arrow-left"></i>
            কোর্স পরিবর্তন ও স্থানান্তর (Course Transfer)
        </div>
        <div class="st-subtitle">
            বর্তমান সক্রিয় কোর্স থেকে নতুন কোনো কোর্সে স্থানান্তর আবেদন, ফি পরিশোধ ও অবস্থা ট্র্যাকিং
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
    <div class="card-st" style="border-left: 5px solid {{ $activeRequest->status === 'APPROVED_PENDING_PAYMENT' ? '#0284c7' : '#f59e0b' }}">
        <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px;margin-bottom:14px">
            <div>
                <span style="font-size:11px;font-weight:700;text-transform:uppercase;color:#64748b;letter-spacing:1px">চলমান আবেদন (Current Request)</span>
                <div style="font-size:17px;font-weight:800;color:#0f172a;margin-top:2px">
                    {{ $activeRequest->fromCourse?->name }} &rarr; <span style="color:#047857">{{ $activeRequest->toCourse?->name }}</span>
                </div>
            </div>
            <div>
                @if($activeRequest->status === 'PENDING')
                    <span class="badge" style="background:#fef3c7;color:#92400e;padding:6px 14px;border-radius:20px;font-size:12px;font-weight:700">
                        <i class="fa-solid fa-clock"></i> অ্যাডমিন পর্যালোচনার অপেক্ষায়
                    </span>
                @elseif($activeRequest->status === 'APPROVED_PENDING_PAYMENT')
                    <span class="badge" style="background:#e0f2fe;color:#0369a1;padding:6px 14px;border-radius:20px;font-size:12px;font-weight:700">
                        <i class="fa-solid fa-credit-card"></i> ফি পরিশোধের অপেক্ষায়
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
            <div class="step-item {{ $activeRequest->status === 'APPROVED_PENDING_PAYMENT' ? 'completed' : 'active' }}">
                <div class="step-circle">
                    @if($activeRequest->status === 'APPROVED_PENDING_PAYMENT')
                        <i class="fa-solid fa-check"></i>
                    @else
                        ২
                    @endif
                </div>
                <div class="step-text">২. অ্যাডমিন অনুমোদন</div>
            </div>
            <div class="step-item {{ $activeRequest->status === 'APPROVED_PENDING_PAYMENT' ? 'active' : '' }}">
                <div class="step-circle">৩</div>
                <div class="step-text">৩. ফি পরিশোধ</div>
            </div>
            <div class="step-item">
                <div class="step-circle">৪</div>
                <div class="step-text">৪. নতুন কোর্স কার্যকর</div>
            </div>
        </div>

        @if($activeRequest->status === 'APPROVED_PENDING_PAYMENT')
            <div style="background:#f0f9ff;border:1px solid #bae6fd;border-radius:10px;padding:16px 20px;margin-top:16px;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:14px">
                <div>
                    <div style="font-size:15px;font-weight:700;color:#0369a1">
                        🎉 আপনার আবেদনটি অনুমোদিত হয়েছে!
                    </div>
                    <div style="font-size:13px;color:#334155;margin-top:4px">
                        নতুন বরাদ্দকৃত ব্যাচ: <strong>{{ $activeRequest->toBatch?->name ?? 'শীঘ্রই নির্ধারিত হবে' }}</strong> | 
                        প্রযোজ্য স্থানান্তর ফি: <strong style="color:#0f172a;font-size:15px">৳{{ number_format($activeRequest->transfer_fee, 2) }}</strong>
                    </div>
                    <div style="font-size:12px;color:#64748b;margin-top:2px">
                        ফি পরিশোধ করা মাত্রই আপনার বর্তমান কোর্সের সংযোগ বন্ধ হয়ে নতুন কোর্সটি স্বয়ংক্রিয়ভাবে চালু হবে।
                    </div>
                </div>
                <div>
                    <a href="{{ route('student.fees.index') }}" class="btn btn-primary" style="padding:10px 20px;font-size:13px;font-weight:700;display:inline-flex;align-items:center;gap:8px">
                        <i class="fa-solid fa-credit-card"></i> ফি পরিশোধ করুন (Pay Fee)
                    </a>
                </div>
            </div>
        @else
            <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px;margin-top:14px">
                <div style="font-size:12px;color:#64748b">
                    আবেদনের তারিখ: {{ $activeRequest->created_at->format('d M Y, h:i A') }} | কারণ: {{ $activeRequest->reason }}
                </div>
                <form method="POST" action="{{ route('student.course-transfers.cancel', $activeRequest) }}" onsubmit="return confirm('আপনি কি নিশ্চিত যে কোর্স পরিবর্তনের এই আবেদনটি প্রত্যাহার করতে চান?');">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-outline btn-sm" style="color:#dc2626;border-color:#fecaca">
                        <i class="fa-solid fa-xmark"></i> আবেদন বাতিল করুন
                    </button>
                </form>
            </div>
        @endif
    </div>
    @endif

    <div class="grid-2">
        {{-- Current Enrolled Course --}}
        <div class="card-st">
            <h3 style="font-size:16px;font-weight:700;color:#0f172a;margin:0 0 16px;display:flex;align-items:center;gap:8px">
                <i class="fa-solid fa-graduation-cap" style="color:#047857"></i> বর্তমান সক্রিয় কোর্স (Current Active Course)
            </h3>

            @if($activeEnrollment && $activeEnrollment->course)
                <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;padding:16px 18px">
                    <div style="font-size:17px;font-weight:800;color:#047857">
                        {{ $activeEnrollment->course->name }}
                    </div>
                    <div style="display:flex;gap:10px;margin-top:8px;flex-wrap:wrap">
                        <span class="badge" style="background:#e0f2fe;color:#0369a1;padding:3px 10px;border-radius:12px;font-size:12px">
                            {{ $activeEnrollment->course->type === 'SEMESTER_BASED' ? 'সেমিস্টার ভিত্তিক' : 'বিষয় ভিত্তিক' }}
                        </span>
                        @if($activeEnrollment->batch)
                            <span class="badge" style="background:#f1f5f9;color:#334155;padding:3px 10px;border-radius:12px;font-size:12px">
                                ব্যাচ: {{ $activeEnrollment->batch->name }}
                            </span>
                        @endif
                        @if($activeEnrollment->semester)
                            <span class="badge" style="background:#fef3c7;color:#92400e;padding:3px 10px;border-radius:12px;font-size:12px">
                                {{ $activeEnrollment->semester->name }}
                            </span>
                        @endif
                    </div>
                    <div style="font-size:12px;color:#64748b;margin-top:12px;line-height:1.5">
                        ভর্তির তারিখ: {{ $activeEnrollment->enrolled_at ? date('d M Y', strtotime($activeEnrollment->enrolled_at)) : '—' }} | 
                        মেয়াদ: {{ $activeEnrollment->course->duration_value }} {{ ucfirst(strtolower($activeEnrollment->course->duration_unit)) }}
                    </div>
                </div>
            @else
                <div style="text-align:center;padding:30px;color:#94a3b8">
                    আপনার কোনো সক্রিয় কোর্স পাওয়া যায়নি।
                </div>
            @endif

            {{-- Rules / Guidelines --}}
            <div style="margin-top:20px;padding:14px 16px;background:#fefce8;border:1px solid #fef08a;border-radius:8px">
                <div style="font-size:13px;font-weight:700;color:#854d0e;margin-bottom:6px">
                    <i class="fa-solid fa-circle-info"></i> কোর্স পরিবর্তনের নিয়মাবলী:
                </div>
                <ul style="margin:0;padding-left:18px;font-size:12px;color:#713f12;line-height:1.6">
                    <li>কোর্স পরিবর্তনের আবেদন করার পর অ্যাডমিন কর্তৃক পর্যালোচনা করে অনুমোদন দেওয়া হবে।</li>
                    <li>অ্যাডমিন নতুন কোর্সের জন্য একটি স্থানান্তর ফি (যদি প্রযোজ্য হয়) ও নতুন ব্যাচ নির্ধারণ করবেন।</li>
                    <li><strong>ফি পরিশোধের পর:</strong> আপনার পূর্বের কোর্সের ক্লাসের এক্সেস বন্ধ হয়ে নতুন কোর্সের সকল ক্লাস, রুটিন ও বিষয়াবলি আপনার প্যানেলে চালু হবে।</li>
                </ul>
            </div>
        </div>

        {{-- Application Form --}}
        <div class="card-st">
            <h3 style="font-size:16px;font-weight:700;color:#0f172a;margin:0 0 16px;display:flex;align-items:center;gap:8px">
                <i class="fa-solid fa-paper-plane" style="color:#0284c7"></i> নতুন কোর্সে স্থানান্তরের আবেদন
            </h3>

            @if($activeRequest)
                <div style="padding:30px 20px;text-align:center;background:#f8fafc;border-radius:10px;border:1px dashed #cbd5e1">
                    <i class="fa-solid fa-hourglass-half" style="font-size:32px;color:#d97706;margin-bottom:10px"></i>
                    <div style="font-size:14px;font-weight:700;color:#334155">আপনার একটি আবেদন প্রক্রিয়াধীন রয়েছে</div>
                    <div style="font-size:12px;color:#64748b;margin-top:4px">
                        বর্তমান আবেদনটির নিষ্পত্তি না হওয়া পর্যন্ত নতুন আবেদন জমা দেওয়া যাবে না।
                    </div>
                </div>
            @elseif(!$activeEnrollment)
                <div style="padding:30px 20px;text-align:center;background:#f8fafc;border-radius:10px;color:#94a3b8">
                    কোর্স পরিবর্তন আবেদনের জন্য একটি সক্রিয় কোর্স থাকা বাধ্যতামূলক।
                </div>
            @else
                <form method="POST" action="{{ route('student.course-transfers.store') }}">
                    @csrf
                    <div class="form-group" style="margin-bottom:16px">
                        <label style="display:block;font-size:13px;font-weight:700;color:#334155;margin-bottom:6px">
                            যে কোর্সে যেতে চান (Desired Target Course) <span style="color:#dc2626">*</span>
                        </label>
                        <select name="to_course_id" class="form-control" required style="width:100%;height:42px;border-radius:8px;border:1px solid #cbd5e1;padding:0 12px;font-size:13px">
                            <option value="">-- কোর্স নির্বাচন করুন --</option>
                            @foreach($availableCourses as $c)
                                <option value="{{ $c->id }}" {{ old('to_course_id') == $c->id ? 'selected' : '' }}>
                                    {{ $c->name }} ({{ $c->duration_value }} {{ ucfirst(strtolower($c->duration_unit)) }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group" style="margin-bottom:16px">
                        <label style="display:block;font-size:13px;font-weight:700;color:#334155;margin-bottom:6px">
                            কোর্স পরিবর্তনের কারণ (Reason for Transfer) <span style="color:#dc2626">*</span>
                        </label>
                        <textarea name="reason" class="form-control" rows="4" required placeholder="আপনি কেন এই কোর্সটি পরিবর্তন করে নতুন কোর্সে যেতে চান তা বিস্তারিত লিখুন..." style="width:100%;border-radius:8px;border:1px solid #cbd5e1;padding:10px 12px;font-size:13px">{{ old('reason') }}</textarea>
                        <small style="color:#64748b;font-size:11px">ন্যূনতম ১০ অক্ষরে আপনার কারণ ব্যক্ত করুন।</small>
                    </div>

                    <button type="submit" class="btn btn-primary" style="width:100%;padding:12px;font-size:14px;font-weight:700">
                        <i class="fa-solid fa-paper-plane"></i> আবেদন জমা দিন (Submit Application)
                    </button>
                </form>
            @endif
        </div>
    </div>

    {{-- ── Transfer History ── --}}
    <div class="card-st">
        <h3 style="font-size:16px;font-weight:700;color:#0f172a;margin:0 0 16px;display:flex;align-items:center;gap:8px">
            <i class="fa-solid fa-clock-rotate-left" style="color:#64748b"></i> কোর্স পরিবর্তনের ইতিহাস (Transfer History)
        </h3>

        <div style="overflow-x:auto">
            <table style="width:100%;border-collapse:collapse;font-size:13px">
                <thead>
                    <tr style="background:#f8fafc;border-bottom:1px solid #e2e8f0;text-align:left;color:#64748b;font-size:12px">
                        <th style="padding:10px 14px">তারিখ</th>
                        <th style="padding:10px 14px">পূর্ববর্তী কোর্স</th>
                        <th style="padding:10px 14px">নতুন কোর্স ও ব্যাচ</th>
                        <th style="padding:10px 14px">ট্রান্সফার ফি</th>
                        <th style="padding:10px 14px">স্ট্যাটাস</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transfers as $tr)
                    <tr style="border-bottom:1px solid #f1f5f9">
                        <td style="padding:12px 14px;color:#64748b">
                            {{ $tr->created_at->format('d M Y') }}
                        </td>
                        <td style="padding:12px 14px">
                            <strong style="color:#334155">{{ $tr->fromCourse?->name ?? '—' }}</strong>
                            @if($tr->fromBatch)
                                <div style="font-size:11px;color:#64748b">ব্যাচ: {{ $tr->fromBatch->name }}</div>
                            @endif
                        </td>
                        <td style="padding:12px 14px">
                            <strong style="color:#047857">{{ $tr->toCourse?->name ?? '—' }}</strong>
                            @if($tr->toBatch)
                                <div style="font-size:11px;color:#0369a1">ব্যাচ: {{ $tr->toBatch->name }}</div>
                            @endif
                        </td>
                        <td style="padding:12px 14px">
                            <strong>৳{{ number_format($tr->transfer_fee, 2) }}</strong>
                            @if($tr->invoice)
                                <div style="font-size:11px;color:{{ $tr->invoice->status === 'PAID' ? '#16a34a' : '#d97706' }}">
                                    {{ $tr->invoice->status === 'PAID' ? 'পরিশোধিত' : 'বকেয়া' }}
                                </div>
                            @endif
                        </td>
                        <td style="padding:12px 14px">
                            @if($tr->status === 'PENDING')
                                <span style="background:#fef3c7;color:#92400e;padding:3px 8px;border-radius:10px;font-size:11px;font-weight:700">অপেক্ষমাণ</span>
                            @elseif($tr->status === 'APPROVED_PENDING_PAYMENT')
                                <span style="background:#e0f2fe;color:#0369a1;padding:3px 8px;border-radius:10px;font-size:11px;font-weight:700">ফি বকেয়া</span>
                            @elseif($tr->status === 'COMPLETED')
                                <span style="background:#dcfce7;color:#166534;padding:3px 8px;border-radius:10px;font-size:11px;font-weight:700"><i class="fa-solid fa-check"></i> সম্পন্ন</span>
                            @elseif($tr->status === 'REJECTED')
                                <span style="background:#fee2e2;color:#991b1b;padding:3px 8px;border-radius:10px;font-size:11px;font-weight:700">বাতিলকৃত</span>
                            @else
                                <span style="background:#f1f5f9;color:#64748b;padding:3px 8px;border-radius:10px;font-size:11px">{{ $tr->status }}</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" style="text-align:center;padding:30px;color:#94a3b8">
                            পূর্ববর্তী কোনো কোর্স পরিবর্তনের তথ্য পাওয়া যায়নি।
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-student-layout>
