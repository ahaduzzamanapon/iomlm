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
        'is_active',
    ];

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }
}
