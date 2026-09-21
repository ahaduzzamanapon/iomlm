<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Batch;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\SentNotification;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use App\Models\UserFcmToken;
use App\Services\DynamicMailService;
use App\Services\FirebaseNotificationService;
use Illuminate\Http\Request;

class BroadcastNotificationController extends Controller
{
    public function index()
    {
        $notifications = SentNotification::with('sender')->latest()->paginate(15);
        return view('admin.notifications.index', compact('notifications'));
    }

    public function create()
    {
        $students = Student::with('user')->orderBy('name')->get();
        $batches  = Batch::orderBy('name')->get();
        $courses  = Course::with('semesters')->orderBy('name')->get();

        return view('admin.notifications.create', compact('students', 'batches', 'courses'));
    }

    public function send(Request $request, DynamicMailService $mailService, FirebaseNotificationService $fcmService)
    {
        $validated = $request->validate([
            'title'                => 'required|string|max:250',
            'message'              => 'required|string',
            'channel'              => 'required|in:PUSH,EMAIL,BOTH',
            'recipient_type'       => 'required|in:ALL_STUDENTS,ALL_TEACHERS,SPECIFIC_STUDENT,BATCH_WISE,SEMESTER_WISE',
            'specific_student_id'  => 'nullable|required_if:recipient_type,SPECIFIC_STUDENT|exists:users,id',
            'batch_id'             => 'nullable|required_if:recipient_type,BATCH_WISE|exists:batches,id',
            'semester_id'          => 'nullable|required_if:recipient_type,SEMESTER_WISE|exists:semesters,id',
            'image_url'            => 'nullable|string|max:1000',
            'image_file'           => 'nullable|image|max:3072',
            'action_url'           => 'nullable|url|max:1000',
            'scheduled_at'         => 'nullable|date',
        ]);

        // Handle Image Upload if file provided
        $imageUrl = $request->input('image_url');
        if ($request->hasFile('image_file')) {
            $path = $request->file('image_file')->store('notifications', 'public');
            $imageUrl = url('storage/' . $path);
        }

        // 1. Resolve Target Users
        $targetUsers = collect();
        $filterId = null;

        switch ($validated['recipient_type']) {
            case 'ALL_STUDENTS':
                $targetUsers = User::where('role', 'student')->get();
                break;

            case 'ALL_TEACHERS':
                $targetUsers = User::where('role', 'teacher')->get();
                break;

            case 'SPECIFIC_STUDENT':
                $filterId = $validated['specific_student_id'];
                $targetUsers = User::where('id', $filterId)->get();
                break;

            case 'BATCH_WISE':
                $filterId = $validated['batch_id'];
                $studentIds = Enrollment::where('batch_id', $filterId)->pluck('student_id')->unique();
                if ($studentIds->isEmpty()) {
                    $studentIds = Student::where('batch_id', $filterId)->pluck('id');
                }
                $userIds = Student::whereIn('id', $studentIds)->pluck('user_id');
                $targetUsers = User::whereIn('id', $userIds)->get();
                break;

            case 'SEMESTER_WISE':
                $filterId = $validated['semester_id'];
                $studentIds = Enrollment::where('semester_id', $filterId)->pluck('student_id')->unique();
                $userIds = Student::whereIn('id', $studentIds)->pluck('user_id');
                $targetUsers = User::whereIn('id', $userIds)->get();
                break;
        }

        if ($targetUsers->isEmpty()) {
            return back()->withInput()->with('error', 'নির্বাচিত ফিল্টারের জন্য কোনো প্রাপক পাওয়া যায়নি।');
        }

        // Check if Scheduled for future
        $isScheduled = !empty($validated['scheduled_at']) && \Carbon\Carbon::parse($validated['scheduled_at'])->isFuture();

        if ($isScheduled) {
            $scheduledTime = \Carbon\Carbon::parse($validated['scheduled_at']);
            SentNotification::create([
                'title'               => $validated['title'],
                'message'             => $validated['message'],
                'channel'             => $validated['channel'],
                'recipient_type'      => $validated['recipient_type'],
                'recipient_filter_id' => $filterId,
                'image_url'           => $imageUrl,
                'action_url'          => $validated['action_url'] ?? null,
                'sent_count'          => $targetUsers->count(),
                'sent_by'             => auth()->id(),
                'scheduled_at'        => $scheduledTime,
                'status'              => 'SCHEDULED',
            ]);

            return redirect()->route('admin.notifications.index')
                ->with('success', "✅ নোটিফিকেশন সফলভাবে শিডিউল করা হয়েছে! নির্ধারিত সময়ে (" . $scheduledTime->format('d M Y, h:i A') . ") এটি {$targetUsers->count()} জন প্রাপকের নিকট স্বয়ংক্রিয়ভাবে প্রেরিত হবে।");
        }

        $emailCount = 0;
        $pushCount  = 0;

        // 2. Dispatch Email Notifications
        if (in_array($validated['channel'], ['EMAIL', 'BOTH'])) {
            foreach ($targetUsers as $user) {
                if (!empty($user->email)) {
                    $sent = $mailService->sendHtmlNotification(
                        $user->email,
                        $validated['title'],
                        $validated['message'],
                        $imageUrl,
                        $validated['action_url'] ?? null
                    );
                    if ($sent) $emailCount++;
                }
            }
        }

        // 3. Dispatch Firebase Push Notifications
        if (in_array($validated['channel'], ['PUSH', 'BOTH'])) {
            $userIds = $targetUsers->pluck('id');
            $fcmTokens = UserFcmToken::whereIn('user_id', $userIds)->pluck('fcm_token')->toArray();

            if (!empty($fcmTokens)) {
                $fcmResult = $fcmService->sendPushNotification(
                    $fcmTokens,
                    $validated['title'],
                    $validated['message'],
                    $imageUrl,
                    $validated['action_url'] ?? null
                );
                $pushCount = $fcmResult['sent'] ?? 0;
            }
        }

        // 4. Record Audit Log in database
        SentNotification::create([
            'title'               => $validated['title'],
            'message'             => $validated['message'],
            'channel'             => $validated['channel'],
            'recipient_type'      => $validated['recipient_type'],
            'recipient_filter_id' => $filterId,
            'image_url'           => $imageUrl,
            'action_url'          => $validated['action_url'] ?? null,
            'sent_count'          => $targetUsers->count(),
            'sent_by'             => auth()->id(),
            'scheduled_at'        => now(),
            'status'              => 'SENT',
        ]);

        $summaryMsg = "নোটিফিকেশন সফলভাবে প্রেরিত হয়েছে! প্রাপক: {$targetUsers->count()} জন। ";
        if (in_array($validated['channel'], ['EMAIL', 'BOTH'])) {
            $summaryMsg .= "ইমেইল ডেলিভারি: {$emailCount}। ";
        }
        if (in_array($validated['channel'], ['PUSH', 'BOTH'])) {
            $summaryMsg .= "পুশ নোটিফিকেশন: {$pushCount}।";
        }

        return redirect()->route('admin.notifications.index')->with('success', $summaryMsg);
    }

    /**
     * Return JSON representation of notification for modal full view
     */
    public function showJson(SentNotification $notification)
    {
        return response()->json([
            'id'             => $notification->id,
            'title'          => $notification->title,
            'message'        => $notification->message,
            'channel'        => $notification->channel,
            'recipient_type' => $notification->recipient_type,
            'image_url'      => $notification->image_url,
            'action_url'     => $notification->action_url,
            'sent_count'     => $notification->sent_count,
            'status'         => $notification->status ?? 'SENT',
            'scheduled_at'   => $notification->scheduled_at ? $notification->scheduled_at->format('d M Y, h:i A') : null,
            'created_at'     => $notification->created_at->format('d M Y, h:i A'),
            'sender_name'    => $notification->sender?->name ?? 'System',
        ]);
    }
}
