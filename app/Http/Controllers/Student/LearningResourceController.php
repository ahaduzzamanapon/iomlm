<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\LearningResource;
use Illuminate\Http\Request;

class LearningResourceController extends Controller
{
    public function index()
    {
        $resources = LearningResource::with('module.subject')->latest()->get();
        return view('student.resources.index', compact('resources'));
    }
}
