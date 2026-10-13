<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Teacher extends Model
{
    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function assignments()
    {
        return $this->hasMany(SubjectTeacherAssignment::class, 'teacher_id');
    }

    public function classes()
    {
        return $this->hasMany(ClassSession::class, 'teacher_id');
    }

    /**
     * Resolve default avatar image by teacher gender
     */
    public static function defaultAvatarForGender(?string $gender): string
    {
        $g = strtolower($gender ?? '');
        if (str_contains($g, 'female') || str_contains($g, 'মহিলা') || str_contains($g, 'বোন')) {
            return '/images/avatars/employee_female.jpg';
        }
        return '/images/avatars/employee_male.jpg';
    }

    /**
     * Get photo URL with gender default avatar fallback
     */
    public function getPhotoUrlAttribute($value): ?string
    {
        if (!empty($value)) {
            return $value;
        }

        return self::defaultAvatarForGender($this->gender);
    }
}
