<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\CourseSubjectMap;
use App\Models\Enrollment;
use App\Models\LearningResource;
use App\Models\Student;
use App\Models\Subject;
use App\Models\SubjectModule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LearningResourceController extends Controller
{
    private function student(): ?Student
    {
        return Student::where('user_id', Auth::id())->first();
    }

    public function index(Request $request)
    {
        $student = $this->student();
        $selectedSubjectId = $request->query('subject_id');

        $activeEnrollments = Enrollment::where('student_id', $student?->id)
            ->where('status', 'ACTIVE')
            ->with(['batch.course.semesters', 'course.semesters', 'batch.semesterPosition.currentSemester', 'semester'])
            ->get();

        if ($activeEnrollments->isEmpty()) {
            $activeEnrollments = Enrollment::where('student_id', $student?->id)
                ->whereNotIn('status', ['TRANSFERRED', 'CANCELLED', 'DROPPED'])
                ->with(['batch.course.semesters', 'course.semesters', 'batch.semesterPosition.currentSemester', 'semester'])
                ->get();
        }

        $enrolledSubjectIds = collect();
        $runningSemesterNames = collect();

        foreach ($activeEnrollments as $enrollment) {
            $course = $enrollment->course ?? $enrollment->batch?->course;
            if (!$course) {
                continue;
            }

            if ($course->type === 'SEMESTER_BASED') {
                $runningSemester = $enrollment->semester
                    ?? $enrollment->batch?->semesterPosition?->currentSemester
                    ?? $course->semesters->sortBy('sequence_no')->first();

                $runningSemesterId = $runningSemester?->id;

                if ($runningSemester && !empty($runningSemester->name)) {
                    $runningSemesterNames->push($runningSemester->name);
                }

                if ($runningSemesterId) {
                    $subIds = CourseSubjectMap::where('course_id', $course->id)
                        ->where('semester_id', $runningSemesterId)
                        ->pluck('subject_id');
                    $enrolledSubjectIds = $enrolledSubjectIds->merge($subIds);
                }
            } else {
                $subIds = CourseSubjectMap::where('course_id', $course->id)
                    ->pluck('subject_id');
                $enrolledSubjectIds = $enrolledSubjectIds->merge($subIds);
            }
        }

        $enrolledSubjectIds = $enrolledSubjectIds->filter()->unique()->values();
        $runningSemesterNames = $runningSemesterNames->unique()->values();

        $subjects = Subject::whereIn('id', $enrolledSubjectIds)->orderBy('name')->get();

        if ($selectedSubjectId && $enrolledSubjectIds->contains((int)$selectedSubjectId)) {
            $targetSubjectIds = collect([(int)$selectedSubjectId]);
        } else {
            $targetSubjectIds = $enrolledSubjectIds;
            $selectedSubjectId = null;
        }

        if ($targetSubjectIds->isEmpty()) {
            return view('student.resources.index', [
                'resources'            => collect(),
                'subjects'             => collect(),
                'selectedSubjectId'    => null,
                'runningSemesterNames' => $runningSemesterNames,
            ]);
        }

        // Fetch subject modules with attachments, drive links, or videos
        $modules = SubjectModule::with(['subject', 'learningResources'])
            ->whereIn('subject_id', $targetSubjectIds)
            ->where('is_hidden', false)
            ->orderBy('sequence_no')
            ->get();

        // Standard learning resources uploaded via teacher portal
        $extraResources = LearningResource::with('module.subject')
            ->whereHas('module', function ($q) use ($targetSubjectIds) {
                $q->whereIn('subject_id', $targetSubjectIds)->where('is_hidden', false);
            })
            ->latest()
            ->get();

        // Normalize all resources into a clean collection
        $resources = collect();

        foreach ($modules as $mod) {
            // 1. File attachment
            if ($mod->file_path) {
                $ext = strtolower(pathinfo($mod->file_path, PATHINFO_EXTENSION));
                $type = in_array($ext, ['pdf']) ? 'PDF' : (in_array($ext, ['doc', 'docx', 'ppt', 'pptx']) ? 'SLIDES' : 'ATTACHMENT');
                $resources->push([
                    'id'           => 'mod_file_' . $mod->id,
                    'title'        => $mod->title,
                    'description'  => $mod->description,
                    'subject_name' => $mod->subject?->name ?? '—',
                    'module_title' => $mod->title,
                    'type'         => $type,
                    'url'          => asset('storage/' . $mod->file_path),
                    'is_external'  => false,
                    'created_at'   => $mod->created_at,
                ]);
            }

            // 2. Drive link
            if ($mod->drive_link) {
                $resources->push([
                    'id'           => 'mod_drive_' . $mod->id,
                    'title'        => $mod->title . ' (Google Drive)',
                    'description'  => $mod->description,
                    'subject_name' => $mod->subject?->name ?? '—',
                    'module_title' => $mod->title,
                    'type'         => 'DRIVE',
                    'url'          => $mod->drive_link,
                    'is_external'  => true,
                    'created_at'   => $mod->created_at,
                ]);
            }

            // 3. Recorded videos
            if (!empty($mod->videos)) {
                foreach ($mod->videos as $idx => $v) {
                    $vUrl = $v['url'] ?? ($v['file_path'] ? asset('storage/' . $v['file_path']) : null);
                    if ($vUrl) {
                        $resources->push([
                            'id'           => 'mod_vid_' . $mod->id . '_' . $idx,
                            'title'        => $v['title'] ?? ($mod->title . ' - ভিডিও ' . ($idx + 1)),
                            'description'  => $mod->description,
                            'subject_name' => $mod->subject?->name ?? '—',
                            'module_title' => $mod->title,
                            'type'         => 'VIDEO',
                            'url'          => $vUrl,
                            'is_external'  => !empty($v['url']),
                            'created_at'   => $mod->created_at,
                        ]);
                    }
                }
            }

            // 4. Learning resources child records
            foreach ($mod->learningResources as $lr) {
                $resources->push([
                    'id'           => 'lr_' . $lr->id,
                    'title'        => $lr->title,
                    'description'  => null,
                    'subject_name' => $mod->subject?->name ?? '—',
                    'module_title' => $mod->title,
                    'type'         => $lr->type,
                    'url'          => $lr->url,
                    'is_external'  => str_starts_with($lr->url, 'http'),
                    'created_at'   => $lr->created_at,
                ]);
            }
        }

        // Add any remaining standalone learning resources not already captured
        foreach ($extraResources as $lr) {
            if (!$resources->contains('id', 'lr_' . $lr->id)) {
                $resources->push([
                    'id'           => 'lr_' . $lr->id,
                    'title'        => $lr->title,
                    'description'  => null,
                    'subject_name' => $lr->module?->subject?->name ?? '—',
                    'module_title' => $lr->module?->title ?? '—',
                    'type'         => $lr->type,
                    'url'          => $lr->url,
                    'is_external'  => str_starts_with($lr->url, 'http'),
                    'created_at'   => $lr->created_at,
                ]);
            }
        }

        $resources = $resources->sortByDesc('created_at')->values();

        return view('student.resources.index', compact('resources', 'subjects', 'selectedSubjectId', 'runningSemesterNames'));
    }
}
