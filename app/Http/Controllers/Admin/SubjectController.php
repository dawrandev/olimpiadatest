<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SubjectStoreRequest;
use App\Http\Requests\Admin\SubjectUpdateRequest;
use App\Models\Subject;
use App\Services\Admin\SubjectService;
use Illuminate\Http\Request;
use Illuminate\View\ViewFinderInterface;

class SubjectController extends Controller
{
    public function __construct(protected SubjectService $subjectService)
    {
        // 
    }

    public function index(Request $request)
    {
        $subjects = $this->subjectService->getAllSubjects($request);

        if ($request->ajax()) {
            return view('partials.admin.subjects.subjects_table', compact('subjects'));
        }

        return view('pages.admin.subjects.index', compact('subjects'));
    }

    public function create()
    {
        return view('pages.admin.subjects.create');
    }

    public function store(SubjectStoreRequest $request)
    {
        $this->subjectService->createSubject($request->validated());

        return redirect()->route('admin.subjects.index')->with('success', __('Subject created successfully'));
    }

    public function show(string $id)
    {
        //
    }

    public function edit(string $id)
    {
        $subject = Subject::with('translations')->findOrFail($id);

        return view('pages.admin.subjects.edit', compact('subject'));
    }

    public function update(SubjectUpdateRequest $request, string $id)
    {
        $this->subjectService->updateSubject($request->validated(), $id);

        return redirect()->route('admin.subjects.index')->with('success', __('Subject updated successfully'));
    }

    public function destroy(string $id)
    {
        $subject = Subject::findorFail($id);

        $subject->delete();

        return redirect()->route('admin.subjects.index')->with('success', __('Subject deleted successfully'));
    }
}
