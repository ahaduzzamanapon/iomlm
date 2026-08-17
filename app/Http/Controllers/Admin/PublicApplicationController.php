<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PublicApplication;
use Illuminate\Http\Request;

class PublicApplicationController extends Controller
{
    public function index(Request $request)
    {
        $query = PublicApplication::with(['course', 'session'])
            ->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('search')) {
            $q = $request->search;
            $query->where(function ($qb) use ($q) {
                $qb->where('application_no', 'like', "%{$q}%")
                   ->orWhere('applicant_name', 'like', "%{$q}%")
                   ->orWhere('phone', 'like', "%{$q}%");
            });
        }

        $applications = $query->paginate(20)->withQueryString();

        return view('admin.public_applications.index', compact('applications'));
    }

    public function show(PublicApplication $publicApplication)
    {
        $publicApplication->load(['course', 'session', 'bloodGroup', 'religion',
            'presentDistrict.division', 'permanentDistrict.division']);

        return view('admin.public_applications.show', compact('publicApplication'));
    }

    public function updateStatus(Request $request, PublicApplication $publicApplication)
    {
        $r = $request->validate([
            'status'      => 'required|in:PENDING,REVIEWED,APPROVED,REJECTED',
            'admin_notes' => 'nullable|string',
        ]);
        $publicApplication->update($r);
        return back()->with('success', "Application {$publicApplication->application_no} status updated to {$r['status']}.");
    }
}
