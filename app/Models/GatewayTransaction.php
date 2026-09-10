<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GatewayTransaction extends Model
{
    protected $guarded = [];

    protected $casts = [
        'amount'          => 'float',
        'raw_response'    => 'array',
        'verified_at'     => 'datetime',
        'last_checked_at' => 'datetime',
        'check_attempts'  => 'integer',
    ];

    // ── Relationships ──────────────────────────────────────────────────
    public function admissionForm()
    {
        return $this->belongsTo(AdmissionForm::class, 'admission_form_id');
    }

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class, 'invoice_id');
    }

    // ── Scopes ─────────────────────────────────────────────────────────
    public function scopePending($query)
    {
        return $query->whereIn('status', ['INITIATED', 'PENDING']);
    }

    public function scopeNeedsReconciliation($query, int $olderThanMinutes = 3)
    {
        return $query->whereIn('status', ['INITIATED', 'PENDING'])
            ->where('created_at', '<=', now()->subMinutes($olderThanMinutes))
            ->where('created_at', '>=', now()->subHours(24))
            ->orderBy('last_checked_at', 'asc');
    }

    // ── Helpers ────────────────────────────────────────────────────────
    public static function generateTranId(string $prefix = 'ADM'): string
    {
        return $prefix . '-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(4)));
    }

    public function isCompleted(): bool
    {
        return $this->status === 'SUCCESS';
    }

    public function isPending(): bool
    {
        return in_array($this->status, ['INITIATED', 'PENDING']);
    }
}
