<?php

namespace App\Console\Commands;

use App\Models\ClassSession;
use App\Models\HolidayCalendar;
use App\Models\RoutineEntry;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class GenerateSessions extends Command
{
    protected $signature = 'sessions:generate
                            {--weeks=8 : How many weeks ahead to generate sessions}
                            {--batch= : Only generate for a specific batch ID}
                            {--dry-run : Show what would be created without saving}';

    protected $description = 'Auto-generate ClassSession records from routine entries (run daily via scheduler)';

    public function handle(): int
    {
        $weeksAhead = (int) $this->option('weeks');
        $batchId    = $this->option('batch');
        $dryRun     = $this->option('dry-run');

        $today   = Carbon::today();
        $endDate = $today->copy()->addWeeks($weeksAhead);

        $dayMap = [
            'SUN' => Carbon::SUNDAY,
            'MON' => Carbon::MONDAY,
            'TUE' => Carbon::TUESDAY,
            'WED' => Carbon::WEDNESDAY,
            'THU' => Carbon::THURSDAY,
            'FRI' => Carbon::FRIDAY,
            'SAT' => Carbon::SATURDAY,
        ];

        // Load holidays
        $holidayRecords = HolidayCalendar::all();
        $isHoliday = function(Carbon $date) use ($holidayRecords) {
            foreach ($holidayRecords as $h) {
                $hDate = Carbon::parse($h->date);
                if ($hDate->isSameDay($date)) {
                    return true;
                }
                if ($h->is_recurring_yearly && $hDate->month === $date->month && $hDate->day === $date->day) {
                    return true;
                }
            }
            return false;
        };

        // Load routine entries (active batches only)
        $query = RoutineEntry::with(['slot', 'batch'])
            ->whereHas('batch', fn($q) => $q->where('status', 'ACTIVE'));

        if ($batchId) {
            $query->where('batch_id', $batchId);
        }

        $entries = $query->get();

        if ($entries->isEmpty()) {
            $this->warn('No active routine entries found.');
            return self::SUCCESS;
        }

        $created = 0;
        $skipped = 0;

        foreach ($entries as $entry) {
            if (!isset($dayMap[$entry->day_of_week])) {
                $this->warn("Unknown day_of_week [{$entry->day_of_week}] for entry #{$entry->id}, skipping.");
                continue;
            }

            $targetDow   = $dayMap[$entry->day_of_week];
            $sessionDate = $today->copy();

            // Advance to next occurrence of this day (including today if it matches)
            while ($sessionDate->dayOfWeek !== $targetDow) {
                $sessionDate->addDay();
            }

            while ($sessionDate <= $endDate) {
                $dateStr = $sessionDate->toDateString();

                // Skip holidays (both exact and yearly recurring)
                if ($isHoliday($sessionDate)) {
                    $sessionDate->addWeek();
                    continue;
                }

                // Skip if session already exists for this entry+date
                $exists = ClassSession::where('routine_entry_id', $entry->id)
                    ->whereDate('session_date', $dateStr)
                    ->exists();

                if ($exists) {
                    $skipped++;
                    $sessionDate->addWeek();
                    continue;
                }

                if ($dryRun) {
                    $this->line("  [DRY-RUN] Would create: Batch #{$entry->batch_id} | {$entry->day_of_week} {$dateStr} | Subject #{$entry->subject_id}");
                    $created++;
                } else {
                    ClassSession::create([
                        'routine_entry_id' => $entry->id,
                        'batch_id'         => $entry->batch_id,
                        'subject_id'       => $entry->subject_id,
                        'teacher_id'       => $entry->teacher_id,
                        'session_date'     => $dateStr,
                        'start_time'       => $entry->slot?->start_time,
                        'status'           => 'SCHEDULED',
                        'meeting_link'     => null,
                        'teacher_present'  => false,
                        'class_conducted'  => false,
                    ]);
                    $created++;
                }

                $sessionDate->addWeek();
            }
        }

        $this->info("✅ Sessions generated: {$created} | Already existed (skipped): {$skipped}");
        $this->line("   Range: {$today->toDateString()} → {$endDate->toDateString()} ({$weeksAhead} weeks)");

        return self::SUCCESS;
    }
}
