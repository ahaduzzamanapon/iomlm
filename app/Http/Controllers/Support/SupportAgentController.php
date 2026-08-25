<?php

namespace App\Http\Controllers\Support;

use App\Http\Controllers\Controller;
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
     * Accept Ticket to start live chat
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
     * Agent Live Chat View (Updated to include Canned Messages)
     */
    public function agentChat($uuid)
    {
        $agent = Auth::user();

        $ticket = SupportTicket::with('department', 'assignedAgent', 'messages.sender')
            ->where('uuid', $uuid)
            ->firstOrFail();

        $departments = SupportDepartment::orderBy('sort_order', 'asc')->get();
        $cannedMessages = \App\Models\SupportCannedMessage::where('user_id', $agent->id)->get();

        return view('support.chat', compact('ticket', 'departments', 'cannedMessages'));
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
