<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SupportDepartment;
use App\Models\User;
use Illuminate\Http\Request;

class SupportUserController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'           => 'required|string|max:150',
            'email'          => 'required|email|unique:users,email',
            'password'       => 'required|string|min:6',
            'departments'    => 'nullable|array',
            'departments.*'  => 'exists:support_departments,id',
        ]);

        $user = User::create([
            'name'     => $validated['name'],
            'email'    => $validated['email'],
            'password' => bcrypt($validated['password']),
            'role'     => 'support_agent',
        ]);

        if (!empty($validated['departments'])) {
            $user->supportDepartments()->sync($validated['departments']);
        }

        return back()->with('success', "Support user '{$user->name}' created successfully.");
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name'          => 'required|string|max:150',
            'email'         => 'required|email|unique:users,email,' . $user->id,
            'password'      => 'nullable|string|min:6',
            'departments'   => 'nullable|array',
            'departments.*' => 'exists:support_departments,id',
        ]);

        $userData = [
            'name'  => $validated['name'],
            'email' => $validated['email'],
        ];

        if (!empty($validated['password'])) {
            $userData['password'] = bcrypt($validated['password']);
        }

        $user->update($userData);

        if (isset($validated['departments'])) {
            $user->supportDepartments()->sync($validated['departments']);
        } else {
            $user->supportDepartments()->detach();
        }

        return back()->with('success', "Support user '{$user->name}' updated successfully.");
    }

    public function destroy(User $user)
    {
        $user->supportDepartments()->detach();
        $user->delete();
        return back()->with('success', 'Support user deleted.');
    }
}
