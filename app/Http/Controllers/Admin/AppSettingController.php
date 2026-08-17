<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AppSetting;
use App\Models\BloodGroup;
use App\Models\District;
use App\Models\Division;
use App\Models\Religion;
use Illuminate\Http\Request;

class AppSettingController extends Controller
{
    public function index()
    {
        $bloodGroups = BloodGroup::orderBy('sort_order')->get();
        $religions   = Religion::orderBy('sort_order')->get();
        $divisions   = Division::with('districts')->orderBy('name')->get();
        $settings    = AppSetting::orderBy('group')->orderBy('sort_order')->get()->groupBy('group');

        return view('admin.app_settings.index', compact('bloodGroups', 'religions', 'divisions', 'settings'));
    }

    // ── Blood Groups ──────────────────────────────────────
    public function storeBloodGroup(Request $request)
    {
        $r = $request->validate(['name' => 'required|string|max:10|unique:blood_groups,name']);
        $max = BloodGroup::max('sort_order') ?? 0;
        BloodGroup::create(['name' => $r['name'], 'sort_order' => $max + 1]);
        return back()->with('success', 'Blood group added.');
    }

    public function destroyBloodGroup(BloodGroup $bloodGroup)
    {
        $bloodGroup->delete();
        return back()->with('success', 'Blood group removed.');
    }

    // ── Religions ─────────────────────────────────────────
    public function storeReligion(Request $request)
    {
        $r = $request->validate(['name' => 'required|string|max:100|unique:religions,name']);
        $max = Religion::max('sort_order') ?? 0;
        Religion::create(['name' => $r['name'], 'sort_order' => $max + 1]);
        return back()->with('success', 'Religion added.');
    }

    public function destroyReligion(Religion $religion)
    {
        $religion->delete();
        return back()->with('success', 'Religion removed.');
    }

    // ── Divisions ─────────────────────────────────────────
    public function storeDivision(Request $request)
    {
        $r = $request->validate(['name' => 'required|string|max:100|unique:divisions,name']);
        Division::create(['name' => $r['name']]);
        return back()->with('success', 'Division added.');
    }

    public function destroyDivision(Division $division)
    {
        $division->delete();
        return back()->with('success', 'Division removed.');
    }

    // ── Districts ─────────────────────────────────────────
    public function storeDistrict(Request $request)
    {
        $r = $request->validate([
            'division_id' => 'required|exists:divisions,id',
            'name'        => 'required|string|max:100',
        ]);
        District::create($r);
        return back()->with('success', 'District added.');
    }

    public function destroyDistrict(District $district)
    {
        $district->delete();
        return back()->with('success', 'District removed.');
    }

    // ── Global Settings ───────────────────────────────────
    public function updateSettings(Request $request)
    {
        $settings = AppSetting::all();
        foreach ($settings as $setting) {
            if ($request->has($setting->key)) {
                $val = $setting->type === 'boolean'
                    ? ($request->boolean($setting->key) ? '1' : '0')
                    : $request->input($setting->key);
                $setting->update(['value' => $val]);
            }
        }
        return back()->with('success', 'Settings saved successfully.');
    }
}
