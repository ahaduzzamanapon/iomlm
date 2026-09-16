<x-admin-layout>
    <x-slot name="title">শিক্ষার্থী একাউন্ট লেজার — {{ $student->name }}</x-slot>

    <style>
        .sl-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 22px; flex-wrap: wrap; gap: 14px; font-family: 'Kalpurush', sans-serif; }
        .sl-title { display: flex; align-items: center; gap: 12px; font-size: 22px; font-weight: 700; color: #1e293b; }
        .sl-title i { width: 42px; height: 42px; border-radius: 50%; background: #ecfdf5; color: #047857; display: inline-flex; align-items: center; justify-content: center; font-size: 20px; }
        .sl-kpi-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(210px, 1fr)); gap: 16px; margin-bottom: 24px; font-family: 'Kalpurush', sans-serif; }
        .sl-kpi-card { background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 18px 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); }
        .sl-kpi-label { font-size: 13px; color: #64748b; font-weight: 600; margin-bottom: 6px; }
        .sl-kpi-val { font-size: 26px; font-weight: 800; line-height: 1; }
        .table-sl th { background: #f8fafc; font-size: 12px; font-weight: 700; color: #475569; padding: 12px 14px; border-bottom: 1px solid #e2e8f0; font-family: 'Kalpurush', sans-serif; }
        .table-sl td { padding: 12px 14px; border-bottom: 1px solid #f1f5f9; font-size: 13px; vertical-align: middle; font-family: 'Kalpurush', sans-serif; }
    </style>

    {{-- Page Header --}}
    <div class="sl-header">
        <div>
            <div style="font-size:12px;color:#64748b;margin-bottom:4px">
                <a href="{{ route('admin.students.index') }}" style="color:#047857;text-decoration:none">← শিক্ষার্থী তালিকা</a> /
                <a href="{{ route('admin.students.show', $student) }}" style="color:#047857;text-decoration:none">{{ $student->name }}</a> /
                <span>একাউন্ট লেজার</span>
            </div>
            <div class="sl-title">
                <i class="fa-solid fa-wallet"></i>
                <div>
                    <div>শিক্ষার্থী একাউন্ট ও ফি লেজার (Accounts Ledger)</div>
                    <div style="font-size:13px;color:#64748b;font-weight:400">
                        শিক্ষার্থী: <strong>{{ $student->name }}</strong> |
                        রোল/আইডি: <strong style="color:#047857">{{ $student->student_code ?? 'অনির্ধারিত' }}</strong> |
                        মোবাইল: {{ $student->phone ?? '—' }}
                    </div>
                </div>
            </div>
        </div>
        <div style="display:flex;gap:10px;flex-wrap:wrap">
            <a href="{{ route('admin.students.impersonate', $student) }}" class="btn btn-outline" style="color:#047857;border-color:#10b981;font-weight:700">
                <i class="fa-solid fa-arrow-right-to-bracket"></i> শিক্ষার্থী হিসেবে লগইন
            </a>
            <button type="button" class="btn btn-primary" onclick="openModal('addCustomFeeModal')" style="font-family:'Kalpurush',sans-serif">
                <i class="fa-solid fa-plus-circle"></i> নতুন ফি / ইনভয়েস ধার্য
            </button>
            <a href="{{ route('admin.students.show', $student) }}" class="btn btn-outline" style="font-family:'Kalpurush',sans-serif">
                প্রোফাইল দেখুন →
            </a>
        </div>
    </div>

    {{-- Alerts --}}
    @if(session('success'))
        <div class="alert alert-success" style="margin-bottom:20px;font-family:'Kalpurush',sans-serif;background:#ecfdf5;border:1px solid #a7f3d0;color:#065f46">
            <i class="fa-solid fa-circle-check"></i> {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger" style="margin-bottom:20px;font-family:'Kalpurush',sans-serif;background:#fef2f2;border:1px solid #fecaca;color:#991b1b">
            <i class="fa-solid fa-circle-exclamation"></i> {{ session('error') }}
        </div>
    @endif

    {{-- KPI Cards --}}
    <div class="sl-kpi-grid">
        <div class="sl-kpi-card" style="border-left:4px solid #2563eb">
            <div class="sl-kpi-label">মোট ধার্যকৃত ফি (Total Billed)</div>
            <div class="sl-kpi-val" style="color:#1e40af">৳{{ number_format($totalBilled, 2) }}</div>
        </div>
        <div class="sl-kpi-card" style="border-left:4px solid #10b981">
            <div class="sl-kpi-label">মোট আদায়কৃত / পরিশোধিত (Paid)</div>
            <div class="sl-kpi-val" style="color:#047857">৳{{ number_format($totalPaid, 2) }}</div>
        </div>
        <div class="sl-kpi-card" style="border-left:4px solid #ef4444">
            <div class="sl-kpi-label">সর্বমোট বকেয়া (Total Due)</div>
            <div class="sl-kpi-val" style="color:#b91c1c">৳{{ number_format($totalDue, 2) }}</div>
        </div>
        <div class="sl-kpi-card" style="border-left:4px solid #8b5cf6">
            <div class="sl-kpi-label">ইনভয়েস সংখ্যা</div>
            <div class="sl-kpi-val" style="color:#6d28d9">{{ $invoices->count() }}টি</div>
        </div>
    </div>

    {{-- Invoices Table --}}
    <div class="card" style="margin-bottom:26px;border-radius:12px;overflow:hidden">
        <div class="card-header" style="display:flex;justify-content:space-between;align-items:center;padding:16px 20px;background:#fff;border-bottom:1px solid #e2e8f0">
            <div>
                <span class="card-title" style="font-family:'Kalpurush',sans-serif;font-size:16px;font-weight:700">
                    <i class="fa-solid fa-file-invoice-dollar" style="color:#047857;margin-right:6px"></i> ইনভয়েস ও ফি ধার্য তালিকা (Invoices)
                </span>
                <span style="font-size:12px;color:#64748b;margin-left:8px">শিক্ষার্থীর সকল প্রকার ফি, মওকুফ ও বকেয়া হিসাব</span>
            </div>
            <button type="button" class="btn btn-sm btn-primary" onclick="openModal('addCustomFeeModal')" style="font-family:'Kalpurush',sans-serif">
                <i class="fa-solid fa-plus"></i> নতুন ফি যোগ করুন
            </button>
        </div>
        <div class="table-wrapper" style="overflow-x:auto">
            <table class="table-sl" style="width:100%;border-collapse:collapse">
                <thead>
                    <tr>
                        <th>ইনভয়েস নং</th>
                        <th>বিবরণ / ফি খাত</th>
                        <th>ক্যাটাগরি</th>
                        <th style="text-align:right">মোট ফি</th>
                        <th style="text-align:right">ছাড়</th>
                        <th style="text-align:right">প্রদেয়</th>
                        <th style="text-align:right">পরিশোধিত</th>
                        <th style="text-align:right">বকেয়া</th>
                        <th>শেষ তারিখ</th>
                        <th>স্ট্যাটাস</th>
                        <th style="text-align:center">অ্যাকশন</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($invoices as $inv)
                    <tr>
                        <td style="font-family:monospace;font-weight:700;color:#1e40af">{{ $inv->invoice_no }}</td>
                        <td>
                            <strong>{{ $inv->title }}</strong>
                            @if($inv->enrollment)
                                <div style="font-size:11px;color:#64748b">{{ $inv->enrollment->course->name ?? '' }} ({{ $inv->enrollment->batch->name ?? '' }})</div>
                            @endif
                        </td>
                        <td>
                            <span style="font-size:11px;padding:2px 8px;border-radius:10px;background:#f1f5f9;color:#334155;font-weight:600">
                                {{ $inv->category }}
                            </span>
                        </td>
                        <td style="text-align:right">৳{{ number_format($inv->amount, 2) }}</td>
                        <td style="text-align:right;color:#64748b">৳{{ number_format($inv->discount, 2) }}</td>
                        <td style="text-align:right;font-weight:700">৳{{ number_format($inv->payable_amount, 2) }}</td>
                        <td style="text-align:right;color:#047857;font-weight:700">৳{{ number_format($inv->paid_amount, 2) }}</td>
                        <td style="text-align:right;color:{{ $inv->due_amount > 0 ? '#dc2626' : '#64748b' }};font-weight:700">
                            ৳{{ number_format($inv->due_amount, 2) }}
                        </td>
                        <td style="font-size:12px;color:#475569">
                            {{ $inv->due_date ? \Carbon\Carbon::parse($inv->due_date)->format('d M Y') : '—' }}
                        </td>
                        <td>
                            @php
                                $statusStyle = match($inv->status) {
                                    'PAID' => 'background:#dcfce7;color:#166534',
                                    'PARTIAL' => 'background:#fef3c7;color:#92400e',
                                    'UNPAID' => 'background:#fee2e2;color:#991b1b',
                                    default => 'background:#f1f5f9;color:#475569'
                                };
                            @endphp
                            <span style="font-size:11px;font-weight:700;padding:3px 10px;border-radius:12px;{{ $statusStyle }}">
                                {{ $inv->status }}
                            </span>
                        </td>
                        <td style="text-align:center;white-space:nowrap">
                            <div style="display:inline-flex;gap:5px;align-items:center">
                                @if($inv->due_amount > 0)
                                    <button type="button" class="btn btn-sm btn-success" 
                                            onclick="openCollectModal('{{ $inv->id }}', '{{ $inv->invoice_no }}', '{{ $inv->due_amount }}', '{{ addslashes($inv->title) }}')"
                                            style="padding:3px 8px;font-size:11px;font-family:'Kalpurush',sans-serif" title="টাকা জমা নিন">
                                        <i class="fa-solid fa-money-bill-wave"></i> জমা
                                    </button>
                                @endif

                                <button type="button" class="btn btn-sm btn-outline" 
                                        onclick="openEditModal('{{ $inv->id }}', '{{ addslashes($inv->title) }}', '{{ $inv->category }}', '{{ $inv->amount }}', '{{ $inv->discount }}', '{{ $inv->due_date ? \Carbon\Carbon::parse($inv->due_date)->format('Y-m-d') : '' }}')"
                                        style="padding:3px 8px;font-size:11px;color:#2563eb;border-color:#93c5fd" title="ইনভয়েস এডিট করুন">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </button>

                                <form action="{{ route('admin.accounts.invoices.destroy', $inv) }}" method="POST" 
                                      onsubmit="return confirm('আপনি কি নিশ্চিত যে এই ইনভয়েসটি ({{ $inv->invoice_no }}) স্থায়ীভাবে মুছে ফেলতে চান?')" 
                                      style="display:inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline" style="padding:3px 8px;font-size:11px;color:#dc2626;border-color:#fca5a5" title="ইনভয়েস মুছুন">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="11" style="text-align:center;padding:24px;color:#94a3b8">
                            এই শিক্ষার্থীর জন্য কোনো ইনভয়েস পাওয়া যায়নি।
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Payments History Table --}}
    <div class="card" style="border-radius:12px;overflow:hidden">
        <div class="card-header" style="padding:16px 20px;background:#fff;border-bottom:1px solid #e2e8f0">
            <span class="card-title" style="font-family:'Kalpurush',sans-serif;font-size:16px;font-weight:700">
                <i class="fa-solid fa-receipt" style="color:#047857;margin-right:6px"></i> পরিশোধের রসিদ ও লেনদেন ইতিহাস (Payments Log)
            </span>
        </div>
        <div class="table-wrapper" style="overflow-x:auto">
            <table class="table-sl" style="width:100%;border-collapse:collapse">
                <thead>
                    <tr>
                        <th>রসিদ নং (Receipt)</th>
                        <th>তারিখ ও সময়</th>
                        <th>ইনভয়েস নং</th>
                        <th>পেমেন্ট মেথড</th>
                        <th>ট্রানজেকশন আইডি</th>
                        <th style="text-align:right">পরিশোধিত টাকা</th>
                        <th>গ্রহণকারী</th>
                        <th>স্ট্যাটাস</th>
                        <th style="text-align:center">রসিদ প্রিন্ট</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($payments as $pay)
                    <tr>
                        <td style="font-family:monospace;font-weight:700;color:#047857">{{ $pay->payment_no }}</td>
                        <td style="font-size:12px;color:#475569">{{ $pay->paid_at ? \Carbon\Carbon::parse($pay->paid_at)->format('d M Y, h:i A') : '—' }}</td>
                        <td style="font-family:monospace;color:#1e40af">{{ $pay->invoice?->invoice_no ?? '—' }}</td>
                        <td>
                            <span style="font-size:11px;padding:2px 8px;border-radius:10px;background:#f1f5f9;color:#1e293b;font-weight:600">
                                {{ $pay->payment_method }}
                            </span>
                        </td>
                        <td style="font-family:monospace;font-size:12px">{{ $pay->transaction_id ?? '—' }}</td>
                        <td style="text-align:right;font-weight:800;color:#047857">৳{{ number_format($pay->amount, 2) }}</td>
                        <td style="font-size:12px;color:#475569">{{ $pay->receivedBy?->name ?? 'Online / System' }}</td>
                        <td>
                            <span style="font-size:11px;font-weight:700;padding:2px 8px;border-radius:10px;background:#dcfce7;color:#166534">
                                {{ $pay->status }}
                            </span>
                        </td>
                        <td style="text-align:center">
                            <a href="{{ route('admin.accounts.payments.receipt', $pay) }}" target="_blank" class="btn btn-outline btn-sm" style="padding:2px 8px;font-size:11px">
                                <i class="fa-solid fa-print"></i> মানি রসিদ
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" style="text-align:center;padding:24px;color:#94a3b8">
                            এখনও কোনো পেমেন্ট রেকর্ড নেই।
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- MODAL: Add Custom Fee / Invoice --}}
    <div class="modal" id="addCustomFeeModal">
        <div class="modal-dialog" style="max-width:520px;font-family:'Kalpurush',sans-serif">
            <div class="modal-content">
                <form action="{{ route('admin.accounts.invoices.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="student_id" value="{{ $student->id }}">
                    <div class="modal-header">
                        <h3 class="modal-title"><i class="fa-solid fa-plus-circle" style="color:#047857"></i> নতুন ফি / ইনভয়েস যোগ করুন</h3>
                        <button type="button" class="btn-close" onclick="closeModal('addCustomFeeModal')">&times;</button>
                    </div>
                    <div class="modal-body" style="display:flex;flex-direction:column;gap:14px">
                        <div class="form-group">
                            <label style="font-weight:600">ফি এর খাত / শিরোনাম (Title) *</label>
                            <input type="text" name="title" class="form-control" placeholder="যেমন: সেমিস্টার ফি, ল্যাব ফি, জরিমানা..." required>
                        </div>
                        <div class="form-group">
                            <label style="font-weight:600">ক্যাটাগরি (Category) *</label>
                            <select name="category" class="form-control" required>
                                <option value="SEMESTER">SEMESTER (সেমিস্টার ফি / মাসিক বেতন)</option>
                                <option value="ADMISSION">ADMISSION (ভর্তি ফি)</option>
                                <option value="RETAKE">RETAKE (রিটেক ফি)</option>
                                <option value="EXAM">EXAM (পরীক্ষা ফি)</option>
                                <option value="FINE">FINE (জরিমানা / বিলম্ব ফি)</option>
                                <option value="DOCUMENT">DOCUMENT (সনদ / ডকুমেন্ট ফি)</option>
                                <option value="MANUAL">MANUAL (অন্যান্য কাস্টম ফি)</option>
                            </select>
                        </div>
                        <div class="grid-2" style="gap:12px">
                            <div class="form-group">
                                <label style="font-weight:600">মোট পরিমাণ (৳) *</label>
                                <input type="number" step="0.01" min="1" name="amount" class="form-control" placeholder="0.00" required>
                            </div>
                            <div class="form-group">
                                <label style="font-weight:600">ছাড় / ডিসকাউন্ট (৳)</label>
                                <input type="number" step="0.01" min="0" name="discount" class="form-control" value="0.00">
                            </div>
                        </div>
                        <div class="form-group">
                            <label style="font-weight:600">পরিশোধের শেষ তারিখ (Due Date)</label>
                            <input type="date" name="due_date" class="form-control" value="{{ now()->addDays(7)->format('Y-m-d') }}">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline" onclick="closeModal('addCustomFeeModal')">বাতিল</button>
                        <button type="submit" class="btn btn-primary"><i class="fa-solid fa-check"></i> ইনভয়েস তৈরি করুন</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- MODAL: Edit Existing Invoice --}}
    <div class="modal" id="editInvoiceModal">
        <div class="modal-dialog" style="max-width:520px;font-family:'Kalpurush',sans-serif">
            <div class="modal-content">
                <form id="editInvoiceForm" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-header">
                        <h3 class="modal-title"><i class="fa-solid fa-pen-to-square" style="color:#2563eb"></i> ইনভয়েস এডিট করুন</h3>
                        <button type="button" class="btn-close" onclick="closeModal('editInvoiceModal')">&times;</button>
                    </div>
                    <div class="modal-body" style="display:flex;flex-direction:column;gap:14px">
                        <div class="form-group">
                            <label style="font-weight:600">ফি এর শিরোনাম (Title) *</label>
                            <input type="text" id="edit_title" name="title" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label style="font-weight:600">ক্যাটাগরি (Category) *</label>
                            <select id="edit_category" name="category" class="form-control" required>
                                <option value="SEMESTER">SEMESTER (সেমিস্টার ফি / মাসিক বেতন)</option>
                                <option value="ADMISSION">ADMISSION (ভর্তি ফি)</option>
                                <option value="RETAKE">RETAKE (রিটেক ফি)</option>
                                <option value="EXAM">EXAM (পরীক্ষা ফি)</option>
                                <option value="FINE">FINE (জরিমানা / বিলম্ব ফি)</option>
                                <option value="DOCUMENT">DOCUMENT (সনদ / ডকুমেন্ট ফি)</option>
                                <option value="MANUAL">MANUAL (অন্যান্য কাস্টম ফি)</option>
                                <option value="COURSE_TRANSFER">COURSE_TRANSFER (কোর্স পরিবর্তন)</option>
                            </select>
                        </div>
                        <div class="grid-2" style="gap:12px">
                            <div class="form-group">
                                <label style="font-weight:600">মোট পরিমাণ (৳) *</label>
                                <input type="number" step="0.01" min="0" id="edit_amount" name="amount" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label style="font-weight:600">ছাড় (৳)</label>
                                <input type="number" step="0.01" min="0" id="edit_discount" name="discount" class="form-control">
                            </div>
                        </div>
                        <div class="form-group">
                            <label style="font-weight:600">শেষ তারিখ (Due Date)</label>
                            <input type="date" id="edit_due_date" name="due_date" class="form-control">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline" onclick="closeModal('editInvoiceModal')">বাতিল</button>
                        <button type="submit" class="btn btn-primary"><i class="fa-solid fa-check"></i> পরিবর্তন সংরক্ষণ করুন</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- MODAL: Collect Offline Payment --}}
    <div class="modal" id="collectModal">
        <div class="modal-dialog" style="max-width:500px;font-family:'Kalpurush',sans-serif">
            <div class="modal-content">
                <form id="collectForm" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h3 class="modal-title"><i class="fa-solid fa-cash-register" style="color:#047857"></i> অফলাইন পেমেন্ট গ্রহণ</h3>
                        <button type="button" class="btn-close" onclick="closeModal('collectModal')">&times;</button>
                    </div>
                    <div class="modal-body" style="display:flex;flex-direction:column;gap:14px">
                        <div style="background:#f8fafc;padding:10px 14px;border-radius:8px;border:1px solid #e2e8f0;font-size:13px">
                            ইনভয়েস: <strong id="collect_inv_no"></strong><br>
                            বিবরণ: <span id="collect_inv_title"></span><br>
                            বকেয়া প্রদেয়: <strong style="color:#dc2626" id="collect_inv_due"></strong>
                        </div>
                        <div class="form-group">
                            <label style="font-weight:600">জমার পরিমাণ (৳) *</label>
                            <input type="number" step="0.01" min="1" id="collect_amount" name="amount" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label style="font-weight:600">পেমেন্ট মেথড (Payment Method) *</label>
                            <select name="payment_method" class="form-control" required>
                                <option value="CASH">CASH (কাউন্টারে নগদ ক্যাশ গ্রহণ)</option>
                                <option value="BKASH">BKASH (ম্যানুয়াল বিকাশ)</option>
                                <option value="NAGAD">NAGAD (ম্যানুয়াল নগদ)</option>
                                <option value="ROCKET">ROCKET (ম্যানুয়াল রকেট)</option>
                                <option value="BANK_TRANSFER">BANK_TRANSFER (ব্যাংক ডিপোজিট)</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label style="font-weight:600">ট্রানজেকশন আইডি / রেফারেন্স নং</label>
                            <input type="text" name="transaction_id" class="form-control" placeholder="e.g. TRX12345678 বা মানি রসিদ নং">
                        </div>
                        <div class="form-group">
                            <label style="font-weight:600">মন্তব্য (Remarks)</label>
                            <textarea name="remarks" class="form-control" rows="2" placeholder="প্রয়োজনে নোট লিখুন..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline" onclick="closeModal('collectModal')">বাতিল</button>
                        <button type="submit" class="btn btn-primary"><i class="fa-solid fa-check"></i> টাকা গ্রহণ ও রসিদ তৈরি</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
    function openEditModal(id, title, category, amount, discount, dueDate) {
        document.getElementById('edit_title').value = title;
        document.getElementById('edit_category').value = category;
        document.getElementById('edit_amount').value = amount;
        document.getElementById('edit_discount').value = discount;
        document.getElementById('edit_due_date').value = dueDate;
        document.getElementById('editInvoiceForm').action = "/admin/accounts/invoices/" + id;
        openModal('editInvoiceModal');
    }

    function openCollectModal(id, invNo, due, title) {
        document.getElementById('collect_inv_no').innerText = invNo;
        document.getElementById('collect_inv_title').innerText = title;
        document.getElementById('collect_inv_due').innerText = '৳' + due;
        document.getElementById('collect_amount').value = due;
        document.getElementById('collect_amount').max = due;
        document.getElementById('collectForm').action = "/admin/accounts/invoices/" + id + "/collect";
        openModal('collectModal');
    }
    </script>
</x-admin-layout>
