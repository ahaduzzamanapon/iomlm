<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SentNotification extends Model
{
    protected $guarded = [];

    protected $casts = [
        'scheduled_at' => 'datetime',
    ];

    public function sender()
    {
        return $this->belongsTo(User::class, 'sent_by');
    }

    /**
     * Scope notifications relevant to a specific user (Admin or Student)
     */
    public function scopeForUser($query, ?User $user)
    {
        if (!$user) {
            return $query->whereRaw('1=0');
        }

        if ($user->isAdmin()) {
            return $query;
        }

        $userId = $user->id;
        $student = $user->resolveStudent();
        $batchIds = [];
        if ($student) {
            $batchIds = \App\Models\Enrollment::where('student_id', $student->id)
                ->where('status', 'ACTIVE')
                ->pluck('batch_id')
                ->filter()
                ->unique()
                ->toArray();
        }

        return $query->where(function ($q) use ($userId, $batchIds) {
            $q->whereIn('recipient_type', ['ALL', 'ALL_STUDENTS'])
              ->orWhere(function ($sq) use ($userId) {
                  $sq->whereIn('recipient_type', ['SPECIFIC_STUDENT', 'CLASS_REMINDER'])
                     ->where('recipient_filter_id', $userId);
              });

            if (!empty($batchIds)) {
                $q->orWhere(function ($sq) use ($batchIds) {
                    $sq->where('recipient_type', 'BATCH_WISE')
                       ->whereIn('recipient_filter_id', $batchIds);
                });
            }
        });
    }
}

