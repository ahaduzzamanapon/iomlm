<?php

namespace App\Http\Controllers\Support;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\SupportDepartment;
use App\Models\SupportMessage;
use App\Models\SupportTicket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SupportAgentController extends Controller
{
    /**
     * Support Agent Dashboard
     */
    public function dashboard(Request $request)
    {
        $agent = Auth::user();
        $myDepartmentIds = $agent->isAdmin()
            ? SupportDepartment::pluck('id')->toArray()
            : $agent->supportDepartments()->pluck('support_departments.id')->toArray();

        $statusFilter = $request->query('status', 'ALL');

        // Department Queue Query
        $ticketQuery = SupportTicket::with('department', 'assignedAgent', 'latestMessage')
            ->whereIn('department_id', $myDepartmentIds)
            ->latest();

        if ($statusFilter !== 'ALL' && in_array($statusFilter, ['PENDING', 'IN_PROGRESS', 'CLOSED'])) {
            $ticketQuery->where('status', $statusFilter);
        }

        $tickets = $ticketQuery->paginate(15)->withQueryString();

        // Stats
        $pendingCount    = SupportTicket::whereIn('department_id', $myDepartmentIds)->where('status', 'PENDING')->count();
        $myActiveCount   = SupportTicket::where('assigned_agent_id', $agent->id)->where('status', 'IN_PROGRESS')->count();
        $myResolvedCount = SupportTicket::where('assigned_agent_id', $agent->id)->where('status', 'CLOSED')->count();

        $myDepartments   = SupportDepartment::whereIn('id', $myDepartmentIds)->get();

        return view('support.dashboard', compact('tickets', 'pendingCount', 'myActiveCount', 'myResolvedCount', 'myDepartments', 'statusFilter'));
    }

    /**
     * Live Queue API for Auto-refreshing Dashboard
     */
    public function queueApi(Request $request)
    {
        $agent = Auth::user();
        $myDepartmentIds = $agent->isAdmin()
            ? SupportDepartment::pluck('id')->toArray()
            : $agent->supportDepartments()->pluck('support_departments.id')->toArray();

        $statusFilter = $request->query('status', 'ALL');

        $ticketQuery = SupportTicket::with('department', 'assignedAgent', 'latestMessage')
            ->whereIn('department_id', $myDepartmentIds)
            ->latest();

        if ($statusFilter !== 'ALL' && in_array($statusFilter, ['PENDING', 'IN_PROGRESS', 'CLOSED'])) {
            $ticketQuery->where('status', $statusFilter);
        }

        $tickets = $ticketQuery->get()->map(function ($t) {
            $studentCode = $t->student_id ?: ($t->resolved_student?->student_code ?? null);
            return [
                'id'              => $t->id,
                'ticket_no'       => $t->ticket_no,
                'uuid'            => $t->uuid,
                'name'            => $t->name,
                'phone'           => $t->phone,
                'email'           => $t->email,
                'student_id'      => $studentCode ? str_replace('-', '', $studentCode) : null,
                'department_name' => $t->department?->name ?? '—',
                'subject'         => $t->subject,
                'problem_details' => $t->problem_details,
                'created_at'      => $t->created_at->format('d M Y, h:i A'),
                'status'          => $t->status,
                'agent_name'      => $t->assignedAgent?->name,
            ];
        });

        $pendingCount    = SupportTicket::whereIn('department_id', $myDepartmentIds)->where('status', 'PENDING')->count();
        $myActiveCount   = SupportTicket::where('assigned_agent_id', $agent->id)->where('status', 'IN_PROGRESS')->count();
        $myResolvedCount = SupportTicket::where('assigned_agent_id', $agent->id)->where('status', 'CLOSED')->count();

        return response()->json([
            'pendingCount'    => $pendingCount,
            'myActiveCount'   => $myActiveCount,
            'myResolvedCount' => $myResolvedCount,
            'tickets'         => $tickets,
        ]);
    }

    /**
     * Accept Ticket to start support chat
     */
    public function acceptTicket($uuid)
    {
        $agent = Auth::user();
        $myDepartmentIds = $agent->isAdmin()
            ? SupportDepartment::pluck('id')->toArray()
            : $agent->supportDepartments()->pluck('support_departments.id')->toArray();

        $ticket = SupportTicket::where('uuid', $uuid)->whereIn('department_id', $myDepartmentIds)->firstOrFail();

        if ($ticket->status === 'CLOSED') {
            return back()->with('error', 'এই টিকিটটি ইতিমধ্যে বন্ধ করা হয়েছে।');
        }

        $ticket->update([
            'status'            => 'IN_PROGRESS',
            'assigned_agent_id' => $agent->id,
            'accepted_at'       => now(),
        ]);

        // Send System message
        SupportMessage::create([
            'ticket_id'   => $ticket->id,
            'sender_type' => 'SYSTEM',
            'sender_id'   => $agent->id,
            'message'     => "সাপোর্ট প্রতিনিধি '{$agent->name}' আপনার টিকিটটি গ্রহণ করেছেন। এখন সরাসরি কথা বলুন।",
        ]);

        return redirect()->route('support.chat', $ticket->uuid)->with('success', 'টিকিটটি গ্রহণ করা হয়েছে!');
    }

    /**
     * Transfer Ticket to another department (Pass to Other Dept)
     */
    public function transferTicket(Request $request, $uuid)
    {
        $agent = Auth::user();
        $ticket = SupportTicket::where('uuid', $uuid)->firstOrFail();

        $validated = $request->validate([
            'new_department_id' => 'required|exists:support_departments,id',
            'reason'            => 'nullable|string|max:255',
        ]);

        $oldDept = $ticket->department->name ?? '—';
        $newDept = SupportDepartment::findOrFail($validated['new_department_id']);

        $ticket->update([
            'department_id'     => $newDept->id,
            'assigned_agent_id' => null, // Reset agent so new dept agents can accept
            'status'            => 'PENDING',
        ]);

        $reasonText = !empty($validated['reason']) ? " (কারণ: {$validated['reason']})" : '';

        SupportMessage::create([
            'ticket_id'   => $ticket->id,
            'sender_type' => 'SYSTEM',
            'sender_id'   => $agent->id,
            'message'     => "সাপোর্ট প্রতিনিধি '{$agent->name}' টিকিটটি '{$oldDept}' থেকে '{$newDept->name}' ডিপার্টমেন্টে স্থানান্তর করেছেন{$reasonText}।",
        ]);

        return redirect()->route('support.dashboard')->with('success', "টিকিটটি '{$newDept->name}' ডিপার্টমেন্টে স্থানান্তর করা হয়েছে।");
    }

    /**
     * Agent Support Chat View (Updated to include Canned Messages & Student Resolution)
     */
    public function agentChat($uuid)
    {
        $agent = Auth::user();

        $ticket = SupportTicket::with('department', 'assignedAgent', 'messages.sender')
            ->where('uuid', $uuid)
            ->firstOrFail();

        // Auto-resolve student if student_id is empty
        $resolvedStudent = $ticket->resolved_student;
        if (empty($ticket->student_id) && $resolvedStudent) {
            $ticket->update([
                'student_id' => $resolvedStudent->student_code,
                'user_id'    => $ticket->user_id ?? $resolvedStudent->user_id,
            ]);
            $ticket->refresh();
        }

        $departments = SupportDepartment::orderBy('sort_order', 'asc')->get();
        $cannedMessages = \App\Models\SupportCannedMessage::where('user_id', $agent->id)->get();

        return view('support.chat', compact('ticket', 'departments', 'cannedMessages', 'resolvedStudent'));
    }

    /**
     * API to search and return full Student Profile data for Support Panel
     */
    public function studentLookupApi(Request $request)
    {
        $term = trim($request->input('code', $request->input('query', $request->input('student_id', ''))));
        if (empty($term)) {
            return response()->json(['success' => false, 'message' => 'অনুগ্রহ করে স্টুডেন্ট আইডি দিন।'], 422);
        }

        $cleanTerm = str_replace('-', '', $term);

        $student = Student::where(function ($q) use ($term, $cleanTerm) {
                $q->where('student_code', $cleanTerm)
                  ->orWhere('student_code', $term);
                if (is_numeric($cleanTerm)) {
                    $q->orWhere('id', (int) $cleanTerm);
                }
                $q->orWhere('email', $term)
                  ->orWhere('phone', $term);
            })
            ->with([
                'user',
                'enrollments' => function ($q) {
                    $q->with(['course', 'batch', 'semester'])->latest();
                },
                'invoices' => function ($q) {
                    $q->latest()->take(10);
                },
                'attendances',
                'results' => function ($q) {
                    $q->with('exam')->latest()->take(10);
                },
            ])
            ->first();

        if (!$student) {
            return response()->json([
                'success' => false,
                'message' => "স্টুডেন্ট আইডি '{$term}'-এর বিপরীতে কোনো শিক্ষার্থী পাওয়া যায়নি।",
            ], 404);
        }

        // Academic details
        $activeEnrollment = $student->enrollments->where('status', 'ACTIVE')->first() ?? $student->enrollments->first();
        $enrollmentList = $student->enrollments->map(function ($en) {
            return [
                'course'   => $en->course?->title ?? '—',
                'batch'    => $en->batch?->name ?? '—',
                'semester' => $en->semester?->name ?? ($en->course?->is_semester_based ? 'Semester Base' : 'Full Course'),
                'status'   => $en->status ?? 'ACTIVE',
            ];
        });

        // Financials
        $totalBilled = (float) $student->invoices()->sum('payable_amount');
        $totalPaid   = (float) $student->invoices()->sum('paid_amount');
        $totalDue    = (float) $student->invoices()->sum('due_amount');

        $recentInvoices = $student->invoices->map(function ($inv) {
            return [
                'id'          => $inv->id,
                'invoice_no'  => $inv->invoice_no,
                'title'       => $inv->title ?? 'ফি',
                'total_amount'=> number_format($inv->payable_amount, 2),
                'paid_amount' => number_format($inv->paid_amount, 2),
                'due_amount'  => number_format($inv->due_amount, 2),
                'status'      => $inv->status,
                'date'        => $inv->created_at ? $inv->created_at->format('d M Y') : '—',
            ];
        });

        // Attendance
        $totalClasses = $student->attendances->count();
        $presentClasses = $student->attendances->whereIn('status', ['PRESENT', 'LATE'])->count();
        $attendanceRate = $totalClasses > 0 ? round(($presentClasses / $totalClasses) * 100, 1) : null;

        // Results
        $recentResults = $student->results->map(function ($r) {
            return [
                'exam_name'      => $r->exam?->title ?? ('Exam #' . $r->exam_id),
                'marks_obtained' => $r->marks !== null ? $r->marks : '—',
                'total_marks'    => $r->exam?->total_marks ?? '—',
                'grade'          => $r->grade ?? '—',
                'date'           => $r->created_at ? $r->created_at->format('d M Y') : '—',
            ];
        });

        $currentUser = Auth::user();
        $isAdmin = $currentUser && $currentUser->isAdmin();

        return response()->json([
            'success' => true,
            'student' => [
                'id'              => $student->id,
                'student_code'    => str_replace('-', '', $student->student_code),
                'name'            => $student->name,
                'email'           => $student->email ?? ($student->user?->email ?? '—'),
                'phone'           => $student->phone ?? '—',
                'gender'          => $student->gender ?? '—',
                'blood_group'     => $student->blood_group ?? '—',
                'status'          => $student->status,
                'photo_url'       => $student->photo_url,
                'father_name'     => $student->father_name ?? '—',
                'mother_name'     => $student->mother_name ?? '—',
                'guardian_name'   => $student->guardian_name ?? '—',
                'guardian_phone'  => $student->guardian_phone ?? '—',
                'address'         => $student->address ?? '—',
                'active_course'   => $activeEnrollment?->course?->title ?? 'কোনো সক্রিয় কোর্স নেই',
                'active_batch'    => $activeEnrollment?->batch?->name ?? '—',
                'active_semester' => $activeEnrollment?->semester?->name ?? '—',
                'enrollments'     => $enrollmentList,
                'financials'      => [
                    'total_billed' => number_format($totalBilled, 2),
                    'total_paid'   => number_format($totalPaid, 2),
                    'total_due'    => number_format($totalDue, 2),
                    'raw_due'      => $totalDue,
                    'invoices'     => $recentInvoices,
                ],
                'attendance'      => [
                    'total'       => $totalClasses,
                    'present'     => $presentClasses,
                    'percentage'  => $attendanceRate !== null ? $attendanceRate . '%' : 'রেকর্ড নেই',
                ],
                'results'         => $recentResults,
                'admin_urls'      => [
                    'profile'     => $isAdmin ? route('admin.students.show', $student->id) : null,
                    'accounts'    => $isAdmin ? route('admin.students.accounts', $student->id) : null,
                    'impersonate' => route('support.students.impersonate', $student->id),
                ],
            ],
        ]);
    }

    /**
     * Link Student ID to Support Ticket
     */
    public function linkStudent(Request $request, $uuid)
    {
        $ticket = SupportTicket::where('uuid', $uuid)->firstOrFail();
        $code = trim($request->input('student_code', ''));
        $cleanCode = str_replace('-', '', $code);

        $student = Student::where('student_code', $cleanCode)
            ->orWhere('student_code', $code)
            ->firstOrFail();

        $ticket->update([
            'student_id' => $student->student_code,
            'user_id'    => $ticket->user_id ?? $student->user_id,
        ]);

        SupportMessage::create([
            'ticket_id'   => $ticket->id,
            'sender_type' => 'SYSTEM',
            'sender_id'   => Auth::id(),
            'message'     => "স্টুডেন্ট আইডি '{$student->student_code}' ({$student->name}) এই টিকিটের সাথে যুক্ত করা হয়েছে।",
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'success'      => true,
                'student_code' => $student->student_code,
                'name'         => $student->name,
                'message'      => "স্টুডেন্ট আইডি '{$student->student_code}' লিংক করা হয়েছে।",
            ]);
        }

        return back()->with('success', "স্টুডেন্ট আইডি '{$student->student_code}' টিকিটের সাথে লিংক করা হয়েছে।");
    }

    /**
     * Agent Canned Messages (Quick Replies) Index & Storage
     */
    public function cannedMessagesIndex()
    {
        $agent = Auth::user();
        $cannedMessages = \App\Models\SupportCannedMessage::where('user_id', $agent->id)->latest()->get();
        return view('support.canned_messages', compact('cannedMessages'));
    }

    public function cannedMessagesStore(Request $request)
    {
        $agent = Auth::user();
        $validated = $request->validate([
            'title'   => 'required|string|max:150',
            'message' => 'required|string',
        ]);

        \App\Models\SupportCannedMessage::create([
            'user_id' => $agent->id,
            'title'   => $validated['title'],
            'message' => $validated['message'],
        ]);

        return back()->with('success', 'কাস্টম মেসেজ টেমপ্লেট সফলভাবে সংরক্ষিত হয়েছে।');
    }

    public function cannedMessagesUpdate(Request $request, \App\Models\SupportCannedMessage $cannedMessage)
    {
        $agent = Auth::user();
        if ($cannedMessage->user_id !== $agent->id) {
            abort(403);
        }

        $validated = $request->validate([
            'title'   => 'required|string|max:150',
            'message' => 'required|string',
        ]);

        $cannedMessage->update($validated);

        return back()->with('success', 'কাস্টম মেসেজ আপডেট করা হয়েছে।');
    }

    public function cannedMessagesDestroy(\App\Models\SupportCannedMessage $cannedMessage)
    {
        $agent = Auth::user();
        if ($cannedMessage->user_id !== $agent->id) {
            abort(403);
        }

        $cannedMessage->delete();
        return back()->with('success', 'কাস্টম মেসেজ মুছে ফেলা হয়েছে।');
    }

    /**
     * Agent Send Message
     */
    public function sendMessage(Request $request, $uuid)
    {
        $agent = Auth::user();
        $ticket = SupportTicket::where('uuid', $uuid)->firstOrFail();

        if ($ticket->status === 'CLOSED') {
            return response()->json(['error' => 'এই টিকিটটি বন্ধ করা হয়েছে।'], 422);
        }

        $request->validate([
            'message'    => 'required_without:attachment|nullable|string',
            'attachment' => 'nullable|file|mimes:jpg,jpeg,png,gif,pdf|max:5120',
        ]);

        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $attachmentPath = $request->file('attachment')->store('support_attachments/' . $ticket->id, 'public');
        }

        $msg = SupportMessage::create([
            'ticket_id'       => $ticket->id,
            'sender_type'     => 'AGENT',
            'sender_id'       => $agent->id,
            'message'         => $request->input('message') ?? '📎 ফাইল সংযুক্ত করা হয়েছে',
            'attachment_path' => $attachmentPath,
        ]);

        return response()->json(['success' => true, 'message' => $msg]);
    }

    /**
     * Close Ticket & Prompt Rating
     */
    public function closeTicket($uuid)
    {
        $agent = Auth::user();
        $ticket = SupportTicket::where('uuid', $uuid)->firstOrFail();

        $ticket->update([
            'status'    => 'CLOSED',
            'closed_at' => now(),
        ]);

        SupportMessage::create([
            'ticket_id'   => $ticket->id,
            'sender_type' => 'SYSTEM',
            'sender_id'   => $agent->id,
            'message'     => "সাপোর্ট প্রতিনিধি '{$agent->name}' কর্তৃক সেবা প্রদান সম্পন্ন হয়েছে এবং টিকিটটি বন্ধ করা হয়েছে। আপনার অভিজ্ঞতার রেটিং দিন।",
        ]);

        return redirect()->route('support.dashboard')->with('success', "টিকিট #{$ticket->ticket_no} সফলভাবে বন্ধ করা হয়েছে।");
    }
}
