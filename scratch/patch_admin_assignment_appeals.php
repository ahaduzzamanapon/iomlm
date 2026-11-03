<?php

// 1. Update Admin AssignmentController show method to load appeals.student
$ctrlPath = __DIR__ . '/../app/Http/Controllers/Admin/AssignmentController.php';
$cc = file_get_contents($ctrlPath);
$cc = str_replace(
    "\$assignment->load(['subject', 'batch', 'teacher', 'submissions.student']);",
    "\$assignment->load(['subject', 'batch', 'teacher', 'submissions.student', 'appeals.student']);",
    $cc
);
file_put_contents($ctrlPath, $cc);
echo "Admin AssignmentController show method updated.\n";

// 2. Update resources/views/admin/assignments/show.blade.php
$showViewPath = __DIR__ . '/../resources/views/admin/assignments/show.blade.php';
$sc = file_get_contents($showViewPath);

$targetTable = '{{-- Submissions Table --}}';

$appealsBlock = '    {{-- Appeals Table --}}
    @if(isset($assignment->appeals) && $assignment->appeals->count() > 0)
    <div class="card" style="margin-bottom:24px;border:1px solid #fde68a">
        <div class="card-header" style="background:#fffbeb;display:flex;justify-content:space-between;align-items:center">
            <span class="card-title" style="color:#92400e;font-size:14px;font-family:\'Kalpurush\',sans-serif">
                <i class="fa-solid fa-clock-rotate-left" style="color:#d97706"></i> দেরিতে জমা দেওয়ার আবেদন (Late Submission Appeals)
            </span>
            <span class="badge badge-warning no-dot">{{ $assignment->appeals->where(\'status\', \'PENDING\')->count() }}টি অপেক্ষমাণ</span>
        </div>
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>শিক্ষার্থী</th>
                        <th>রোল/আইডি</th>
                        <th>আবেদনের কারণ</th>
                        <th>আবেদনের সময়</th>
                        <th>অবস্থা (Status)</th>
                        <th>অ্যাকশন (Action)</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($assignment->appeals as $app)
                    <tr>
                        <td class="td-primary">{{ $app->student?->name }}</td>
                        <td>{{ $app->student?->student_code }}</td>
                        <td style="max-width:300px;font-size:12px;color:#475569">{{ $app->reason }}</td>
                        <td class="td-muted" style="font-size:11px">{{ $app->created_at->format(\'d M Y, h:i A\') }}</td>
                        <td>
                            @if($app->isPending())
                                <span class="badge badge-warning no-dot">অপেক্ষমাণ (PENDING)</span>
                            @elseif($app->isApproved())
                                <span class="badge badge-success no-dot">অনুমোদিত (APPROVED)</span>
                            @else
                                <span class="badge badge-danger no-dot">বাতিল (REJECTED)</span>
                            @endif
                        </td>
                        <td>
                            @if($app->isPending())
                            <div style="display:flex;gap:6px">
                                <form method="POST" action="{{ route(\'admin.assignments.appeals.approve\', $app) }}" style="display:inline">
                                    @csrf
                                    <button type="submit" class="btn btn-primary btn-sm" style="font-size:11px;padding:3px 8px;background:#059669;border-color:#059669" title="বিনা ফিতে অনুমোদন করুন">
                                        <i class="fa-solid fa-check"></i> অনুমোদন
                                    </button>
                                </form>
                                <form method="POST" action="{{ route(\'admin.assignments.appeals.reject\', $app) }}" style="display:inline">
                                    @csrf
                                    <button type="submit" class="btn btn-outline btn-sm" style="font-size:11px;padding:3px 8px;color:#dc2626" title="আবেদন বাতিল করুন">
                                        <i class="fa-solid fa-xmark"></i> বাতিল
                                    </button>
                                </form>
                            </div>
                            @else
                                <span style="font-size:11px;color:#94a3b8">বিবেচিত</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    {{-- Submissions Table --}}';

$sc = str_replace($targetTable, $appealsBlock, $sc);
file_put_contents($showViewPath, $sc);
echo "Admin Assignment show view updated.\n";
