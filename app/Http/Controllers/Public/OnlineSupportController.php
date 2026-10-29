<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\SupportDepartment;
use App\Models\SupportMessage;
use App\Models\SupportTicket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class OnlineSupportController extends Controller
{
    /**
     * Display public support form page
     */
    public function index(Request $request)
    {
        $departments = SupportDepartment::where('is_active', true)->orderBy('sort_order', 'asc')->get();
        $user = Auth::user();
        $student = $user?->student;

        return view('public.online_support', compact('departments', 'user', 'student'));
    }

    /**
     * Store new support ticket and redirect to Support Chat
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'department_id'   => 'required|exists:support_departments,id',
            'phone'           => 'required|string|max:20',
            'email'           => 'required|email|max:150',
            'name'            => 'required|string|max:150',
            'gender'          => 'required|in:MALE,FEMALE',
            'student_id'      => 'nullable|string|max:50',
            'reference'       => 'nullable|string|max:100',
            'subject'         => 'required|string|max:255',
            'problem_details' => 'required|string',
            'captcha'         => 'nullable|string',
        ]);

        $user = Auth::user();

        $studentCode = !empty($validated['student_id']) ? str_replace('-', '', $validated['student_id']) : null;
        if (empty($studentCode)) {
            $matchedStudent = $user?->student
                ?? \App\Models\Student::where('email', $validated['email'])->orWhere('phone', $validated['phone'])->first();
            if ($matchedStudent) {
                $studentCode = str_replace('-', '', $matchedStudent->student_code);
            }
        }

        $ticket = SupportTicket::create([
            'department_id'   => $validated['department_id'],
            'user_id'         => $user?->id,
            'name'            => $validated['name'],
            'email'           => $validated['email'],
            'phone'           => $validated['phone'],
            'gender'          => $validated['gender'],
            'student_id'      => $studentCode,
            'reference'       => $validated['reference'] ?? null,
            'subject'         => $validated['subject'],
            'problem_details' => $validated['problem_details'],
            'status'          => 'PENDING',
        ]);

        // Create initial system/user message
        SupportMessage::create([
            'ticket_id'   => $ticket->id,
            'sender_type' => 'USER',
            'sender_id'   => $user?->id,
            'message'     => $validated['problem_details'],
        ]);

        // Initial system greeting
        SupportMessage::create([
            'ticket_id'   => $ticket->id,
            'sender_type' => 'SYSTEM',
            'sender_id'   => null,
            'message'     => "আপনার সাপোর্ট টিকিটটি (#{$ticket->ticket_no}) সফলভাবে জমা হয়েছে। আপনার সমস্যার সমাধান করে ২৪ ঘণ্টার মধ্যে রিপ্লাই দেওয়া হবে। অনুগ্রহ করে অপেক্ষা করুন।",
        ]);

        // Dispatch Confirmation Email with Live Chat / Tracking Link
        if (!empty($ticket->email) && filter_var($ticket->email, FILTER_VALIDATE_EMAIL)) {
            try {
                $chatUrl = route('online-support.chat', $ticket->uuid);
                $mailService = app(\App\Services\DynamicMailService::class);
                $deptName = $ticket->department?->name ?? 'সাধারণ হেল্পডেস্ক';

                $mailSubject = "আপনার সাপোর্ট অনুরোধ গ্রহণ করা হয়েছে — টিকিট নম্বর #{$ticket->ticket_no}";
                $mailBody = "আসসালামু আলাইকুম {$ticket->name},\n\n"
                    . "ইসলামিক অনলাইন মাদ্রাসায় (IOM) আপনার সাপোর্ট অনুরোধটি (টিকিট নং: #{$ticket->ticket_no}) সফলভাবে গ্রহণ করা হয়েছে।\n\n"
                    . "বিভাগ: {$deptName}\n"
                    . "বিষয়: {$ticket->subject}\n\n"
                    . "আমাদের সাপোর্ট প্রতিনিধি দ্রুত আপনার সমস্যা পর্যালোচনা করে সমাধান করবেন।\n\n"
                    . "আপনি নিচের ট্র্যাকিং লিংকে ক্লিক করে সরাসরি সাপোর্ট চ্যাটবক্স ওপেন করতে পারবেন এবং আমাদের সাথে চ্যাট করতে পারবেন:\n"
                    . "লাইভ চ্যাট ও ট্র্যাকিং লিংক: {$chatUrl}\n\n"
                    . "বিনীত,\n"
                    . "সাপোর্ট ও হেল্পডেস্ক বিভাগ,\n"
                    . "ইসলামিক অনলাইন মাদ্রাসা (IOM)";

                $mailService->sendHtmlNotification(
                    $ticket->email,
                    $mailSubject,
                    $mailBody,
                    null,
                    $chatUrl
                );
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Support Ticket Creation Email Error: ' . $e->getMessage());
            }
        }

        return redirect()->route('online-support.chat', $ticket->uuid)
            ->with('success', "সাপোর্ট অনুরোধ নম্বর #{$ticket->ticket_no} তৈরি হয়েছে। আপনার সমস্যার সমাধান করে ২৪ ঘণ্টার মধ্যে রিপ্লাই দেওয়া হবে।");
    }

    /**
     * Search previous support tickets by Phone, Email, or Ticket No
     */
    public function searchStatus(Request $request)
    {
        $query = trim($request->input('query'));
        $searchType = $request->input('search_type', 'phone'); // phone, email, ticket_no

        $tickets = collect();

        if ($query) {
            $ticketQuery = SupportTicket::with('department', 'assignedAgent')->latest();

            if ($searchType === 'email') {
                $ticketQuery->where('email', $query);
            } elseif ($searchType === 'ticket_no') {
                $ticketQuery->where('ticket_no', $query)->orWhere('uuid', $query);
            } else {
                $ticketQuery->where('phone', 'like', "%{$query}%");
            }

            $tickets = $ticketQuery->get();
        }

        $departments = SupportDepartment::where('is_active', true)->orderBy('sort_order', 'asc')->get();
        $user = Auth::user();
        $student = $user?->student;

        return view('public.online_support', compact('departments', 'user', 'student', 'tickets', 'query', 'searchType'));
    }

    /**
     * Public Support Chat View
     */
    public function chatView($uuid)
    {
        $ticket = SupportTicket::with('department', 'assignedAgent', 'messages.sender')->where('uuid', $uuid)->firstOrFail();
        return view('public.support_chat', compact('ticket'));
    }

    /**
     * Fetch messages JSON for live polling
     */
    public function getMessages($uuid)
    {
        $ticket = SupportTicket::where('uuid', $uuid)->firstOrFail();

        $messages = SupportMessage::with('sender')
            ->where('ticket_id', $ticket->id)
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(function ($msg) {
                return [
                    'id'          => $msg->id,
                    'sender_type' => $msg->sender_type,
                    'sender_name' => $msg->sender_type === 'AGENT' ? ($msg->sender?->name ?? 'Support Agent') : ($msg->sender_type === 'SYSTEM' ? 'IOM System' : 'You'),
                    'message'     => $msg->message,
                    'attachment'  => $msg->attachment_path ? asset('storage/' . $msg->attachment_path) : null,
                    'time'        => $msg->created_at->format('h:i A'),
                ];
            });

        return response()->json([
            'status'     => $ticket->status,
            'agent_name' => $ticket->assignedAgent?->name ?? 'অপেক্ষা করা হচ্ছে...',
            'rating'     => $ticket->rating,
            'messages'   => $messages,
        ]);
    }

    /**
     * Send message from user
     */
    public function sendMessage(Request $request, $uuid)
    {
        $ticket = SupportTicket::where('uuid', $uuid)->firstOrFail();

        if ($ticket->status === 'CLOSED') {
            return response()->json(['error' => 'এই টিকিটটি বন্ধ করা হয়েছে, আর নতুন কোনো বার্তা পাঠানো যাবে না।'], 422);
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
            'sender_type'     => 'USER',
            'sender_id'       => Auth::id(),
            'message'         => $request->input('message') ?? '📎 ফাইল সংযুক্ত করা হয়েছে',
            'attachment_path' => $attachmentPath,
        ]);

        // Student replied again -> mark status back to PENDING
        $ticket->update(['status' => 'PENDING']);

        return response()->json(['success' => true, 'message' => $msg]);
    }

    /**
     * Submit Rating & Feedback
     */
    public function submitRating(Request $request, $uuid)
    {
        $ticket = SupportTicket::where('uuid', $uuid)->firstOrFail();

        $request->validate([
            'rating'   => 'nullable|integer|min:1|max:5',
            'feedback' => 'nullable|string|max:500',
        ]);

        $rating = (int) ($request->input('rating') ?: 5);

        $ticket->update([
            'rating'   => $rating,
            'feedback' => $request->input('feedback'),
        ]);

        return back()->with('success', 'আপনার মূল্যবান ফিডব্যাকের জন্য ধন্যবাদ!');
    }
}
