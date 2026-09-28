<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExamAppeal extends Model
{
    protected $guarded = [];

    protected $casts = [
        'reviewed_at' => 'datetime',
        'fee_amount'  => 'float',
    ];

    public function exam()
    {
        return $this->belongsTo(Exam::class);
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function submission()
    {
        return $this->belongsTo(ExamSubmission::class, 'submission_id');
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class, 'invoice_id');
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function isPending(): bool
    {
        return $this->status === 'PENDING';
    }

    public function isApproved(): bool
    {
        return $this->status === 'APPROVED';
    }

    public function isRejected(): bool
    {
        return $this->status === 'REJECTED';
    }

    public function isPaid(): bool
    {
        if (($this->fee_amount ?? 0) <= 0) {
            return true;
        }
        if (in_array($this->payment_status, ['PAID', 'EXEMPTED'])) {
            return true;
        }
        if ($this->invoice && $this->invoice->status === 'PAID') {
            return true;
        }
        return false;
    }

    public function requiresPayment(): bool
    {
        return $this->isApproved() && ($this->fee_amount ?? 0) > 0 && !$this->isPaid();
    }

    public function isExamExpired(): bool
    {
        if (!$this->exam) {
            return false;
        }
        return $this->exam->isExpired();
    }
}
