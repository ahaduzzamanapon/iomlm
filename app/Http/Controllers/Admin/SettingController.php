<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

use App\Models\Setting;
use Illuminate\Http\Request;


class SettingController extends Controller
{
    public function index()
    {
        $settings = Setting::all()->keyBy('key');
        return view('admin.settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        // Explicitly handle weekend_days (allow empty string for no weekends)
        if ($request->has('weekend_days')) {
            $inputs['weekend_days'] = trim((string)$request->input('weekend_days', ''));
        }

        foreach ($inputs as $key => $val) {
            // Skip zoom_client_secret if blank — keep existing
            if ($key === 'zoom_client_secret' && empty($val)) {
                continue;
            }

            Setting::updateOrCreate(
                ['key' => $key],
                ['value' => is_array($val) ? json_encode($val) : (string)$val]
            );
        }

        // Handle unchecked boolean checkboxes
        if (!$request->has('min_attendance_required')) {
            Setting::updateOrCreate(['key' => 'min_attendance_required'], ['value' => '0']);
        }

        return back()->with('success', 'System settings saved successfully.');
    }
}
