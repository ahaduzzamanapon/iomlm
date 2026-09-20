<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SupportDepartment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class UserManagementController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('admin.module:user_management'),
        ];
    }

    /**
     * Display a listing of admin and staff users.
     */
    public function index(Request $request)
    {
        $roleFilter   = $request->query('role', 'ALL');
        $search       = $request->query('search');
        $statusFilter = $request->query('status', 'ALL');

        // Query users with administrative or staff roles
        $query = User::with('supportDepartments')
            ->whereIn('role', ['admin', 'super_admin', 'support_agent', 'support'])
            ->latest();

        if ($roleFilter !== 'ALL') {
            $query->where('role', $roleFilter);
        }

        if ($statusFilter !== 'ALL') {
            $query->where('is_active', $statusFilter === 'ACTIVE');
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('designation', 'like', "%{$search}%");
            });
        }

        $users = $query->paginate(20)->withQueryString();

        $modules = User::adminModules();

        // Calculate KPI Stats
        $totalAdmins   = User::whereIn('role', ['admin', 'super_admin'])->count();
        $activeAdmins  = User::whereIn('role', ['admin', 'super_admin'])->where('is_active', true)->count();
        $supportAgents = User::where(function ($q) {
            $q->where('can_provide_support', true)
              ->orWhereIn('role', ['support_agent', 'support']);
        })->count();

        return view('admin.users.index', compact(
            'users',
            'modules',
            'roleFilter',
            'search',
            'statusFilter',
            'totalAdmins',
            'activeAdmins',
            'supportAgents'
        ));
    }

    /**
     * Show form to create new admin user.
     */
    public function create()
    {
        $modules     = User::adminModules();
        $departments = SupportDepartment::where('is_active', true)->orderBy('name')->get();

        return view('admin.users.create', compact('modules', 'departments'));
    }

    /**
     * Store newly created admin user.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'                => 'required|string|max:150',
            'email'               => 'required|email|unique:users,email',
            'password'            => 'required|string|min:6',
            'role'                => 'required|in:admin,super_admin',
            'designation'         => 'nullable|string|max:120',
            'permissions'         => 'nullable|array',
            'permissions.*'       => 'string',
            'can_provide_support' => 'nullable|boolean',
            'departments'         => 'nullable|array',
            'departments.*'       => 'exists:support_departments,id',
        ]);

        $canSupport = $request->boolean('can_provide_support');

        $permissions = $validated['role'] === 'super_admin'
            ? array_keys(User::adminModules())
            : ($validated['permissions'] ?? []);

        $user = User::create([
            'name'                => $validated['name'],
            'email'               => $validated['email'],
            'password'            => bcrypt($validated['password']),
            'role'                => $validated['role'],
            'designation'         => $validated['designation'] ?? null,
            'admin_permissions'   => $permissions,
            'can_provide_support' => $canSupport,
            'is_active'           => true,
        ]);

        if ($canSupport && !empty($validated['departments'])) {
            $user->supportDepartments()->sync($validated['departments']);
        }

        return redirect()->route('admin.users.index')
            ->with('success', "এডমিন ইউজার '{$user->name}' সফলভাবে তৈরি করা হয়েছে।");
    }

    /**
     * Show edit form for admin user.
     */
    public function edit(User $user)
    {
        $user->load('supportDepartments');
        $modules     = User::adminModules();
        $departments = SupportDepartment::where('is_active', true)->orderBy('name')->get();

        return view('admin.users.edit', compact('user', 'modules', 'departments'));
    }

    /**
     * Update admin user.
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name'                => 'required|string|max:150',
            'email'               => 'required|email|unique:users,email,' . $user->id,
            'password'            => 'nullable|string|min:6',
            'role'                => 'required|in:admin,super_admin',
            'designation'         => 'nullable|string|max:120',
            'permissions'         => 'nullable|array',
            'permissions.*'       => 'string',
            'can_provide_support' => 'nullable|boolean',
            'departments'         => 'nullable|array',
            'departments.*'       => 'exists:support_departments,id',
            'is_active'           => 'nullable|boolean',
        ]);

        $canSupport = $request->boolean('can_provide_support');

        $permissions = $validated['role'] === 'super_admin'
            ? array_keys(User::adminModules())
            : ($validated['permissions'] ?? []);

        $updateData = [
            'name'                => $validated['name'],
            'email'               => $validated['email'],
            'role'                => $validated['role'],
            'designation'         => $validated['designation'] ?? null,
            'admin_permissions'   => $permissions,
            'can_provide_support' => $canSupport,
            'is_active'           => $request->boolean('is_active', true),
        ];

        if (!empty($validated['password'])) {
            $updateData['password'] = bcrypt($validated['password']);
        }

        $user->update($updateData);

        if ($canSupport && !empty($validated['departments'])) {
            $user->supportDepartments()->sync($validated['departments']);
        } else {
            $user->supportDepartments()->detach();
        }

        return redirect()->route('admin.users.index')
            ->with('success', "এডমিন ইউজার '{$user->name}'-এর তথ্য ও পারমিশন সফলভাবে আপডেট করা হয়েছে।");
    }

    /**
     * Delete admin user.
     */
    public function destroy(User $user)
    {
        if (auth()->id() === $user->id) {
            return back()->with('error', '⚠️ আপনি নিজের অ্যাকাউন্ট মুছে ফেলতে পারবেন না!');
        }

        $user->supportDepartments()->detach();
        $userName = $user->name;
        $user->delete();

        return back()->with('success', "এডমিন ইউজার '{$userName}' সফলভাবে মুছে ফেলা হয়েছে।");
    }

    /**
     * Toggle active/inactive status.
     */
    public function toggleStatus(User $user)
    {
        if (auth()->id() === $user->id) {
            return back()->with('error', '⚠️ আপনি নিজের অ্যাকাউন্টের স্ট্যাটাস পরিবর্তন করতে পারবেন না!');
        }

        $user->is_active = !$user->is_active;
        $user->save();

        $statusText = $user->is_active ? 'সক্রিয় (Active)' : 'নিষ্ক্রিয় (Inactive)';
        return back()->with('success', "ইউজার '{$user->name}' সফলভাবে {$statusText} করা হয়েছে।");
    }
}
