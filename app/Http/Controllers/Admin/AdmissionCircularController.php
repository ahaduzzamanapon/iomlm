<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdmissionCircular;
use App\Models\AdmissionCircularBatch;
use App\Models\Batch;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdmissionCircularController extends Controller
{
    public function index(Request $request)
    {
        $query = AdmissionCircular::with(['circularBatches.course', 'circularBatches.batch'])->latest('id');

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('short_name', 'like', "%{$search}%")
                  ->orWhere('session_year', 'like', "%{$search}%")
                  ->orWhere('semester_name', 'like', "%{$search}%");
            });
        }

        if ($request->filled('semester_duration')) {
            $query->where('semester_name', $request->semester_duration);
        }

        if ($request->filled('status')) {
            $query->where('circular_status', $request->status);
        }

        $circulars = $query->paginate($request->input('per_page', 20))->withQueryString();

        // Get all courses with their batches for batch settings mapping
        $courses = Course::where('is_active', true)->with(['batches' => function ($q) {
            $q->orderBy('name');
        }])->orderBy('name')->get();

        $semesters = [
            'Fall 2026 (Jul-Dec)',
            'Spring 2026 (Jan-Jun)',
            'Summer 2026',
            'Fall 2025 (Jul-Dec)',
            'Spring 2025 (Jan-Jun)',
            'Fall 2024 (Jul-Dec)',
            'Spring 2024 (Jan-Jun)',
            'Summer 2024 (4M)',
            'Admission Fall 2024 (4M) (Oct-Jan)',
        ];

        return view('admin.admission_circulars.index', compact('circulars', 'courses', 'semesters'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'                          => 'required|string|max:200',
            'short_name'                    => 'nullable|string|max:100',
            'semester_name'                 => 'nullable|string|max:100',
            'session_year'                  => 'required|string|max:50',
            'student_id_prefix'             => 'required|string|max:10',
            'ugc_id_prefix'                 => 'nullable|string|max:50',
            'student_id_suffix'             => 'nullable|string|max:50',
            'program_type'                  => 'nullable|string|max:50',
            'circular_status'               => 'required|in:Current,Expired,Upcoming',
            'remark'                        => 'nullable|string',
            'exam_date'                     => 'nullable|date',
            'admission_start_date'          => 'nullable|date',
            'admission_end_date'            => 'nullable|date',
        ]);

        DB::beginTransaction();
        try {
            $circular = AdmissionCircular::create([
                'name'                          => $validated['name'],
                'short_name'                    => !empty($validated['short_name']) ? $validated['short_name'] : $validated['name'],
                'semester_name'                 => $validated['semester_name'] ?? null,
                'session_year'                  => $validated['session_year'],
                'student_id_prefix'             => !empty($validated['student_id_prefix']) ? $validated['student_id_prefix'] : '26',
                'ugc_id_prefix'                 => $validated['ugc_id_prefix'] ?? null,
                'student_id_suffix'             => $validated['student_id_suffix'] ?? null,
                'program_type'                  => !empty($validated['program_type']) ? $validated['program_type'] : 'Any',
                'circular_status'               => $validated['circular_status'],
                'is_enabled'                    => $request->boolean('is_enabled', true),
                'is_program_batch_map_enabled'  => $request->boolean('is_program_batch_map_enabled', true),
                'remark'                        => $validated['remark'] ?? null,
                'exam_date'                     => $validated['exam_date'] ?? null,
                'admission_start_date'          => $validated['admission_start_date'] ?? null,
                'admission_end_date'            => $validated['admission_end_date'] ?? null,
            ]);

            // Save Program Batch Settings
            $batchSettings = $request->input('batch_settings', []);
            foreach ($batchSettings as $courseId => $setting) {
                AdmissionCircularBatch::create([
                    'admission_circular_id'       => $circular->id,
                    'course_id'                   => $courseId,
                    'batch_id'                    => !empty($setting['batch_id']) ? $setting['batch_id'] : null,
                    'campus'                      => $setting['campus'] ?? 'Main Campus',
                    'is_online_admission_enabled' => !empty($setting['is_enabled']) && $setting['is_enabled'] == '1',
                ]);
            }

            DB::commit();

            if ($request->wantsJson()) {
                return response()->json(['success' => true, 'message' => 'ভর্তি সার্কুলার সফলভাবে তৈরি করা হয়েছে।', 'circular' => $circular]);
            }

            return redirect()->route('admin.admission-circulars.index')->with('success', 'ভর্তি সার্কুলার সফলভাবে তৈরি করা হয়েছে।');
        } catch (\Exception $e) {
            DB::rollBack();
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
            }
            return back()->withInput()->with('error', 'সার্কুলার তৈরিতে সমস্যা হয়েছে: ' . $e->getMessage());
        }
    }

    public function show(AdmissionCircular $admissionCircular)
    {
        $admissionCircular->load(['circularBatches.course', 'circularBatches.batch']);
        return response()->json($admissionCircular);
    }

    public function update(Request $request, AdmissionCircular $admissionCircular)
    {
        $validated = $request->validate([
            'name'                          => 'required|string|max:200',
            'short_name'                    => 'nullable|string|max:100',
            'semester_name'                 => 'nullable|string|max:100',
            'session_year'                  => 'required|string|max:50',
            'student_id_prefix'             => 'required|string|max:10',
            'ugc_id_prefix'                 => 'nullable|string|max:50',
            'student_id_suffix'             => 'nullable|string|max:50',
            'program_type'                  => 'nullable|string|max:50',
            'circular_status'               => 'required|in:Current,Expired,Upcoming',
            'remark'                        => 'nullable|string',
            'exam_date'                     => 'nullable|date',
            'admission_start_date'          => 'nullable|date',
            'admission_end_date'            => 'nullable|date',
        ]);

        DB::beginTransaction();
        try {
            $admissionCircular->update([
                'name'                          => $validated['name'],
                'short_name'                    => !empty($validated['short_name']) ? $validated['short_name'] : $validated['name'],
                'semester_name'                 => $validated['semester_name'] ?? null,
                'session_year'                  => $validated['session_year'],
                'student_id_prefix'             => !empty($validated['student_id_prefix']) ? $validated['student_id_prefix'] : '26',
                'ugc_id_prefix'                 => $validated['ugc_id_prefix'] ?? null,
                'student_id_suffix'             => $validated['student_id_suffix'] ?? null,
                'program_type'                  => !empty($validated['program_type']) ? $validated['program_type'] : 'Any',
                'circular_status'               => $validated['circular_status'],
                'is_enabled'                    => $request->boolean('is_enabled'),
                'is_program_batch_map_enabled'  => $request->boolean('is_program_batch_map_enabled'),
                'remark'                        => $validated['remark'] ?? null,
                'exam_date'                     => $validated['exam_date'] ?? null,
                'admission_start_date'          => $validated['admission_start_date'] ?? null,
                'admission_end_date'            => $validated['admission_end_date'] ?? null,
            ]);

            // Sync Batch Settings
            $batchSettings = $request->input('batch_settings', []);
            foreach ($batchSettings as $courseId => $setting) {
                AdmissionCircularBatch::updateOrCreate(
                    [
                        'admission_circular_id' => $admissionCircular->id,
                        'course_id'             => $courseId,
                        'campus'                => $setting['campus'] ?? 'Main Campus',
                    ],
                    [
                        'batch_id'                    => !empty($setting['batch_id']) ? $setting['batch_id'] : null,
                        'is_online_admission_enabled' => !empty($setting['is_enabled']) && $setting['is_enabled'] == '1',
                    ]
                );
            }

            DB::commit();

            if ($request->wantsJson()) {
                return response()->json(['success' => true, 'message' => 'ভর্তি সার্কুলার সফলভাবে আপডেট করা হয়েছে।']);
            }

            return redirect()->route('admin.admission-circulars.index')->with('success', 'ভর্তি সার্কুলার সফলভাবে আপডেট করা হয়েছে।');
        } catch (\Exception $e) {
            DB::rollBack();
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
            }
            return back()->withInput()->with('error', 'আপডেট করতে সমস্যা হয়েছে: ' . $e->getMessage());
        }
    }

    public function clone(AdmissionCircular $admissionCircular)
    {
        DB::beginTransaction();
        try {
            $replica = $admissionCircular->replicate([
                'created_at',
                'updated_at',
            ]);
            $replica->name = $admissionCircular->name . ' (কপি)';
            $replica->short_name = $admissionCircular->short_name . ' (কপি)';
            $replica->circular_status = 'Upcoming';
            $replica->save();

            // Replicate batches
            foreach ($admissionCircular->circularBatches as $cb) {
                AdmissionCircularBatch::create([
                    'admission_circular_id'       => $replica->id,
                    'course_id'                   => $cb->course_id,
                    'batch_id'                    => $cb->batch_id,
                    'campus'                      => $cb->campus,
                    'is_online_admission_enabled' => $cb->is_online_admission_enabled,
                ]);
            }

            DB::commit();

            return redirect()->route('admin.admission-circulars.index')->with('success', 'সার্কুলারটি সফলভাবে ক্লোন করা হয়েছে।');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'ক্লোন করতে সমস্যা হয়েছে: ' . $e->getMessage());
        }
    }

    public function toggle(AdmissionCircular $admissionCircular)
    {
        $admissionCircular->is_enabled = !$admissionCircular->is_enabled;
        $admissionCircular->save();

        return response()->json([
            'success'    => true,
            'is_enabled' => $admissionCircular->is_enabled,
            'message'    => 'ভর্তি স্ট্যাটাস সফলভাবে পরিবর্তন করা হয়েছে।',
        ]);
    }

    public function destroy(AdmissionCircular $admissionCircular)
    {
        $admissionCircular->circularBatches()->delete();
        $admissionCircular->delete();

        return redirect()->route('admin.admission-circulars.index')->with('success', 'ভর্তি সার্কুলার সফলভাবে মুছে ফেলা হয়েছে।');
    }
}
