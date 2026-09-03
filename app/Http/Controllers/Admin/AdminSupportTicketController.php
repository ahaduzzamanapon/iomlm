<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SupportDepartment;
use App\Models\SupportTicket;
use App\Models\User;
use Illuminate\Http\Request;

class AdminSupportTicketController extends Controller
{
    /**
     * Display all support tickets in Admin panel
     */
    public function index(Request $request)
    {
        $status       = $request->query('status', 'ALL');
        $departmentId = $request->query('department_id');
        $search       = trim($request->query('search'));

        $query = SupportTicket::with('department', 'assignedAgent', 'latestMessage')->latest();

        if ($status !== 'ALL' && in_array($status, ['PENDING', 'IN_PROGRESS', 'CLOSED'])) {
            $query->where('status', $status);
        }

        if ($departmentId) {
            $query->where('department_id', $departmentId);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('ticket_no', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('student_id', 'like', "%{$search}%")
                  ->orWhere('subject', 'like', "%{$search}%");
            });
        }

        $tickets = $query->paginate(20)->withQueryString();

        // Overall Analytics
        $totalCount      = SupportTicket::count();
        $pendingCount    = SupportTicket::where('status', 'PENDING')->count();
        $activeCount     = SupportTicket::where('status', 'IN_PROGRESS')->count();
        $closedCount     = SupportTicket::where('status', 'CLOSED')->count();
        $avgRating       = round(SupportTicket::whereNotNull('rating')->avg('rating') ?? 0, 1);

        $departments  = SupportDepartment::orderBy('sort_order', 'asc')->get();
        $supportAgents = User::whereIn('role', ['support_agent', 'support', 'admin'])->get();

        return view('admin.support.tickets', compact(
            'tickets', 'totalCount', 'pendingCount', 'activeCount', 'closedCount',
            'avgRating', 'departments', 'supportAgents', 'status', 'departmentId', 'search'
        ));
    }

    /**
     * Show detailed support ticket view in Admin panel
     */
    public function show(SupportTicket $ticket)
    {
        $ticket->load(['department', 'assignedAgent', 'messages.sender']);
        $departments   = SupportDepartment::orderBy('sort_order', 'asc')->get();
        $supportAgents = User::whereIn('role', ['support_agent', 'support', 'admin', 'super_admin'])->get();

        return view('admin.support.show', compact('ticket', 'departments', 'supportAgents'));
    }

    /**
     * Export all filtered support tickets to CSV format
     */
    public function exportCsv(Request $request)
    {
        $status       = $request->query('status', 'ALL');
        $departmentId = $request->query('department_id');
        $search       = trim($request->query('search'));

        $query = SupportTicket::with('department', 'assignedAgent')->latest();

        if ($status !== 'ALL' && in_array($status, ['PENDING', 'IN_PROGRESS', 'CLOSED'])) {
            $query->where('status', $status);
        }
        if ($departmentId) {
            $query->where('department_id', $departmentId);
        }
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('ticket_no', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('student_id', 'like', "%{$search}%")
                  ->orWhere('subject', 'like', "%{$search}%");
            });
        }

        $tickets = $query->get();

        $filename = 'support_tickets_' . date('Ymd_His') . '.csv';
        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ];

        $callback = function () use ($tickets) {
            $file = fopen('php://output', 'w');
            // Add UTF-8 BOM for Excel compatibility
            fputs($file, "\xEF\xBB\xBF");

            fputcsv($file, [
                'Ticket No',
                'Date',
                'Student ID / Roll',
                'User Name',
                'Phone',
                'Email',
                'Department',
                'Subject',
                'Assigned Agent',
                'Status',
                'Rating',
            ]);

            foreach ($tickets as $t) {
                fputcsv($file, [
                    $t->ticket_no,
                    $t->created_at->format('Y-m-d H:i:s'),
                    $t->student_id ?? 'N/A',
                    $t->name,
                    $t->phone,
                    $t->email,
                    $t->department->name ?? '—',
                    $t->subject,
                    $t->assignedAgent->name ?? 'Unassigned',
                    $t->status,
                    $t->rating ? "{$t->rating}/5" : 'N/A',
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Reassign ticket to another agent or department
     */
    public function reassign(Request $request, SupportTicket $ticket)
    {
        $validated = $request->validate([
            'assigned_agent_id' => 'nullable|exists:users,id',
            'department_id'     => 'required|exists:support_departments,id',
        ]);

        $oldDept = $ticket->department->name ?? '—';
        $newDept = SupportDepartment::findOrFail($validated['new_department_id'] ?? $validated['department_id']);

        $ticket->update([
            'department_id'     => $newDept->id,
            'assigned_agent_id' => $validated['assigned_agent_id'] ?? null,
            'status'            => !empty($validated['assigned_agent_id']) ? 'IN_PROGRESS' : $ticket->status,
        ]);

        \App\Models\SupportMessage::create([
            'ticket_id'   => $ticket->id,
            'sender_type' => 'SYSTEM',
            'sender_id'   => auth()->id(),
            'message'     => "অ্যাডমিন প্যানেল থেকে টিকিটটি '{$oldDept}' থেকে '{$newDept->name}' ডিপার্টমেন্টে পরিবর্তন করা হয়েছে।",
        ]);

        return back()->with('success', "টিকিটটির ডিপার্টমেন্ট সফলভাবে '{$newDept->name}' এ পরিবর্তন করা হয়েছে।");
    }
}
