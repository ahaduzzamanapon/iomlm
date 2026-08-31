<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Survey;
use App\Models\SurveyResponse;
use App\Models\SurveyResponseAnswer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SurveyPublicController extends Controller
{
    /**
     * Display the public survey form.
     */
    public function show($slug)
    {
        $survey = Survey::with(['fields' => function($q) {
            $q->orderBy('sort_order', 'asc')->orderBy('id', 'asc');
        }])->where('slug', $slug)->firstOrFail();

        if (!$survey->is_active) {
            return view('public.survey.closed', compact('survey'));
        }

        return view('public.survey.show', compact('survey'));
    }

    /**
     * Process and store public survey form submission.
     */
    public function submit(Request $request, $slug)
    {
        $survey = Survey::with('fields')->where('slug', $slug)->firstOrFail();

        if (!$survey->is_active) {
            return redirect()->back()->with('error', 'This survey form is closed for responses.');
        }

        // Build dynamic validation rules based on survey fields
        $rules = [
            'respondent_name'  => 'nullable|string|max:255',
            'respondent_email' => 'nullable|email|max:255',
        ];

        foreach ($survey->fields as $field) {
            $fieldKey = 'field_' . $field->id;

            if ($field->is_required) {
                if (in_array($field->field_type, ['file', 'image'])) {
                    $rules[$fieldKey] = 'required|file';
                } elseif ($field->field_type === 'checkbox') {
                    $rules[$fieldKey] = 'required|array|min:1';
                } else {
                    $rules[$fieldKey] = 'required';
                }
            } else {
                $rules[$fieldKey] = 'nullable';
            }

            // Type specific validations
            if ($field->field_type === 'number') {
                $rules[$fieldKey] .= '|numeric';
            } elseif ($field->field_type === 'email') {
                $rules[$fieldKey] .= '|email';
            } elseif ($field->field_type === 'date') {
                $rules[$fieldKey] .= '|date';
            } elseif ($field->field_type === 'image') {
                $rules[$fieldKey] .= '|image|mimes:jpeg,png,jpg,gif,webp|max:5120';
            } elseif ($field->field_type === 'file') {
                $rules[$fieldKey] .= '|max:10240';
            }
        }

        $validated = $request->validate($rules);

        // Store Response Header
        $response = SurveyResponse::create([
            'survey_id'        => $survey->id,
            'respondent_name'  => $request->input('respondent_name') ?? (auth()->user()->name ?? null),
            'respondent_email' => $request->input('respondent_email') ?? (auth()->user()->email ?? null),
            'user_id'          => auth()->id(),
            'ip_address'       => $request->ip(),
        ]);

        // Process Answers for each field
        foreach ($survey->fields as $field) {
            $fieldKey = 'field_' . $field->id;
            $val = null;

            if (in_array($field->field_type, ['file', 'image']) && $request->hasFile($fieldKey)) {
                $file = $request->file($fieldKey);
                $path = $file->store('surveys/uploads/' . $survey->id, 'public');
                $val  = Storage::url($path);
            } elseif ($field->field_type === 'checkbox' && is_array($request->input($fieldKey))) {
                $val = json_encode(array_values($request->input($fieldKey)));
            } else {
                $val = $request->input($fieldKey);
            }

            SurveyResponseAnswer::create([
                'survey_response_id' => $response->id,
                'survey_field_id'    => $field->id,
                'answer_value'       => is_array($val) ? json_encode($val) : $val,
            ]);
        }

        return redirect()->route('public.survey.success', $survey->slug)
            ->with('success_message', 'Thank you! Your response has been submitted successfully.');
    }

    /**
     * Submission success page.
     */
    public function success($slug)
    {
        $survey = Survey::where('slug', $slug)->firstOrFail();
        return view('public.survey.success', compact('survey'));
    }
}
