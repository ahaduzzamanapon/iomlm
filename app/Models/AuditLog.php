<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class AuditLog extends Model
{
    protected $table = 'audit_logs';

    protected $guarded = [];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'created_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Polymorphic relation to auditable model
     */
    public function auditable()
    {
        return $this->morphTo();
    }

    /**
     * Helper to log an audit event
     */
    public static function log(
        string $event,
        $auditable = null,
        ?array $oldValues = null,
        ?array $newValues = null,
        ?string $description = null
    ): self {
        $userId = Auth::id();
        $ip = request()->ip() ?? '127.0.0.1';

        return self::create([
            'event'          => $event,
            'description'    => $description,
            'auditable_type' => $auditable ? get_class($auditable) : null,
            'auditable_id'   => $auditable ? $auditable->id : null,
            'user_id'        => $userId,
            'old_values'     => $oldValues,
            'new_values'     => $newValues,
            'ip_address'     => $ip,
        ]);
    }
}
