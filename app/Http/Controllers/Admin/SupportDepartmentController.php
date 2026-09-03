<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SupportDepartment;
use App\Models\User;
use Illuminate\Http\Request;

class SupportDepartmentController extends Controller
{
    public function index()
    {
        $departments = SupportDepartment::withCount(['agents', 'tickets'])->orderBy('sort_order', 'asc')->get();
        $supportUsers = User::whereIn('role', ['support_agent', 'support', 'admin'])->get();

        return view('admin.support.index', compact('departments', 'supportUsers'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:150|unique:support_departments,name',
            'description' => 'nullable|string',
            'sort_order'  => 'nullable|integer',
        ]);

        SupportDepartment::create([
            'name'        => $validated['name'],
            'description' => $validated['description'] ?? null,
            'sort_order'  => $validated['sort_order'] ?? 0,
            'is_active'   => $request->boolean('is_active', true),
        ]);

        return back()->with('success', 'Support department created successfully.');
    }

    public function update(Request $request, SupportDepartment $supportDepartment)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:150|unique:support_departments,name,' . $supportDepartment->id,
            'description' => 'nullable|string',
            'sort_order'  => 'nullable|integer',
        ]);

        $supportDepartment->update([
            'name'        => $validated['name'],
            'description' => $validated['description'] ?? null,
            'sort_order'  => $validated['sort_order'] ?? 0,
            'is_active'   => $request->boolean('is_active'),
        ]);

        return back()->with('success', 'Support department updated successfully.');
    }

    public function destroy(SupportDepartment $supportDepartment)
    {
        $supportDepartment->delete();
        return back()->with('success', 'Support department deleted.');
    }

    public function toggleStatus(SupportDepartment $supportDepartment)
    {
        $supportDepartment->update(['is_active' => !$supportDepartment->is_active]);
        return back()->with('success', 'Department status updated.');
    }
}
