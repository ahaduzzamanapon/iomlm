<x-admin-layout>
    <x-slot name="title">Admission Summary Report - {{ $selectedSession?->name ?? 'All Sessions' }}</x-slot>

    <style>
        /* Report Page Typography & Layout */
        .report-page-container {
            font-family: 'Kalpurush', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            color: #0f172a;
        }

        .report-header-title {
            text-align: center;
            font-size: 22px;
            font-weight: 700;
            color: #0f172a;
            margin: 0 0 16px 0;
            letter-spacing: -0.3px;
        }

        /* Filter Controls Bar */
        .report-filter-bar {
            background: #ffffff;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            padding: 12px 18px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 14px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
        }

        .report-filter-group {
            display: flex;
            align-items: center;
            gap: 14px;
            flex-wrap: wrap;
        }

        .report-control-item {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 13.5px;
            font-weight: 600;
            color: #1e293b;
        }

        .report-control-item select,
        .report-control-item input[type="date"] {
            border: 1px solid #94a3b8;
            border-radius: 6px;
            padding: 6px 12px;
            font-size: 13px;
            font-weight: 500;
            background-color: #fff;
            color: #0f172a;
            min-width: 170px;
            height: 36px;
            font-family: inherit;
        }

        .report-control-item select:focus,
        .report-control-item input[type="date"]:focus {
            outline: none;
            border-color: #047857;
            box-shadow: 0 0 0 2px rgba(4, 120, 87, 0.2);
        }

        .report-badge-incomplete {
            background: #fef2f2;
            color: #b91c1c;
            border: 1px solid #fecaca;
            padding: 6px 14px;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        /* Report Table Styling matching original institutional sheet */
        .report-table-card {
            background: #ffffff;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            overflow-x: auto;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
            margin-bottom: 24px;
        }

        .report-grid-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
            text-align: left;
        }

        .report-grid-table th,
        .report-grid-table td {
            border: 1px solid #cbd5e1;
            padding: 8px 12px;
            vertical-align: middle;
        }

        /* Table Section Header */
        .report-section-heading-th {
            background-color: #f8fafc;
            color: #0f172a;
            font-size: 14px;
            font-weight: 700;
            padding: 10px 14px;
            text-align: left;
            border-bottom: 2px solid #94a3b8;
        }

        /* Column Headers */
        .report-col-header-th {
            background-color: #ffffff;
            color: #0f172a;
            font-size: 13.5px;
            font-weight: 700;
            text-align: center;
        }

        .report-col-header-th.text-left {
            text-align: left;
        }

        .report-col-header-th.col-shaded {
            background-color: #e2e8f0;
            font-weight: 700;
        }

        /* Data Rows */
        .report-row-course td {
            color: #0f172a;
            background-color: #ffffff;
        }

        .report-row-course:nth-child(even) td {
            background-color: #fafafa;
        }

        .report-row-course:hover td {
            background-color: #f1f5f9;
        }

        .report-td-center {
            text-align: center;
            font-variant-numeric: tabular-nums;
        }

        .report-td-shaded {
            background-color: #f1f5f9 !important;
            font-weight: 600;
            text-align: center;
            font-variant-numeric: tabular-nums;
        }

        .report-row-course:hover .report-td-shaded {
            background-color: #e2e8f0 !important;
        }

        /* Totals Rows */
        .report-subtotal-row td {
            background-color: #f8fafc !important;
            font-weight: 700;
            border-top: 2px solid #94a3b8;
            border-bottom: 1px solid #94a3b8;
            font-size: 13.5px;
        }

        .report-grandtotal-row td {
            background-color: #e2e8f0 !important;
            font-weight: 800;
            font-size: 14.5px;
            color: #0f172a;
            border-top: 2px solid #64748b;
            border-bottom: 2px solid #64748b;
        }

        .report-btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 7px 14px;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            border: 1px solid transparent;
            text-decoration: none;
            transition: all 0.15s ease;
            font-family: inherit;
        }

        .report-btn-primary {
            background: #047857;
            color: #ffffff;
        }

        .report-btn-primary:hover {
            background: #065f46;
            color: #ffffff;
        }

        .report-btn-outline {
            background: #ffffff;
            border-color: #cbd5e1;
            color: #334155;
        }

        .report-btn-outline:hover {
            background: #f8fafc;
            border-color: #94a3b8;
            color: #0f172a;
        }

        /* Printable Sheet Optimization */
        @media print {
            .sidebar, .topbar, .report-filter-bar, .report-actions-bar, .footer, .btn, .nav-item {
                display: none !important;
            }
            .page-content, .report-page-container {
                padding: 0 !important;
                margin: 0 !important;
                width: 100% !important;
            }
            .report-table-card {
                border: none !important;
                box-shadow: none !important;
            }
            .report-grid-table th,
            .report-grid-table td {
                border: 1px solid #000000 !important;
                padding: 5px 8px !important;
                font-size: 11px !important;
            }
            .report-col-header-th.col-shaded,
            .report-td-shaded,
            .report-grandtotal-row td {
                background-color: #ececec !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            .print-only-header {
                display: block !important;
                text-align: center;
                margin-bottom: 15px;
            }
        }

        .print-only-header {
            display: none;
        }
    </style>

    <div class="report-page-container">

        {{-- Print Header (Official Printable Letterhead) --}}
        <div class="print-only-header">
            <h2 style="margin:0;font-size:20px;font-weight:800;color:#047857">Islamic Online Madrasah (IOM)</h2>
            <div style="font-size:13px;color:#475569;margin-top:2px">ভর্তি পরিসংখ্যান ও সেশন সামারি রিপোর্ট</div>
            <h3 style="margin:6px 0 0 0;font-size:16px;font-weight:700">Report of {{ $selectedSession?->name ?? 'All Sessions' }}</h3>
            <div style="font-size:12px;color:#64748b">তারিখ: {{ \Carbon\Carbon::parse($admissionDate)->format('d F Y') }} | প্রস্তুতকারী: {{ auth()->user()->name }}</div>
            <hr style="margin:10px 0;border:0;border-top:1px solid #cbd5e1">
        </div>

        {{-- Top Title matching screenshot --}}
        <h1 class="report-header-title">
            Report of {{ $selectedSession?->name ?? 'Admission' }}
        </h1>

        {{-- Filter & Control Bar matching user specifications --}}
        <div class="report-filter-bar">
            <form method="GET" action="{{ route('admin.reports.index') }}" id="reportFilterForm" class="report-filter-group" style="flex:1">
                {{-- 1. Admission Session Dropdown --}}
                <div class="report-control-item">
                    <label for="session_id">Admission Session:</label>
                    <select name="session_id" id="session_id" onchange="document.getElementById('reportFilterForm').submit()">
                        @foreach($sessions as $sess)
                            <option value="{{ $sess->id }}" {{ $selectedSession?->id == $sess->id ? 'selected' : '' }}>
                                {{ $sess->name }}{{ $sess->is_active ? ' (Current)' : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- 2. Faculty / Department Dropdown --}}
                <div class="report-control-item">
                    <label for="faculty">Faculty:</label>
                    <select name="faculty" id="faculty" onchange="document.getElementById('reportFilterForm').submit()">
                        <option value="All" {{ $selectedFaculty == 'All' ? 'selected' : '' }}>All Faculty</option>
                        @foreach($faculties as $fac)
                            <option value="{{ $fac }}" {{ $selectedFaculty == $fac ? 'selected' : '' }}>{{ $fac }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- 3. Admission Date Input --}}
                <div class="report-control-item">
                    <label for="admission_date">Admission Date:</label>
                    <input type="date" name="admission_date" id="admission_date" value="{{ $admissionDate }}" onchange="document.getElementById('reportFilterForm').submit()">
                </div>

                {{-- 4. Status Filter (Approved / All) --}}
                <div class="report-control-item">
                    <label for="status">Status:</label>
                    <select name="status" id="status" onchange="document.getElementById('reportFilterForm').submit()" style="min-width:130px">
                        <option value="APPROVED" {{ $status == 'APPROVED' ? 'selected' : '' }}>Approved Only</option>
                        <option value="ALL" {{ $status == 'ALL' ? 'selected' : '' }}>All Applications</option>
                    </select>
                </div>
            </form>

            <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap">
                {{-- Incomplete Profile Counter matching screenshot badge --}}
                <div class="report-badge-incomplete" title="Total applicants with profile completion below 95% in this session">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                    <span>Incomplete Profile: {{ $incompleteProfilesCount }}</span>
                </div>

                {{-- Action Buttons --}}
                <button type="button" class="report-btn report-btn-outline" onclick="window.print()" title="Print clean official report sheet">
                    <i class="fa-solid fa-print"></i>
                    <span>প্রিন্ট (Print)</span>
                </button>

                <a href="{{ route('admin.reports.admission-summary.export', request()->query()) }}" class="report-btn report-btn-outline" title="Export this summary as CSV">
                    <i class="fa-solid fa-file-csv" style="color:#047857"></i>
                    <span>Export CSV</span>
                </a>
            </div>
        </div>

        {{-- The Admission Summary Table matching screenshot --}}
        <div class="report-table-card">
            <table class="report-grid-table">
                <thead>
                    {{-- Sub-heading row: Academic Teacher (Department/Group) --}}
                    <tr>
                        <th colspan="6" class="report-section-heading-th">
                            <i class="fa-solid fa-chalkboard-user" style="color:#047857;margin-right:6px"></i>
                            Academic Programs &amp; Admission Distribution
                            @if($selectedSession)
                                <span style="font-weight:normal;color:#475569;font-size:12.5px;margin-left:8px">
                                    (সেশন: <strong>{{ $selectedSession->name }}</strong>{{ $selectedSession->academicYear ? ' | শিক্ষাবর্ষ: ' . $selectedSession->academicYear->name : '' }})
                                </span>
                            @endif
                        </th>
                    </tr>
                    {{-- Column Headers --}}
                    <tr>
                        <th class="report-col-header-th text-left" style="width:38%">Program's Name</th>
                        <th class="report-col-header-th" style="width:11%">Male</th>
                        <th class="report-col-header-th" style="width:11%">Female</th>
                        <th class="report-col-header-th col-shaded" style="width:13%">Grand Total</th>
                        <th class="report-col-header-th col-shaded" style="width:13%">{{ $formattedDateHeader }}</th>
                        <th class="report-col-header-th" style="width:14%">Remark</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($courses as $course)
                        @php
                            $cStat = $stats->get($course->id);
                            $maleCount   = $cStat ? (int) $cStat->male_count : 0;
                            $femaleCount = $cStat ? (int) $cStat->female_count : 0;
                            $totalCount  = $cStat ? (int) $cStat->total_count : 0;
                            $dateCount   = $cStat ? (int) $cStat->date_count : 0;
                        @endphp
                        <tr class="report-row-course">
                            {{-- Program's Name --}}
                            <td>
                                <strong>{{ $course->name }}</strong>
                                @if($course->department && $selectedFaculty === 'All')
                                    <span style="font-size:11px;color:#64748b;margin-left:6px">({{ $course->department }})</span>
                                @endif
                            </td>

                            {{-- Male --}}
                            <td class="report-td-center">
                                {{ $maleCount }}
                            </td>

                            {{-- Female --}}
                            <td class="report-td-center">
                                {{ $femaleCount }}
                            </td>

                            {{-- Grand Total --}}
                            <td class="report-td-shaded">
                                {{ $totalCount }}
                            </td>

                            {{-- Specific Admission Date Count --}}
                            <td class="report-td-shaded">
                                {{ $dateCount }}
                            </td>

                            {{-- Remark --}}
                            <td style="color:#64748b;font-size:12px">
                                @if($totalCount === 0)
                                    <span style="color:#94a3b8">—</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="text-align:center;padding:30px;color:#64748b">
                                কোনো প্রোগ্রাম বা কোর্স পাওয়া যায়নি।
                            </td>
                        </tr>
                    @endforelse

                    {{-- Subtotal Row matching screenshot --}}
                    <tr class="report-subtotal-row">
                        <td style="text-align:right;padding-right:16px">
                            Subtotal:
                        </td>
                        <td class="report-td-center">
                            {{ $subtotalMale }}
                        </td>
                        <td class="report-td-center">
                            {{ $subtotalFemale }}
                        </td>
                        <td class="report-td-center" style="background:#e2e8f0 !important">
                            {{ $subtotalTotal }}
                        </td>
                        <td class="report-td-center" style="background:#e2e8f0 !important">
                            {{ $subtotalDate }}
                        </td>
                        <td></td>
                    </tr>

                    {{-- Grand Total Row matching screenshot --}}
                    <tr class="report-grandtotal-row">
                        <td style="text-align:right;padding-right:16px">
                            Grand Total:
                        </td>
                        <td class="report-td-center">
                            {{ $subtotalMale }}
                        </td>
                        <td class="report-td-center">
                            {{ $subtotalFemale }}
                        </td>
                        <td class="report-td-center">
                            {{ $subtotalTotal }}
                        </td>
                        <td class="report-td-center">
                            {{ $subtotalDate }}
                        </td>
                        <td></td>
                    </tr>
                </tbody>
            </table>
        </div>

        {{-- Bottom Summary & Notes --}}
        <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:14px 18px;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px;font-size:12.5px;color:#475569">
            <div>
                <i class="fa-solid fa-circle-info" style="color:#047857"></i>
                ভর্তি সামারি সেশন অনুযায়ী গণনা করা হয়। ড্রপডাউন থেকে যেকোনো সেশন নির্বাচন করে পূর্ববর্তী ও বর্তমান সেশনের ভর্তি তথ্য দেখতে পারেন।
            </div>
            <div>
                সর্বশেষ হালনাগাদ: <strong>{{ now()->format('d M Y, h:i A') }}</strong>
            </div>
        </div>

    </div>
</x-admin-layout>
