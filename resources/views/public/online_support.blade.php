<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Online Support Application — IOM</title>
    <link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; }
        body {
            font-family: 'Hind Siliguri', 'Segoe UI', sans-serif;
            background: #0d2942 url('https://admission.iom.edu.bd/assets/public/images/bg.jpg') center top repeat;
            background-attachment: fixed;
            background-size: cover;
            margin: 0; padding: 0; color: #1e293b;
        }

        /* Top Header Bar */
        .top-navbar {
            background: #081726; color: #fff; height: 50px;
            display: flex; align-items: center; padding: 0 40px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.3); border-bottom: 1px solid #1e293b;
        }
        .top-navbar-brand { font-size: 16px; font-weight: 700; display: flex; align-items: center; gap: 8px; color: #fff; text-decoration: none; }

        /* Container Grid */
        .support-container {
            max-width: 1200px; margin: 40px auto; padding: 0 20px;
            display: grid; grid-template-columns: 1fr 380px; gap: 24px; align-items: start;
        }
        @media (max-width: 900px) {
            .support-container { grid-template-columns: 1fr; }
        }

        /* Tabs Card */
        .tabs-header {
            background: #ffffff; border-radius: 8px 8px 0 0; padding: 16px 20px;
            display: flex; gap: 12px; border: 1px solid #e2e8f0; border-bottom: none;
        }
        .tab-btn {
            padding: 10px 20px; font-size: 14px; font-weight: 600; text-decoration: none;
            border-radius: 4px; border: 1px solid #0284c7; color: #0284c7; background: #fff;
            transition: all 0.2s;
        }
        .tab-btn.active { background: #0284c7; color: #fff; }
        .tab-btn.btn-inactive { background: #f8fafc; color: #0284c7; border-color: #cbd5e1; }

        /* Form Card */
        .form-card {
            background: #ffffff; border-radius: 0 0 8px 8px; padding: 30px;
            border: 1px solid #e2e8f0; box-shadow: 0 10px 25px rgba(0,0,0,0.15);
        }
        .form-title {
            text-align: center; font-size: 24px; font-weight: 700; color: #0f172a;
            margin-bottom: 24px; position: relative; padding-bottom: 10px;
        }
        .form-title::after {
            content: ''; position: absolute; bottom: 0; left: 50%; transform: translateX(-50%);
            width: 60px; height: 3px; background: #0284c7; border-radius: 2px;
        }

        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px; }
        @media (max-width: 600px) { .form-row { grid-template-columns: 1fr; } }

        .form-group { margin-bottom: 16px; }
        .form-group label { display: block; font-size: 13px; font-weight: 600; color: #334155; margin-bottom: 6px; }
        .form-group label .required { color: #dc2626; }

        .form-control {
            width: 100%; height: 42px; padding: 8px 14px; font-size: 14px;
            border: 1px solid #cbd5e1; border-radius: 6px; background: #fff;
            outline: none; transition: border-color 0.2s; font-family: inherit;
        }
        .form-control:focus { border-color: #0284c7; box-shadow: 0 0 0 3px rgba(2, 132, 199, 0.15); }
        textarea.form-control { height: 100px; resize: vertical; }

        .btn-submit-support {
            background: #0266b3; color: #fff; border: none; border-radius: 6px;
            padding: 12px 30px; font-size: 15px; font-weight: 700; cursor: pointer;
            width: 100%; transition: background 0.2s; box-shadow: 0 4px 12px rgba(2, 102, 179, 0.3);
        }
        .btn-submit-support:hover { background: #014d87; }

        /* Right Guidance Box */
        .guidance-box {
            background: #fffbeb; border: 1px solid #fde68a; border-radius: 8px;
            padding: 24px; color: #92400e; box-shadow: 0 10px 25px rgba(0,0,0,0.15);
        }
        .guidance-header {
            background: #fef3c7; color: #b45309; border: 1px solid #fde68a;
            padding: 12px; border-radius: 6px; font-size: 13px; font-weight: 700;
            line-height: 1.5; margin-bottom: 16px;
        }
        .guidance-item { margin-bottom: 14px; font-size: 13px; line-height: 1.5; }
        .guidance-item strong { display: block; color: #78350f; font-size: 14px; margin-bottom: 2px; }

        .captcha-box { display: flex; align-items: center; gap: 12px; }
        .captcha-code { background: #f1f5f9; padding: 6px 16px; border-radius: 6px; font-weight: 800; font-size: 16px; letter-spacing: 2px; border: 1px solid #cbd5e1; }
    </style>
</head>
<body>

    {{-- Top Navbar --}}
    <div class="top-navbar">
        <a href="/" class="top-navbar-brand">
            ☑️ Online Support Application
        </a>
    </div>

    <div class="support-container">
        {{-- Left Form Column --}}
        <div>
            {{-- Tabs --}}
            @php $isSearchTab = request()->routeIs('online-support.search') || isset($tickets); @endphp
            <div class="tabs-header">
                <a href="{{ route('online-support.index') }}" class="tab-btn {{ !$isSearchTab ? 'active' : 'btn-inactive' }}">Submit Support</a>
                <a href="{{ route('online-support.search') }}" class="tab-btn {{ $isSearchTab ? 'active' : 'btn-inactive' }}">My Support Status</a>
            </div>

            <div class="form-card">
                @if(!$isSearchTab)
                    {{-- Submit Support Form --}}
                    <div class="form-title">সাপোর্ট ফর্ম</div>

                    @if(session('success'))
                        <div style="background:#ecfdf5;border:1px solid #a7f3d0;color:#047857;padding:12px;border-radius:6px;margin-bottom:16px;font-size:13px;font-weight:600">
                            ✓ {{ session('success') }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('online-support.store') }}">
                        @csrf

                        <div class="form-group">
                            <label>Department: <span class="required">*</span></label>
                            <select name="department_id" class="form-control" required>
                                <option value="">--Select Department --</option>
                                @foreach($departments as $dept)
                                    <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label>Phone: <span class="required">*</span></label>
                                <input type="text" name="phone" class="form-control" placeholder="Please Enter Your Phone No"
                                       value="{{ old('phone', $student?->phone ?? $user?->phone ?? '') }}" required>
                            </div>
                            <div class="form-group">
                                <label>Email Address: <span class="required">*</span></label>
                                <input type="email" name="email" class="form-control" placeholder="Please Enter Your Email Address"
                                       value="{{ old('email', $user?->email ?? '') }}" required>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label>Name: <span class="required">*</span></label>
                                <input type="text" name="name" class="form-control" placeholder="Please Enter Your Name"
                                       value="{{ old('name', $student?->name ?? $user?->name ?? '') }}" required>
                            </div>
                            <div class="form-group">
                                <label>Gender: <span class="required">*</span></label>
                                <select name="gender" class="form-control" required>
                                    <option value="">--Select Gender --</option>
                                    <option value="MALE" {{ old('gender', strtoupper($student?->gender ?? 'MALE')) === 'MALE' ? 'selected' : '' }}>Male / ভাই</option>
                                    <option value="FEMALE" {{ old('gender', strtoupper($student?->gender ?? 'MALE')) === 'FEMALE' ? 'selected' : '' }}>Female / বোন</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label>Student ID:</label>
                                <input type="text" name="student_id" class="form-control" placeholder="Please Enter Your Student ID"
                                       value="{{ old('student_id', $student?->student_id ?? '') }}">
                            </div>
                            <div class="form-group">
                                <label>Reference:</label>
                                <input type="text" name="reference" class="form-control" placeholder="Please Enter Reference"
                                       value="{{ old('reference') }}">
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Subject: <span class="required">*</span></label>
                            <input type="text" name="subject" class="form-control" placeholder="Please Enter Subject"
                                   value="{{ old('subject') }}" required>
                        </div>

                        <div class="form-group">
                            <label>Write Your Problem in Details: <span class="required">*</span></label>
                            <textarea name="problem_details" class="form-control" placeholder="Please Enter Problem Description" required>{{ old('problem_details') }}</textarea>
                        </div>

                        {{-- Captcha --}}
                        @php $code = rand(100, 999); @endphp
                        <div class="form-group">
                            <label>Captcha: <span class="required">*</span> <span class="captcha-code">{{ $code }}</span></label>
                            <input type="text" name="captcha" class="form-control" placeholder="Enter the captcha word" style="max-width:220px" required>
                        </div>

                        <button type="submit" class="btn-submit-support">Submit Support Application</button>
                    </form>
                @else
                    {{-- Search Support Status Tab --}}
                    <div class="form-title">My Support</div>

                    <form method="GET" action="{{ route('online-support.search') }}" style="margin-bottom:24px">
                        <div style="font-weight:700;margin-bottom:12px;font-size:14px">
                            Search Previous Support By:
                            <label style="margin-left:12px;font-weight:normal"><input type="radio" name="search_type" value="phone" {{ ($searchType ?? 'phone') === 'phone' ? 'checked' : '' }}> Phone Number</label>
                            <label style="margin-left:12px;font-weight:normal"><input type="radio" name="search_type" value="email" {{ ($searchType ?? '') === 'email' ? 'checked' : '' }}> Email</label>
                            <label style="margin-left:12px;font-weight:normal"><input type="radio" name="search_type" value="ticket_no" {{ ($searchType ?? '') === 'ticket_no' ? 'checked' : '' }}> Ticket Number</label>
                        </div>

                        <div class="form-group">
                            <input type="text" name="query" class="form-control" placeholder="Enter Phone No, Email or Ticket No..." value="{{ $query ?? '' }}" required>
                        </div>

                        <button type="submit" class="btn-submit-support" style="width:auto;padding:10px 30px">Search</button>
                    </form>

                    {{-- Search Results Table --}}
                    @if(isset($tickets))
                        <div style="margin-top:20px">
                            <h4 style="margin-bottom:12px">অনুসন্ধান ফলাফল ({{ $tickets->count() }}টি টিকিট পাওয়া গেছে):</h4>
                            @forelse($tickets as $t)
                                <div style="background:#f8fafc;border:1px solid #e2e8f0;padding:16px;border-radius:8px;margin-bottom:12px;display:flex;justify-content:space-between;align-items:center">
                                    <div>
                                        <div style="font-weight:700;color:#0284c7">#{{ $t->ticket_no }} &middot; {{ $t->subject }}</div>
                                        <div style="font-size:12px;color:#64748b;margin-top:2px">
                                            ডিপার্টমেন্ট: <strong>{{ $t->department->name }}</strong> &middot; তারিখ: {{ $t->created_at->format('d M Y, h:i A') }}
                                        </div>
                                    </div>
                                    <div>
                                        <a href="{{ route('online-support.chat', $t->uuid) }}" class="tab-btn active" style="font-size:12px;padding:6px 14px">
                                            💬 চ্যাট খুলুন →
                                        </a>
                                    </div>
                                </div>
                            @empty
                                <div style="text-align:center;padding:30px;color:#94a3b8">কোনো সাপোর্ট টিকিট পাওয়া যায়নি।</div>
                            @endforelse
                        </div>
                    @endif
                @endif
            </div>
        </div>

        {{-- Right Guidance Box (Matching screenshot text) --}}
        <div class="guidance-box">
            <div class="guidance-header">
                সাপোর্ট লেখার সময় অবশ্যই ডিপার্টমেন্ট ঠিকমতো সিলেক্ট করতে হবে। কেননা ৮ টি ডিপার্টমেন্ট আমাদের ৩ টা অফিস থেকে ১১ জন ভিন্ন ভিন্ন ব্যক্তি দ্বারা পরিচালিত হয়ে থাকে।
            </div>

            @foreach($departments as $dept)
                <div class="guidance-item">
                    <strong>{{ $dept->name }}:</strong>
                    {{ $dept->description }}
                </div>
            @endforeach
        </div>
    </div>

</body>
</html>
