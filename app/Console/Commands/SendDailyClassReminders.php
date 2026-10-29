<?php

namespace App\Console\Commands;

use App\Models\ClassSession;
use App\Models\Enrollment;
use App\Models\RoutineEntry;
use App\Models\SentNotification;
use App\Models\Setting;
use App\Models\Student;
use App\Models\User;
use App\Models\UserFcmToken;
use App\Services\DynamicMailService;
use App\Services\FirebaseNotificationService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SendDailyClassReminders extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'classes:send-daily-reminders
                            {--date= : The date to check for classes (defaults to today YYYY-MM-DD)}
                            {--batch= : Send only for a specific batch ID}
                            {--student= : Send only for a specific student ID}
                            {--force : Force sending even if reminder already sent today}
                            {--dry-run : Simulate execution without actually sending emails or notifications}';

    /**
     * The console command description.
     */
    protected $description = 'Send morning class reminders with class names, times, and join instructions to students via email and notifications';

    public function handle(DynamicMailService $mailService, FirebaseNotificationService $fcmService): int
    {
        $dateInput = $this->option('date');
        $targetDate = $dateInput ? Carbon::parse($dateInput)->startOfDay() : Carbon::today();
        $dateStr = $targetDate->toDateString();
        $batchOption = $this->option('batch');
        $studentOption = $this->option('student');
        $force = (bool) $this->option('force');
        $dryRun = (bool) $this->option('dry-run');

        $this->info("=== [ক্লাস রিমাইন্ডার সিস্টেম] তারিখ: {$dateStr} ===");

        // 1. Auto-generate sessions for today if routine entries exist and session record is missing
        $this->ensureSessionsExistForDate($targetDate, $batchOption);

        // 2. Query today's scheduled classes
        $query = ClassSession::with(['subject', 'batch.course', 'teacher', 'routineEntry.slot', 'moduleCovered'])
            ->whereDate('session_date', $dateStr)
            ->where('status', '!=', 'CANCELLED');

        if ($batchOption) {
            $query->where('batch_id', $batchOption);
        }

        $sessions = $query->orderBy('start_time')->get();

        if ($sessions->isEmpty()) {
            $this->warn("আজকের তারিখে ({$dateStr}) কোনো নির্ধারিত ক্লাস পাওয়া যায়নি।");
            return self::SUCCESS;
        }

        $this->line("মোট নির্ধারিত ক্লাস সেশন পাওয়া গেছে: {$sessions->count()} টি।");

        // 3. Resolve active students enrolled in these batches
        $batchIds = $sessions->pluck('batch_id')->unique()->values();

        $enrQuery = Enrollment::with(['student.user', 'batch.course'])
            ->whereIn('batch_id', $batchIds)
            ->where('status', 'ACTIVE');

        if ($studentOption) {
            $enrQuery->where('student_id', $studentOption);
        }

        $enrollments = $enrQuery->get();

        if ($enrollments->isEmpty()) {
            $this->warn("সংশ্লিষ্ট ব্যাচগুলোতে কোনো সক্রিয় শিক্ষার্থী পাওয়া যায়নি।");
            return self::SUCCESS;
        }

        // 4. Group classes by student, filtering by group_tag & gender eligibility
        $studentsMap = [];
        $sessionsByStudent = [];

        foreach ($enrollments as $enr) {
            $student = $enr->student;
            if (!$student) continue;

            // Fee enforcement / suspension check
            if ($student->has_course_access === false) {
                continue;
            }

            $user = $student->user;
            if (!$user) {
                $user = User::where('id', $student->user_id)
                    ->orWhere('email', $student->email)
                    ->first();
            }

            if (!$user && empty($student->email)) {
                continue;
            }

            $studentId = $student->id;
            $studentsMap[$studentId] = [
                'student' => $student,
                'user'    => $user,
                'batch'   => $enr->batch,
            ];

            if (!isset($sessionsByStudent[$studentId])) {
                $sessionsByStudent[$studentId] = collect();
            }

            // Find sessions applicable to this student
            $studentGender = strtoupper($student->gender ?? '');
            $enrGroup = $enr->group_tag;

            foreach ($sessions as $session) {
                if ($session->batch_id !== $enr->batch_id) {
                    continue;
                }

                // Check group eligibility (MALE / FEMALE / GROUP_A / GROUP_B / ALL)
                if ($session->group_tag && $session->group_tag !== 'ALL') {
                    $allowed = false;
                    if ($session->group_tag === 'MALE' && $studentGender === 'MALE') $allowed = true;
                    elseif ($session->group_tag === 'FEMALE' && $studentGender === 'FEMALE') $allowed = true;
                    elseif ($enrGroup && $enrGroup === $session->group_tag) $allowed = true;

                    if (!$allowed) {
                        continue;
                    }
                }

                // Avoid adding duplicate session to the same student
                if (!$sessionsByStudent[$studentId]->contains('id', $session->id)) {
                    $sessionsByStudent[$studentId]->push($session);
                }
            }
        }

        $instituteName = Setting::where('key', 'institute_name')->value('value') ?? 'ইসলামিক অনলাইন মাদ্রাসা (IOM)';
        $dateBangla = $this->formatDateBangla($targetDate);
        $portalUrl = url('/student/classes/today');

        $notifiedCount = 0;
        $emailsCount = 0;
        $pushCount = 0;
        $skippedCount = 0;

        foreach ($studentsMap as $studentId => $data) {
            $student = $data['student'];
            $user = $data['user'];
            $stSessions = $sessionsByStudent[$studentId] ?? collect();

            if ($stSessions->isEmpty()) {
                continue;
            }

            // Check if reminder was already sent today for this student
            if (!$force && !$dryRun && $user) {
                $alreadySent = SentNotification::where('recipient_type', 'SPECIFIC_STUDENT')
                    ->where('recipient_filter_id', $user->id)
                    ->where('title', 'like', '%ক্লাসের রিমাইন্ডার%')
                    ->whereDate('created_at', $dateStr)
                    ->exists();

                if ($alreadySent) {
                    $skippedCount++;
                    continue;
                }
            }

            // Prepare classes data for reminder
            $classesData = [];
            foreach ($stSessions as $cs) {
                $startTime = $cs->start_time ?: $cs->routineEntry?->slot?->start_time;
                $endTime = $cs->end_time ?: $cs->routineEntry?->slot?->end_time;
                $timeRange = $this->formatTimeRangeBangla($startTime, $endTime);

                $classesData[] = [
                    'session_id'   => $cs->id,
                    'subject_name' => $cs->subject?->name ?? 'নির্ধারিত বিষয়',
                    'class_title'  => $cs->title ?: ($cs->moduleCovered?->title ?? null),
                    'batch_name'   => $cs->batch?->name ?? 'নিয়মিত ব্যাচ',
                    'course_name'  => $cs->batch?->course?->name ?? '',
                    'time_range'   => $timeRange,
                    'slot_name'    => $cs->routineEntry?->slot?->name ?? '',
                    'teacher_name' => $cs->teacher?->name ?? 'অনলাইন শিক্ষক',
                    'join_url'     => $cs->meeting_link ? route('student.classes.join', $cs) : $portalUrl,
                    'is_extra'     => (bool) ($cs->is_extra_class || empty($cs->routine_entry_id)),
                ];
            }

            $studentName = $student->name ?: ($user?->name ?? 'শিক্ষার্থী');
            $recipientEmail = $user?->email ?: $student->email;
            $classCount = count($classesData);

            // Construct Bengali In-App & Push Message
            $notifTitle = "🔔 আজকের ক্লাসের রিমাইন্ডার ({$this->toBanglaNumber($classCount)}টি ক্লাস) — {$dateBangla}";
            $notifMessage = $this->buildNotificationMessage($studentName, $dateBangla, $classesData, $portalUrl);

            if ($dryRun) {
                $this->line(" [DRY-RUN] শিক্ষার্থী: {$studentName} ({$recipientEmail}) -> {$classCount} টি ক্লাস রিমাইন্ডার প্রস্তুত।");
                $notifiedCount++;
                continue;
            }

            // 1. Dispatch Email
            $emailSent = false;
            if (!empty($recipientEmail)) {
                try {
                    $emailHtml = view('emails.daily_class_reminder', [
                        'studentName'   => $studentName,
                        'dateBangla'    => $dateBangla,
                        'classes'       => $classesData,
                        'portalUrl'     => $portalUrl,
                        'instituteName' => $instituteName,
                    ])->render();

                    $emailSubject = "🔔 আজকের ক্লাসের রিমাইন্ডার ({$dateBangla}) — {$instituteName}";

                    $emailSent = $mailService->sendHtmlNotification(
                        $recipientEmail,
                        $emailSubject,
                        $emailHtml,
                        null,
                        $portalUrl
                    );

                    if ($emailSent) {
                        $emailsCount++;
                    }
                } catch (\Throwable $e) {
                    Log::error("Class reminder email failed for student #{$studentId} ({$recipientEmail}): " . $e->getMessage());
                }
            }

            // 2. Dispatch Firebase Push Notification
            $pushSent = false;
            if ($user) {
                try {
                    $tokens = UserFcmToken::where('user_id', $user->id)->pluck('fcm_token')->toArray();
                    if (!empty($tokens)) {
                        $pushRes = $fcmService->sendPushNotification(
                            $tokens,
                            $notifTitle,
                            "আজ আপনার {$this->toBanglaNumber($classCount)}টি ক্লাস রয়েছে। সময় ও জয়েন করার নিয়ম জানতে ট্যাপ করুন।",
                            null,
                            $portalUrl
                        );
                        if (!empty($pushRes['sent'])) {
                            $pushCount++;
                            $pushSent = true;
                        }
                    }
                } catch (\Throwable $e) {
                    Log::error("Class reminder push notification failed for user #{$user->id}: " . $e->getMessage());
                }
            }

            // 3. Save In-App Notification in SentNotification
            if ($user) {
                SentNotification::create([
                    'title'               => $notifTitle,
                    'message'             => $notifMessage,
                    'sms_message'         => null,
                    'channel'             => 'ALL',
                    'recipient_type'      => 'SPECIFIC_STUDENT',
                    'recipient_filter_id' => $user->id,
                    'image_url'           => null,
                    'action_url'          => $portalUrl,
                    'sent_count'          => 1,
                    'sms_count'           => 0,
                    'email_count'         => $emailSent ? 1 : 0,
                    'push_count'          => $pushSent ? 1 : 0,
                    'sent_by'             => null, // System automated dispatch
                    'scheduled_at'        => null,
                    'status'              => 'SENT',
                ]);
            }

            $notifiedCount++;
        }

        $this->info("✅ রিমাইন্ডার প্রেরণ সম্পন্ন!");
        $this->line("• মোট শিক্ষার্থী নোটিফাইড: {$notifiedCount} জন");
        $this->line("• সফল ইমেইল প্রেরিত: {$emailsCount} টি");
        $this->line("• পুশ নোটিফিকেশন প্রেরিত: {$pushCount} টি");
        if ($skippedCount > 0) {
            $this->comment("• ইতিমধ্যে পূর্বে পাঠানো থাকায় স্কিপ করা হয়েছে: {$skippedCount} জন (পুনরায় পাঠাতে --force ব্যবহার করুন)");
        }

        return self::SUCCESS;
    }

    /**
     * Ensure session records exist for today based on routine entries.
     */
    protected function ensureSessionsExistForDate(Carbon $targetDate, ?int $batchId = null): void
    {
        $dayMap = [
            'SUN' => Carbon::SUNDAY,
            'MON' => Carbon::MONDAY,
            'TUE' => Carbon::TUESDAY,
            'WED' => Carbon::WEDNESDAY,
            'THU' => Carbon::THURSDAY,
            'FRI' => Carbon::FRIDAY,
            'SAT' => Carbon::SATURDAY,
        ];

        $targetDow = $targetDate->dayOfWeek;
        $dateStr = $targetDate->toDateString();

        $query = RoutineEntry::with(['slot', 'batch'])
            ->whereHas('batch', fn($q) => $q->where('status', 'ACTIVE'));

        if ($batchId) {
            $query->where('batch_id', $batchId);
        }

        $entries = $query->get();

        foreach ($entries as $entry) {
            if (!isset($dayMap[$entry->day_of_week])) continue;
            if ($dayMap[$entry->day_of_week] !== $targetDow) continue;

            $exists = ClassSession::where('routine_entry_id', $entry->id)
                ->whereDate('session_date', $dateStr)
                ->exists();

            if (!$exists) {
                ClassSession::create([
                    'routine_entry_id' => $entry->id,
                    'batch_id'         => $entry->batch_id,
                    'subject_id'       => $entry->subject_id,
                    'teacher_id'       => $entry->teacher_id,
                    'group_tag'        => $entry->group_tag ?? 'ALL',
                    'session_date'     => $dateStr,
                    'start_time'       => $entry->slot?->start_time,
                    'end_time'         => $entry->slot?->end_time,
                    'status'           => 'SCHEDULED',
                    'meeting_link'     => null,
                    'teacher_present'  => false,
                    'class_conducted'  => false,
                ]);
            }
        }
    }

    /**
     * Build rich Bengali notification message text including joining steps.
     */
    protected function buildNotificationMessage(string $studentName, string $dateBangla, array $classes, string $portalUrl): string
    {
        $lines = [];
        $lines[] = "আসসালামু আলাইকুম ওয়া রাহমাতুল্লাহ, {$studentName}!";
        $lines[] = "আজ ({$dateBangla}) আপনার নির্ধারিত লাইভ ক্লাসের সময়সূচী নিচে দেওয়া হলো:";
        $lines[] = "";

        foreach ($classes as $idx => $c) {
            $num = $this->toBanglaNumber($idx + 1);
            $extraTag = !empty($c['is_extra']) ? ' [এক্সট্রা ক্লাস]' : '';
            $lines[] = "{$num}. বিষয়: {$c['subject_name']}{$extraTag}";
            if (!empty($c['class_title'])) {
                $lines[] = "   টপিক: {$c['class_title']}";
            }
            $lines[] = "   সময়: {$c['time_range']}";
            $lines[] = "   উস্তাদ: " . ($c['teacher_name'] ?? 'অনলাইন শিক্ষক');
            $lines[] = "   ব্যাচ: {$c['batch_name']}";
            $lines[] = "";
        }

        $lines[] = "📌 লাইভ ক্লাসে জয়েন করার নিয়ম ও পদ্ধতি:";
        $lines[] = "১. আপনার স্টুডেন্ট পোর্টালে লগইন করুন।";
        $lines[] = "২. মেনু থেকে \"Today's Classes\" (আজকের ক্লাস) পেজে যান।";
        $lines[] = "৩. ক্লাসের পাশে থাকা \"Join Live Class\" (ক্লাসে জয়েন করুন) বাটনে ক্লিক করুন।";
        $lines[] = "৪. বাটনে ক্লিক করার সাথে সাথে আপনার হাজিরা (Attendance) স্বয়ংক্রিয়ভাবে রেকর্ড হয়ে যাবে এবং লাইভ ক্লাস ওপেন হবে।";
        $lines[] = "৫. ক্লাসে প্রবেশের পর মাইক্রোফোন মিউট রাখুন এবং মনোযোগ দিয়ে শুনুন।";
        $lines[] = "";
        $lines[] = "সরাসরি ক্লাসে প্রবেশ করতে ভিজিট করুন: {$portalUrl}";

        return implode("\n", $lines);
    }

    /**
     * Convert digits to Bengali numerals.
     */
    public function toBanglaNumber(int|string|null $number): string
    {
        if ($number === null || $number === '') return '';
        $eng = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
        $bng = ['০', '১', '২', '৩', '৪', '৫', '৬', '৭', '৮', '৯'];
        return str_replace($eng, $bng, (string)$number);
    }

    /**
     * Format Carbon date into Bengali representation.
     */
    public function formatDateBangla(Carbon $date): string
    {
        $months = [
            1 => 'জানুয়ারি', 2 => 'ফেব্রুয়ারি', 3 => 'মার্চ', 4 => 'এপ্রিল',
            5 => 'মে', 6 => 'জুন', 7 => 'জুলাই', 8 => 'আগস্ট',
            9 => 'সেপ্টেম্বর', 10 => 'অক্টোবর', 11 => 'নভেম্বর', 12 => 'ডিসেম্বর'
        ];

        $days = [
            'Sunday'    => 'রবিবার',
            'Monday'    => 'সোমবার',
            'Tuesday'   => 'মঙ্গলবার',
            'Wednesday' => 'বুধবার',
            'Thursday'  => 'বৃহস্পতিবার',
            'Friday'    => 'শুক্রবার',
            'Saturday'  => 'শনিবার'
        ];

        $dayNum = $this->toBanglaNumber($date->format('j'));
        $monthName = $months[$date->month] ?? '';
        $yearNum = $this->toBanglaNumber($date->year);
        $dayName = $days[$date->format('l')] ?? $date->format('l');

        return "{$dayNum} {$monthName} {$yearNum}, {$dayName}";
    }

    /**
     * Format start & end times into culturally natural Bengali string.
     */
    public function formatTimeRangeBangla(?string $startTime, ?string $endTime): string
    {
        if (empty($startTime)) {
            return 'সময় নির্ধারিত হয়নি (TBA)';
        }

        $formattedStart = $this->formatSingleTimeBangla($startTime);

        if (!empty($endTime)) {
            $formattedEnd = $this->formatSingleTimeBangla($endTime);
            return "{$formattedStart} হতে {$formattedEnd}";
        }

        return $formattedStart;
    }

    /**
     * Format single time (HH:MM or HH:MM:SS) into Bengali string.
     */
    public function formatSingleTimeBangla(string $timeStr): string
    {
        try {
            $carbon = Carbon::parse($timeStr);
            $hour = (int) $carbon->format('H');
            $minute = $carbon->format('i');

            $partOfDay = match (true) {
                $hour >= 4 && $hour < 6   => 'ভোর',
                $hour >= 6 && $hour < 12  => 'সকাল',
                $hour >= 12 && $hour < 15 => 'দুপুর',
                $hour >= 15 && $hour < 18 => 'বিকাল',
                $hour >= 18 && $hour < 20 => 'সন্ধ্যা',
                default                   => 'রাত',
            };

            $hour12 = $carbon->format('h');
            $bngHour = $this->toBanglaNumber($hour12);
            $bngMinute = $this->toBanglaNumber($minute);

            return "{$partOfDay} {$bngHour}:{$bngMinute} টা";
        } catch (\Throwable) {
            return $timeStr;
        }
    }
}
