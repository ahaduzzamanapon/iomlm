<x-admin-layout>
    <x-slot name="title">Student Profile — {{ $student->name }}</x-slot>

    <div class="page-header">
        <div class="page-header-left">
            <div style="font-size:12px;color:var(--text-muted);margin-bottom:4px">
                <a href="{{ route('admin.students.index') }}">← Back to Students</a>
            </div>
            <h1>{{ $student->name }}</h1>
            <p>Code: <strong>{{ $student->student_code ?? 'Unassigned' }}</strong> · Status: <span class="badge badge-{{ strtolower($student->status) }} no-dot">{{ ucfirst(strtolower($student->status)) }}</span></p>
        </div>
        <div class="page-header-actions">
            <a href="{{ route('admin.students.id-card', $student) }}" target="_blank" class="btn btn-outline" style="color:#4338ca">🆔 Print ID Card</a>
            <a href="{{ route('admin.students.grade-sheet', $student) }}" target="_blank" class="btn btn-outline">Grade Sheet</a>
            <a href="{{ route('admin.students.certificate', $student) }}" target="_blank" class="btn btn-outline" style="color:#d97706">Certificate</a>
            <a href="{{ route('admin.students.edit', $student) }}" class="btn btn-primary">Edit Profile</a>
        </div>
    </div>

    <div class="grid-2" style="align-items:start">

        {{-- Left Column --}}
        <div style="display:flex;flex-direction:column;gap:16px">

            {{-- Personal & Contact --}}
            <div class="card">
                <div class="card-header">
                    <span class="card-title">Personal & Contact Info</span>
                </div>
                <div style="padding:0">
                    @php
                        $adm = $student->admissionForms()->latest()->first();
                        $studentCode = $student->student_code ?? '—';
                        $phone = $student->phone ?: ($adm?->phone ?? '—');
                        $email = $student->email ?: ($adm?->email ?? ($student->user?->email ?? '—'));
                        $dob = ($student->date_of_birth ?: $adm?->date_of_birth) ? \Carbon\Carbon::parse($student->date_of_birth ?: $adm?->date_of_birth)->format('d M Y') : '—';
                        $gender = $student->gender ?: ($adm?->gender ?? '—');
                        $bloodGroup = $student->blood_group ?: ($adm?->bloodGroup?->name ?? ($adm?->blood_group ?? '—'));
                        $nationalId = $student->national_id ?: ($adm?->national_id ?? '—');
                        $nationality = $student->nationality ?? ($adm?->nationality ?? 'Bangladeshi');
                        $religion = $student->religion ?? ($adm?->religion?->name ?? ($adm?->religion ?? '—'));

                        $rows = [
                            ['Student Code',  $studentCode],
                            ['Phone',         $phone],
                            ['Email',         $email],
                            ['Date of Birth', $dob],
                            ['Gender',        $gender],
                            ['Blood Group',   $bloodGroup],
                            ['National ID',   $nationalId],
                            ['Nationality',   $nationality],
                            ['Religion',      $religion],
                        ];
                    @endphp
                    @foreach($rows as [$label, $value])
                    <div style="display:flex;padding:10px 20px;border-bottom:1px solid var(--card-border);font-size:14px">
                        <span style="width:150px;color:var(--text-muted);flex-shrink:0">{{ $label }}:</span>
                        <span style="font-weight:500">{{ $value }}</span>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- Family Info --}}
            <div class="card">
                <div class="card-header"><span class="card-title">Family Information</span></div>
                <div style="padding:0">
                    @php
                        $fatherName = $student->father_name ?: ($adm?->father_name ?? '—');
                        $motherName = $student->mother_name ?: ($adm?->mother_name ?? '—');
                        $guardianName = $student->guardian_name ?: ($adm?->guardian_name ?? '—');
                        $guardianPhone = $student->guardian_phone ?: ($adm?->guardian_phone ?? '—');

                        $family = [
                            ['Father Name',    $fatherName],
                            ['Mother Name',    $motherName],
                            ['Guardian Name',  $guardianName],
                            ['Guardian Phone', $guardianPhone],
                        ];
                    @endphp
                    @foreach($family as [$label, $value])
                    <div style="display:flex;padding:10px 20px;border-bottom:1px solid var(--card-border);font-size:14px">
                        <span style="width:150px;color:var(--text-muted);flex-shrink:0">{{ $label }}:</span>
                        <span style="font-weight:500">{{ $value }}</span>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- Academic Background --}}
            <div class="card">
                <div class="card-header"><span class="card-title">Academic Background & Address</span></div>
                <div style="padding:0">
                    @php
                        $sscInfo = ($student->ssc_gpa ? ('GPA: ' . $student->ssc_gpa) : '') 
                            ?: ($adm?->ssc_school ? ($adm->ssc_school . ($adm->ssc_year ? " ({$adm->ssc_year})" : '')) : '—');
                        $hscInfo = ($student->hsc_gpa ? ('GPA: ' . $student->hsc_gpa) : '') 
                            ?: ($adm?->hsc_college ? ($adm->hsc_college . ($adm->hsc_year ? " ({$adm->hsc_year})" : '')) : '—');
                        $qualification = $adm?->education_qualification ?? '—';
                        $presentAddress = $student->address ?: ($adm?->present_house ?: '—');
                        $permanentAddress = $adm ? ($adm->same_as_present ? 'Same as present address' : ($adm->permanent_house ?: '—')) : '—';

                        $academic = [
                            ['SSC Record',        $sscInfo],
                            ['HSC Record',        $hscInfo],
                            ['Qualification',     $qualification],
                            ['Present Address',   $presentAddress],
                            ['Permanent Address', $permanentAddress],
                        ];
                    @endphp
                    @foreach($academic as [$label, $value])
                    <div style="display:flex;padding:10px 20px;border-bottom:1px solid var(--card-border);font-size:14px">
                        <span style="width:150px;color:var(--text-muted);flex-shrink:0">{{ $label }}:</span>
                        <span style="font-weight:500">{{ $value }}</span>
                    </div>
                    @endforeach
                </div>
            </div>

        </div>

        {{-- Right Column --}}
        <div style="display:flex;flex-direction:column;gap:16px">

            {{-- Course Enrollments --}}
            <div class="card">
                <div class="card-header">
                    <span class="card-title">Course Enrollments</span>
                    <span class="badge badge-secondary no-dot">{{ $student->enrollments->count() }} Total</span>
                </div>
                <div style="padding:0">
                    @forelse($student->enrollments as $enr)
                    <div style="padding:14px 20px;border-bottom:1px solid var(--card-border)">
                        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:4px">
                            <strong style="font-size:14px">{{ $enr->batch->course->name ?? '—' }}</strong>
                            <span class="badge badge-{{ strtolower($enr->status) }}">{{ ucfirst(strtolower($enr->status)) }}</span>
                        </div>
                        <div style="font-size:12px;color:var(--text-muted)">
                            Batch: {{ $enr->batch->name ?? '—' }} · Enrolled: {{ \Carbon\Carbon::parse($enr->enrolled_at)->format('d M Y') }}
                        </div>
                        @if($enr->semester)
                        <div style="font-size:12px;color:var(--text-muted);margin-top:2px">Semester: {{ $enr->semester->name }}</div>
                        @endif
                    </div>
                    @empty
                    <div class="empty-state" style="padding:24px"><p>No course enrollments found.</p></div>
                    @endforelse
                </div>
            </div>

            {{-- Admission Info --}}
            @if($student->admissions && $student->admissions->count())
            <div class="card">
                <div class="card-header"><span class="card-title">Admission History</span></div>
                <div style="padding:0">
                    @foreach($student->admissions as $adm)
                    <div style="padding:12px 20px;border-bottom:1px solid var(--card-border);font-size:13px">
                        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:4px">
                            <div>
                                <span class="badge no-dot" style="{{ $adm->source === 'PUBLIC' ? 'background:rgba(139,92,246,.1);color:#7c3aed' : 'background:rgba(59,130,246,.1);color:#1d4ed8' }};font-size:11px">
                                    {{ $adm->source === 'PUBLIC' ? 'Public' : 'Admin' }}
                                </span>
                                @if($adm->application_no)
                                <span style="margin-left:8px;color:var(--text-muted);font-size:12px">{{ $adm->application_no }}</span>
                                @endif
                            </div>
                            <span class="badge badge-{{ strtolower($adm->status) }} no-dot">{{ $adm->status }}</span>
                        </div>
                        <div style="color:var(--text-muted)">
                            Course: {{ $adm->interestedCourse->name ?? '—' }} · Applied: {{ $adm->created_at->format('d M Y') }}
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Fee Invoices --}}
            @if($student->invoices && $student->invoices->count())
            <div class="card">
                <div class="card-header">
                    <span class="card-title">Fee Invoices</span>
                    <span class="badge badge-secondary no-dot">{{ $student->invoices->count() }} Invoices</span>
                </div>
                <div style="padding:0">
                    @foreach($student->invoices->take(5) as $inv)
                    <div style="display:flex;align-items:center;justify-content:space-between;padding:10px 20px;border-bottom:1px solid var(--card-border);font-size:13px">
                        <div>
                            <div style="font-weight:500">{{ $inv->title ?? $inv->fee_type }}</div>
                            <div style="color:var(--text-muted);font-size:12px">{{ $inv->created_at->format('d M Y') }}</div>
                        </div>
                        <div style="text-align:right">
                            <div style="font-weight:600">৳{{ number_format($inv->amount, 0) }}</div>
                            <span class="badge badge-{{ strtolower($inv->status) }} no-dot" style="font-size:11px">{{ $inv->status }}</span>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

        </div>
    </div>
</x-admin-layout>
