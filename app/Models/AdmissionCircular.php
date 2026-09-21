<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AdmissionCircular extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'admission_circulars';

    protected $fillable = [
        'name',
        'short_name',
        'semester_name',
        'session_year',
        'student_id_prefix',
        'ugc_id_prefix',
        'student_id_suffix',
        'program_type',
        'circular_status',
        'is_enabled',
        'is_program_batch_map_enabled',
        'remark',
        'exam_date',
        'admission_start_date',
        'admission_end_date',
    ];

    protected $casts = [
        'is_enabled' => 'boolean',
        'is_program_batch_map_enabled' => 'boolean',
        'exam_date' => 'datetime',
        'admission_start_date' => 'datetime',
        'admission_end_date' => 'datetime',
    ];

    public function circularBatches()
    {
        return $this->hasMany(AdmissionCircularBatch::class, 'admission_circular_id');
    }

    public function courses()
    {
        return $this->belongsToMany(Course::class, 'admission_circular_batches', 'admission_circular_id', 'course_id')
            ->withPivot(['batch_id', 'campus', 'is_online_admission_enabled'])
            ->withTimestamps();
    }

    public function scopeCurrent($query)
    {
        return $query->where('circular_status', 'Current')->where('is_enabled', true);
    }

    public static function getActiveCircular()
    {
        return static::current()->latest('id')->first();
    }
}
