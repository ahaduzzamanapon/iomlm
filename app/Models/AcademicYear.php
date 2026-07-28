<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AcademicYear extends Model
{
    protected $guarded = [];

    public function sessions()
    {
        return $this->hasMany(AcademicSession::class, 'academic_year_id');
    }
}
