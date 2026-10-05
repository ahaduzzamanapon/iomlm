<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseCoupon;
use Illuminate\Http\Request;

class CourseCouponController extends Controller
{
    /**
     * Store a newly created course coupon
     */
    public function store(Request $request, Course $course)
    {
        $request->validate([
            'code'            => 'required|string|max:64',
            'discount_amount' => 'required|numeric|min:0',
            'discount_type'   => 'required|in:FIXED,PERCENT',
            'max_uses'        => 'nullable|integer|min:1',
            'starts_at'       => 'nullable|date',
            'ends_at'         => 'nullable|date|after_or_equal:starts_at',
            'description'     => 'nullable|string|max:255',
        ]);

        $code = strtoupper(trim($request->input('code')));

        // Check if coupon code already exists for this course
        $exists = CourseCoupon::where('course_id', $course->id)
            ->where('code', $code)
            ->exists();

        if ($exists) {
            return back()->withInput()->with('error', "এই কোর্সের জন্য '{$code}' কুপন কোডটি ইতোমধ্যে বিদ্যমান।");
        }

        $coupon = $course->coupons()->create([
            'code'            => $code,
            'discount_type'   => $request->input('discount_type', 'FIXED'),
            'discount_amount' => (float) $request->input('discount_amount'),
            'max_uses'        => $request->filled('max_uses') ? (int) $request->input('max_uses') : null,
            'starts_at'       => $request->input('starts_at') ?: null,
            'ends_at'         => $request->input('ends_at') ?: null,
            'is_active'       => $request->has('is_active') ? $request->boolean('is_active') : true,
            'description'     => $request->input('description'),
        ]);

        return back()->with('success', "কোর্স কুপন '{$coupon->code}' সফলভাবে তৈরি করা হয়েছে!");
    }

    /**
     * Update an existing course coupon
     */
    public function update(Request $request, Course $course, CourseCoupon $coupon)
    {
        $request->validate([
            'code'            => 'required|string|max:64',
            'discount_amount' => 'required|numeric|min:0',
            'discount_type'   => 'required|in:FIXED,PERCENT',
            'max_uses'        => 'nullable|integer|min:1',
            'starts_at'       => 'nullable|date',
            'ends_at'         => 'nullable|date|after_or_equal:starts_at',
            'description'     => 'nullable|string|max:255',
        ]);

        $code = strtoupper(trim($request->input('code')));

        // Ensure unique within the course
        $duplicate = CourseCoupon::where('course_id', $course->id)
            ->where('code', $code)
            ->where('id', '!=', $coupon->id)
            ->exists();

        if ($duplicate) {
            return back()->withInput()->with('error', "এই কোর্সের জন্য '{$code}' কুপন কোডটি ইতোমধ্যে বিদ্যমান।");
        }

        $coupon->update([
            'code'            => $code,
            'discount_type'   => $request->input('discount_type', 'FIXED'),
            'discount_amount' => (float) $request->input('discount_amount'),
            'max_uses'        => $request->filled('max_uses') ? (int) $request->input('max_uses') : null,
            'starts_at'       => $request->input('starts_at') ?: null,
            'ends_at'         => $request->input('ends_at') ?: null,
            'is_active'       => $request->has('is_active') ? $request->boolean('is_active') : false,
            'description'     => $request->input('description'),
        ]);

        return back()->with('success', "কুপন কোড '{$coupon->code}' সফলভাবে হালনাগাদ (Save) করা হয়েছে!");
    }

    /**
     * Toggle active / inactive status of a coupon
     */
    public function toggleActive(Course $course, CourseCoupon $coupon)
    {
        $coupon->is_active = !$coupon->is_active;
        $coupon->save();

        $statusText = $coupon->is_active ? 'সক্রিয় (Active)' : 'নিষ্ক্রিয় (Disabled)';
        return back()->with('success', "কুপন কোড '{$coupon->code}' সফলভাবে {$statusText} করা হয়েছে!");
    }

    /**
     * Remove the specified coupon
     */
    public function destroy(Course $course, CourseCoupon $coupon)
    {
        $code = $coupon->code;
        $coupon->delete();

        return back()->with('success', "কুপন কোড '{$code}' সফলভাবে মুছে ফেলা হয়েছে!");
    }
}
