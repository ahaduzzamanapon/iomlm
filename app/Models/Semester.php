<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Semester extends Model
{
    protected $guarded = [];

    public function course()
    {
        return $this->belongsTo(Course::class, 'course_id');
    }

    public function courseSubjectMaps()
    {
        return $this->hasMany(CourseSubjectMap::class, 'semester_id');
    }

    public function isGrouped(): bool
    {
        return (bool) ($this->has_groups && $this->group_type && $this->group_type !== 'NONE');
    }

    public function getGroupLabelAttribute(): string
    {
        return match($this->group_type) {
            'GENDER' => 'লিঙ্গভিত্তিক (ভাই ও বোন শাখা)',
            'SPLIT'  => 'বিভাজন ভিত্তিক (' . ($this->split_count ?? 2) . ' গ্রুপ)',
            default  => 'যৌথ / সাধারণ',
        };
    }
}
