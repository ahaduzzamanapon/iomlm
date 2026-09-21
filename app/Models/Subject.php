<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{
    protected $guarded = [];

    public function category()
    {
        return $this->belongsTo(SubjectCategory::class, 'category_id');
    }

    public function modules()
    {
        return $this->hasMany(SubjectModule::class, 'subject_id')->orderBy('sequence_no');
    }

    public function courseSubjectMaps()
    {
        return $this->hasMany(CourseSubjectMap::class, 'subject_id');
    }

    public function teacherAssignments()
    {
        return $this->hasMany(SubjectTeacherAssignment::class, 'subject_id');
    }

    public function exams()
    {
        return $this->hasMany(Exam::class, 'subject_id');
    }

    public function assignments()
    {
        return $this->hasMany(Assignment::class, 'subject_id');
    }

    public function cloneSubject(?string $newCode = null, ?string $newName = null): Subject
    {
        $targetCode = $newCode ?: ($this->code . '-COPY-' . strtoupper(substr(uniqid(), -4)));
        $targetName = $newName ?: ($this->name . ' (Copy)');

        $newSubj = Subject::create([
            'category_id'   => $this->category_id,
            'department_id' => $this->department_id,
            'name'          => $targetName,
            'code'          => $targetCode,
            'credit'        => $this->credit ?? 3,
            'full_marks'    => $this->full_marks ?? 100,
            'pass_marks'    => $this->pass_marks ?? 40,
            'version'       => $this->version ?? 1,
            'is_active'     => true,
        ]);

        foreach ($this->modules as $mod) {
            $newMod = $mod->replicate();
            $newMod->subject_id = $newSubj->id;
            $newMod->save();

            foreach ($mod->learningResources as $lr) {
                $newLr = $lr->replicate();
                $newLr->module_id = $newMod->id;
                $newLr->save();
            }
        }

        return $newSubj;
    }
}
