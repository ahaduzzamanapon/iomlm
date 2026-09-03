<x-admin-layout>
    <x-slot name="title">Invoices & Student Dues List</x-slot>

    <div class="page-header">
        <div class="page-header-left">
            <h1>Student Invoices &amp; Dues</h1>
            <p>Manage auto-generated &amp; manual student invoices, track due payments</p>
        </div>
        <div class="page-header-actions">
            <button class="btn btn-primary" onclick="openModal('createInvoiceModal')">
                + Create Custom Invoice
            </button>
        </div>
    </div>

    @if(session('success'))
        <div style="background:#ecfdf5;border:1px solid #a7f3d0;color:#047857;padding:12px 16px;border-radius:8px;margin-bottom:16px;font-size:13px;font-weight:600">
            {{ session('success') }}
        </div>
    @endif

    {{-- Search & Filters Bar --}}
    <form method="GET" action="{{ route('admin.accounts.invoices') }}" class="card" style="padding:14px;margin-bottom:20px">
        <div style="display:flex;gap:12px;align-items:center;flex-wrap:wrap">
            <input type="text" name="search" class="form-control" style="flex:1;min-width:240px;height:40px"
                placeholder="Search Invoice No, Student Name, Student Code..." value="{{ $search }}">

            <select name="category" class="form-control" style="width:180px;height:40px">
                <option value="">All Categories</option>
                <option value="ADMISSION" {{ $category === 'ADMISSION' ? 'selected' : '' }}>ADMISSION (ভর্তি)</option>
                <option value="SEMESTER"  {{ $category === 'SEMESTER'  ? 'selected' : '' }}>SEMESTER (সেমিস্টার)</option>
                <option value="RETAKE"    {{ $category === 'RETAKE'    ? 'selected' : '' }}>RETAKE (রিটেক)</option>
                <option value="FINE"      {{ $category === 'FINE'      ? 'selected' : '' }}>FINE (জরিমানা)</option>
                <option value="MANUAL"    {{ $category === 'MANUAL'    ? 'selected' : '' }}>MANUAL (অন্যান্য)</option>
            </select>

            <select name="status" class="form-control" style="width:160px;height:40px">
                <option value="">All Statuses</option>
                <option value="UNPAID"  {{ $status === 'UNPAID'  ? 'selected' : '' }}>UNPAID (অপরিশোধিত)</option>
                <option value="PARTIAL" {{ $status === 'PARTIAL' ? 'selected' : '' }}>PARTIAL (আংশিক)</option>
                <option value="PAID"    {{ $status === 'PAID'    ? 'selected' : '' }}>PAID (পরিশোধিত)</option>
            </select>

            <button type="submit" class="btn btn-primary" style="height:40px">Filter Invoices</button>
            @if($search || $category || $status)
                <a href="{{ route('admin.accounts.invoices') }}" class="btn btn-outline" style="height:40px">Reset</a>
            @endif
        </div>
    </form>

    {{-- Invoices Table --}}
    <div class="card">
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Invoice No</th>
                        <th>Student</th>
                        <th>Category &amp; Title</th>
                        <th>Payable</th>
                        <th>Paid</th>
                        <th>Due Amount</th>
                        <th>Status</th>
                        <th style="text-align:center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($invoices as $inv)
                    <tr>
                        <td style="font-weight:700;color:#2563eb;font-size:12px">{{ $inv->invoice_no }}</td>
                        <td>
                            <strong>{{ $inv->student->name }}</strong><br>
                            <span style="color:#64748b;font-size:11px">{{ $inv->student->student_code }}</span>
                        </td>
                        <td>
                            <span class="badge badge-secondary no-dot" style="font-size:10px">{{ $inv->category }}</span><br>
                            <span style="font-size:13px;font-weight:600">{{ $inv->title }}</span>
                        </td>
                        <td>৳{{ number_format($inv->payable_amount, 2) }}</td>
                        <td><span style="color:#10b981">৳{{ number_format($inv->paid_amount, 2) }}</span></td>
                        <td>
                            @if($inv->due_amount > 0)
                                <strong style="color:#e11d48">৳{{ number_format($inv->due_amount, 2) }}</strong>
                            @else
                                <span style="color:#10b981">৳0.00</span>
                            @endif
                        </td>
                        <td>
                            @php
                                $badgeClass = match($inv->status) {
                                    'PAID' => 'badge-success',
                                    'PARTIAL' => 'badge-warning',
                                    'UNPAID' => 'badge-danger',
                                    default => 'badge-secondary'
                                };
                            @endphp
                            <span class="badge {{ $badgeClass }} no-dot">{{ $inv->status }}</span>
                        </td>
                        <td style="text-align:center">
                            @if($inv->due_amount > 0)
                                <button class="btn btn-success btn-sm" onclick="openPaymentModal({{ $inv->id }}, '{{ $inv->invoice_no }}', {{ $inv->due_amount }})">
                                    Collect
                                </button>
                            @else
                                <span style="color:#10b981;font-size:12px;font-weight:600">Cleared</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" style="text-align:center;padding:30px;color:#94a3b8">No invoices found matching criteria.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div style="margin-top:20px">
        {{ $invoices->links() }}
    </div>

    {{-- Create Custom Invoice Modal --}}
    <div class="modal-overlay" id="createInvoiceModal">
        <div class="modal" style="max-width:550px">
            <div class="modal-header">
                <span class="modal-title">+ Create Custom Student Invoice</span>
                <button class="modal-close" onclick="closeModal('createInvoiceModal')">&times;</button>
            </div>
            <form method="POST" action="{{ route('admin.accounts.invoices.store') }}">
                @csrf
                <div class="modal-body" style="display:flex;flex-direction:column;gap:14px">
                    <div class="form-group">
                        <label>Select Student <span class="required">*</span></label>
                        <select name="student_id" class="form-control" required>
                            <option value="">-- Choose Active Student --</option>
                            @foreach($students as $st)
                                <option value="{{ $st->id }}">{{ $st->name }} (Code: {{ $st->student_code ?? 'Unassigned' }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
                        <div class="form-group">
                            <label>Category <span class="required">*</span></label>
                            <select name="category" class="form-control" required>
                                <option value="FINE">FINE (জরিমানা)</option>
                                <option value="MANUAL">MANUAL (অন্যান্য ফি)</option>
                                <option value="DOCUMENT">DOCUMENT (সার্টিফিকেট/মার্কশিট)</option>
                                <option value="EXAM">EXAM (পরীক্ষা ফি)</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Due Date</label>
                            <input type="date" name="due_date" class="form-control" value="{{ date('Y-m-d', strtotime('+7 days')) }}">
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Invoice Title / Particulars <span class="required">*</span></label>
                        <input type="text" name="title" class="form-control" placeholder="e.g. Late Fee for Exam / ID Card Renewal" required>
                    </div>

                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
                        <div class="form-group">
                            <label>Amount (৳) <span class="required">*</span></label>
                            <input type="number" step="0.01" name="amount" class="form-control" placeholder="1000.00" required>
                        </div>
                        <div class="form-group">
                            <label>Discount Amount (৳)</label>
                            <input type="number" step="0.01" name="discount" class="form-control" value="0.00">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline" onclick="closeModal('createInvoiceModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">Issue Invoice</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Collect Payment Modal --}}
    <div class="modal-overlay" id="collectModal">
        <div class="modal" style="max-width:500px">
            <div class="modal-header">
                <span class="modal-title">Receive Fee Payment</span>
                <button class="modal-close" onclick="closeModal('collectModal')">&times;</button>
            </div>
            <form id="collectForm" method="POST" action="">
                @csrf
                <div class="modal-body" style="display:flex;flex-direction:column;gap:14px">
                    <div style="background:#eff6ff;padding:10px 14px;border-radius:8px;border:1px solid #bfdbfe;font-size:13px">
                        Invoice No: <strong id="modalInvNo" style="color:#1d4ed8"></strong>
                    </div>

                    <div class="form-group">
                        <label>Amount to Collect (৳) <span class="required">*</span></label>
                        <input type="number" step="0.01" name="amount" id="modalAmount" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label>Payment Method <span class="required">*</span></label>
                        <select name="payment_method" class="form-control" required>
                            <option value="CASH">CASH (নগদ)</option>
                            <option value="BKASH">BKASH (বিকাশ)</option>
                            <option value="NAGAD">NAGAD (নগদ)</option>
                            <option value="ROCKET">ROCKET (রকেট)</option>
                            <option value="BANK_TRANSFER">BANK TRANSFER (ব্যাংক)</option>
                            <option value="CARD">CARD (কার্ড)</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Transaction ID / Ref (Optional)</label>
                        <input type="text" name="transaction_id" class="form-control" placeholder="e.g. TRX987654321">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline" onclick="closeModal('collectModal')">Cancel</button>
                    <button type="submit" class="btn btn-success">Save Payment</button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
    function openPaymentModal(invoiceId, invNo, dueAmount) {
        document.getElementById('modalInvNo').innerText = invNo;
        document.getElementById('modalAmount').value = dueAmount;
        document.getElementById('modalAmount').max = dueAmount;
        document.getElementById('collectForm').action = '/admin/accounts/invoices/' + invoiceId + '/collect';
        openModal('collectModal');
    }
    </script>
    @endpush
</x-admin-layout>
