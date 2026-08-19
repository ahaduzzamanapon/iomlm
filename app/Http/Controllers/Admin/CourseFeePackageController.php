<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseFeePackage;
use App\Models\CourseFeePackageItem;
use App\Models\FeeHead;
use Illuminate\Http\Request;

class CourseFeePackageController extends Controller
{
    /**
     * Store a new fee package for a course.
     */
    public function store(Request $request, Course $course)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:150',
            'description' => 'nullable|string',
            'is_default'  => 'nullable|boolean',
        ]);

        if ($request->boolean('is_default')) {
            // Remove existing default
            $course->feePackages()->update(['is_default' => false]);
        }

        $package = CourseFeePackage::create([
            'course_id'   => $course->id,
            'name'        => $validated['name'],
            'description' => $validated['description'] ?? null,
            'is_default'  => $request->boolean('is_default', false),
            'is_active'   => true,
        ]);

        return back()->with('success', "Fee Package '{$package->name}' created.");
    }

    /**
     * Update a fee package.
     */
    public function update(Request $request, CourseFeePackage $package)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:150',
            'description' => 'nullable|string',
            'is_default'  => 'nullable|boolean',
        ]);

        if ($request->boolean('is_default')) {
            CourseFeePackage::where('course_id', $package->course_id)->update(['is_default' => false]);
        }

        $package->update([
            'name'        => $validated['name'],
            'description' => $validated['description'] ?? null,
            'is_default'  => $request->boolean('is_default', false),
        ]);

        return back()->with('success', "Fee Package updated.");
    }

    /**
     * Delete a fee package.
     */
    public function destroy(CourseFeePackage $package)
    {
        $package->delete();
        return back()->with('success', "Fee Package deleted.");
    }

    /**
     * Set a package as default.
     */
    public function setDefault(Course $course, CourseFeePackage $package)
    {
        CourseFeePackage::where('course_id', $course->id)->update(['is_default' => false]);
        $package->update(['is_default' => true]);
        return back()->with('success', "'{$package->name}' set as default package.");
    }

    /**
     * Add an item to a package.
     */
    public function storeItem(Request $request, CourseFeePackage $package)
    {
        $validated = $request->validate([
            'fee_head_id'     => 'required|exists:fee_heads,id',
            'label'           => 'nullable|string|max:150',
            'quantity'        => 'required|integer|min:1',
            'amount_per_unit' => 'required|numeric|min:0',
        ]);

        $total = $validated['quantity'] * $validated['amount_per_unit'];
        $max   = CourseFeePackageItem::where('package_id', $package->id)->max('sort_order') ?? 0;

        CourseFeePackageItem::create([
            'package_id'      => $package->id,
            'fee_head_id'     => $validated['fee_head_id'],
            'label'           => $validated['label'] ?? null,
            'quantity'        => $validated['quantity'],
            'amount_per_unit' => $validated['amount_per_unit'],
            'total_amount'    => $total,
            'sort_order'      => $max + 1,
        ]);

        return back()->with('success', 'Fee item added to package.');
    }

    /**
     * Remove an item from a package.
     */
    public function destroyItem(CourseFeePackageItem $item)
    {
        $item->delete();
        return back()->with('success', "Fee item removed.");
    }

    /**
     * Create a package from template — auto-populates all active fee heads.
     */
    public function fromTemplate(Request $request, Course $course)
    {
        $validated = $request->validate([
            'name'       => 'required|string|max:150',
            'is_default' => 'nullable|boolean',
        ]);

        $totalMonths = $course->duration_unit === 'YEAR'
            ? $course->duration_value * 12
            : $course->duration_value;

        if ($request->boolean('is_default')) {
            $course->feePackages()->update(['is_default' => false]);
        }

        $package = CourseFeePackage::create([
            'course_id'   => $course->id,
            'name'        => $validated['name'],
            'description' => 'Auto-generated from template',
            'is_default'  => $request->boolean('is_default', false),
            'is_active'   => true,
        ]);

        // Get all active fee heads and create items
        $feeHeads = FeeHead::where('is_active', true)->orderBy('sort_order')->get();
        foreach ($feeHeads as $i => $fh) {
            $slug = $fh->slug ?? '';
            // Monthly-based fee heads get quantity = total months
            $isMonthly = str_contains(strtolower($slug), 'monthly') || str_contains(strtolower($slug), 'tuition');
            $qty       = $isMonthly ? $totalMonths : 1;
            $rate      = 0; // Admin fills in the actual amount later

            CourseFeePackageItem::create([
                'package_id'      => $package->id,
                'fee_head_id'     => $fh->id,
                'label'           => $fh->name,
                'quantity'        => $qty,
                'amount_per_unit' => $rate,
                'total_amount'    => 0,
                'sort_order'      => $i + 1,
            ]);
        }

        return back()->with('success', "✅ Template package '{$package->name}' created with " . $feeHeads->count() . " fee heads. Edit the amounts below.");
    }
}
