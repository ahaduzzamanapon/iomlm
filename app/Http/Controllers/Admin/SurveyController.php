<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Survey;
use App\Models\SurveyField;
use App\Models\SurveyResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class SurveyController extends Controller
{
    /**
     * Display a listing of surveys.
     */
    public function index(Request $request)
    {
        $query = Survey::withCount(['fields', 'responses'])->latest();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
        }

        $surveys = $query->paginate(15)->withQueryString();

        return view('admin.surveys.index', compact('surveys'));
    }

    /**
     * Store a newly created survey in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'                     => 'required|string|max:255',
            'description'               => 'nullable|string',
            'banner'                    => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:3072',
            'allow_multiple_responses' => 'nullable|boolean',
        ]);

        $bannerPath = null;
        if ($request->hasFile('banner')) {
            $bannerPath = $request->file('banner')->store('surveys/banners', 'public');
        }

        $survey = Survey::create([
            'title'                     => $validated['title'],
            'slug'                      => Survey::generateUniqueSlug($validated['title']),
            'description'               => $validated['description'] ?? null,
            'banner_image'              => $bannerPath ? Storage::url($bannerPath) : null,
            'allow_multiple_responses' => $request->has('allow_multiple_responses'),
            'created_by'                => auth()->id(),
            'is_active'                 => true,
        ]);

        return redirect()->route('admin.surveys.builder', $survey)
            ->with('success', 'Survey created successfully! Now build your form questions.');
    }

    /**
     * Show the visual form builder UI for a survey.
     */
    public function builder(Survey $survey)
    {
        $survey->load('fields');
        return view('admin.surveys.builder', compact('survey'));
    }

    /**
     * Save dynamic questions & fields built in the visual form builder.
     */
    public function saveBuilder(Request $request, Survey $survey)
    {
        $validated = $request->validate([
            'title'                     => 'required|string|max:255',
            'description'               => 'nullable|string',
            'is_active'                 => 'nullable|boolean',
            'allow_multiple_responses' => 'nullable|boolean',
            'fields'                    => 'nullable|array',
            'fields.*.id'               => 'nullable|integer',
            'fields.*.label'            => 'required|string|max:255',
            'fields.*.field_type'       => 'required|string|in:text,textarea,number,select,radio,checkbox,date,file,image',
            'fields.*.options'          => 'nullable|array',
            'fields.*.help_text'        => 'nullable|string|max:255',
            'fields.*.is_required'      => 'nullable',
            'fields.*.sort_order'       => 'nullable|integer',
        ]);

        // Update survey details
        $survey->update([
            'title'                     => $validated['title'],
            'description'               => $validated['description'] ?? null,
            'is_active'                 => $request->has('is_active'),
            'allow_multiple_responses' => $request->has('allow_multiple_responses'),
        ]);

        $incomingFields = $validated['fields'] ?? [];
        $keptFieldIds = [];

        foreach ($incomingFields as $index => $fieldData) {
            $options = null;
            if (!empty($fieldData['options']) && is_array($fieldData['options'])) {
                // Filter empty option strings
                $options = array_values(array_filter(array_map('trim', $fieldData['options'])));
            }

            $isRequired = !empty($fieldData['is_required']);
            $sortOrder  = isset($fieldData['sort_order']) ? (int)$fieldData['sort_order'] : $index;

            if (!empty($fieldData['id'])) {
                $field = SurveyField::where('survey_id', $survey->id)->find($fieldData['id']);
                if ($field) {
                    $field->update([
                        'label'       => $fieldData['label'],
                        'field_type'  => $fieldData['field_type'],
                        'options'     => $options,
                        'help_text'   => $fieldData['help_text'] ?? null,
                        'is_required' => $isRequired,
                        'sort_order'  => $sortOrder,
                    ]);
                    $keptFieldIds[] = $field->id;
                    continue;
                }
            }

            // Create new field
            $newField = SurveyField::create([
                'survey_id'   => $survey->id,
                'label'       => $fieldData['label'],
                'field_type'  => $fieldData['field_type'],
                'options'     => $options,
                'help_text'   => $fieldData['help_text'] ?? null,
                'is_required' => $isRequired,
                'sort_order'  => $sortOrder,
            ]);
            $keptFieldIds[] = $newField->id;
        }

        // Remove deleted fields
        SurveyField::where('survey_id', $survey->id)
            ->whereNotIn('id', $keptFieldIds)
            ->delete();

        return redirect()->route('admin.surveys.builder', $survey)
            ->with('success', 'Survey form structure updated successfully!');
    }

    /**
     * Toggle Active/Closed state of survey.
     */
    public function toggleStatus(Survey $survey)
    {
        $survey->update(['is_active' => !$survey->is_active]);

        $statusText = $survey->is_active ? 'Activated' : 'Closed';

        if (request()->wantsJson() || request()->ajax()) {
            return response()->json([
                'success'     => true,
                'is_active'   => $survey->is_active,
                'status_text' => $statusText,
                'message'     => "Survey has been {$statusText}.",
            ]);
        }

        return redirect()->back()->with('success', "Survey has been {$statusText}.");
    }

    /**
     * View dynamic automated responses table for a survey.
     */
    public function responses(Survey $survey)
    {
        $fields = $survey->fields;
        $responses = $survey->responses()
            ->with(['answers.field', 'user'])
            ->latest()
            ->paginate(20);

        return view('admin.surveys.responses', compact('survey', 'fields', 'responses'));
    }

    /**
     * Export all survey responses to CSV format.
     */
    public function exportCsv(Survey $survey)
    {
        $fields = $survey->fields;
        $responses = $survey->responses()->with('answers')->latest()->get();

        $filename = 'survey_responses_' . $survey->slug . '_' . date('Y-m-d_H-i') . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($survey, $fields, $responses) {
            $file = fopen('php://output', 'w');

            // UTF-8 BOM for Excel compatibility
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

            // Generate CSV Header Columns dynamically based on fields
            $columns = ['Response ID', 'Respondent Name', 'Respondent Email', 'IP Address', 'Date & Time'];
            foreach ($fields as $field) {
                $columns[] = $field->label;
            }
            fputcsv($file, $columns);

            // Generate Data Rows
            foreach ($responses as $resp) {
                $row = [
                    '#' . $resp->id,
                    $resp->respondent_name ?? 'Anonymous',
                    $resp->respondent_email ?? '—',
                    $resp->ip_address ?? '—',
                    $resp->created_at ? $resp->created_at->format('Y-m-d H:i:s') : '—',
                ];

                $answersMap = $resp->answers->keyBy('survey_field_id');

                foreach ($fields as $field) {
                    $ansObj = $answersMap->get($field->id);
                    $row[]  = $ansObj ? $ansObj->formatted_answer : '—';
                }

                fputcsv($file, $row);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Delete survey form.
     */
    public function destroy(Survey $survey)
    {
        $survey->delete();
        return redirect()->route('admin.surveys.index')->with('success', 'Survey form deleted successfully.');
    }
}
