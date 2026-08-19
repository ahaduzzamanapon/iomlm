<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\StudentDocument;
use Illuminate\Http\Request;

class DocumentController extends Controller
{
    public function index()
    {
        $student = Student::where('user_id', auth()->id())->first();
        $documents = StudentDocument::where('student_id', $student?->id)->latest()->get();
        return view('student.documents.index', compact('documents'));
    }

    public function generate(Request $request, $type)
    {
        $student = Student::where('user_id', auth()->id())->first();

        $doc = StudentDocument::create([
            'student_id'      => $student->id,
            'type'            => strtoupper($type),
            'document_number' => 'DOC-' . strtoupper($type) . '-' . date('Ymd') . '-' . rand(100, 999),
            'generated_at'    => now(),
        ]);

        return back()->with('success', "Official " . ucfirst(strtolower($type)) . " generated successfully! Document #: {$doc->document_number}");
    }
}
