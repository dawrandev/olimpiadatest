<?php

namespace App\Http\Controllers;

use App\Models\Faculty;
use App\Models\Question;
use App\Models\Subject;
use App\Models\Topic;
use Illuminate\Http\Request;

class AjaxController extends Controller
{
    public function getFacultiesByLanguage($languageId)
    {
        // Fakultetlarni faqat name bilan qaytarish (tarjimasiz)
        $faculties = Faculty::select('id', 'name')->get();
        return response()->json($faculties);
    }

    public function getGroupsByFaculty($facultyId)
    {
        $groups = Faculty::find($facultyId)->groups;
        return response()->json($groups);
    }

    public function getSubjectsByLanguage($languageId)
    {
        $subjects = Subject::with(['translations' => function ($q) use ($languageId) {
            $q->where('language_id', $languageId);
        }])->get();

        return response()->json($subjects);
    }

    public function getTopicsBySubjectAndLanguage(Request $request)
    {
        $subjectId = $request->subject;
        $languageId = $request->language;

        $topics = Topic::where('subject_id', $subjectId)
            ->with(['translations' => function ($q) use ($languageId) {
                $q->where('language_id', $languageId);
            }])->get();

        return response()->json($topics);
    }

    public function getAvailableQuestions(Request $request)
    {
        $count = Question::where('subject_id', $request->subject_id)
            ->where('language_id', $request->language_id)
            ->count();

        return response()->json(['count' => $count]);
    }
}
