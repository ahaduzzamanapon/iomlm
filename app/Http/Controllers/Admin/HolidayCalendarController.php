<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HolidayCalendar;
use Illuminate\Http\Request;

class HolidayCalendarController extends Controller
{
    public function index()
    {
        $holidays = HolidayCalendar::orderBy('date')->get();
        return view('admin.holiday_calendar.index', compact('holidays'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'date'  => 'required|date',
            'name'  => 'required|string|max:200',
            'scope' => 'required|in:GLOBAL,INSTITUTE,DEPARTMENT',
        ]);

        HolidayCalendar::create([
            'date'                 => $validated['date'],
            'name'                 => $validated['name'],
            'scope'                => $validated['scope'],
            'is_recurring_yearly'  => $request->boolean('is_recurring_yearly', true),
        ]);

        return back()->with('success', 'Holiday added to academic calendar.');
    }

    public function destroy(HolidayCalendar $holidayCalendar)
    {
        $holidayCalendar->delete();
        return back()->with('success', 'Holiday deleted.');
    }
}
