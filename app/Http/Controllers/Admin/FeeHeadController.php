<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FeeHead;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class FeeHeadController extends Controller
{
    public function index()
    {
        $feeHeads = FeeHead::orderBy('sort_order')->orderBy('id')->get();
        return view('admin.settings.fee_heads', compact('feeHeads'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:150|unique:fee_heads,name',
        ]);

        $max = FeeHead::max('sort_order') ?? 0;
        FeeHead::create([
            'name'       => $validated['name'],
            'slug'       => Str::slug($validated['name'], '_'),
            'is_static'  => false,
            'is_active'  => true,
            'sort_order' => $max + 1,
        ]);

        return back()->with('success', "Fee Head '{$validated['name']}' added.");
    }

    public function update(Request $request, FeeHead $feeHead)
    {
        if ($feeHead->is_static) {
            return back()->with('error', 'Static fee heads cannot be edited.');
        }

        $validated = $request->validate([
            'name'      => 'required|string|max:150|unique:fee_heads,name,' . $feeHead->id,
            'is_active' => 'nullable|boolean',
        ]);

        $feeHead->update([
            'name'      => $validated['name'],
            'slug'      => Str::slug($validated['name'], '_'),
            'is_active' => $request->boolean('is_active', true),
        ]);

        return back()->with('success', "Fee Head updated.");
    }

    public function destroy(FeeHead $feeHead)
    {
        if ($feeHead->is_static) {
            return back()->with('error', 'Static fee heads (Admission Fee, Retake Fee) cannot be deleted.');
        }

        $feeHead->delete();
        return back()->with('success', "Fee Head deleted.");
    }
}
