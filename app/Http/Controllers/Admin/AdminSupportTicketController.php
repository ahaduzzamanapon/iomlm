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
     * Reassign ticket to another agent or department
     */
    public function reassign(Request $request, SupportTicket $ticket)
    {
        $validated = $request->validate([
            'assigned_agent_id' => 'nullable|exists:users,id',
            'department_id'     => 'required|exists:support_departments,id',
        ]);

        $ticket->update([
            'department_id'     => $validated['department_id'],
            'assigned_agent_id' => $validated['assigned_agent_id'] ?? null,
            'status'            => !empty($validated['assigned_agent_id']) ? 'IN_PROGRESS' : $ticket->status,
        ]);

        return back()->with('success', 'Ticket assignment updated successfully.');
    }
}
