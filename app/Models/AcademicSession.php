<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AcademicSession extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'academic_sessions';

    protected $fillable = [
        'academic_year_id',
        'name',
        'start_date',
        'end_date',
        'is_active',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date'   => 'date',
        'is_active'  => 'boolean',
    ];

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    protected static function booted()
    {
        static::saving(function ($session) {
            if ($session->is_active && !empty($session->academic_year_id)) {
                // Ensure no two sessions are active simultaneously within the same academic year
                static::where('academic_year_id', $session->academic_year_id)
                    ->when($session->exists, function ($q) use ($session) {
                        $q->where('id', '!=', $session->id);
                    })
                    ->update(['is_active' => false]);
            }
        });
    }

    /**
     * Get the current active session
     */
    public static function getActiveSession(?int $academicYearId = null): ?self
    {
        $query = static::where('is_active', true);
        if ($academicYearId) {
            $query->where('academic_year_id', $academicYearId);
        } else {
            $query->where(function ($q) {
                $q->whereHas('academicYear', fn($y) => $y->where('is_active', true))
                  ->orWhere('is_active', true);
            });
        }
        return $query->latest('id')->first();
    }
}
