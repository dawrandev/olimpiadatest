<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\FacultyStoreRequest;
use App\Http\Requests\UpdateStoreRequest;
use App\Models\Faculty;
use App\Services\Admin\FacultyService;
use Illuminate\Http\Request;

class FacultyController extends Controller
{
    public function __construct(protected FacultyService $facultyService)
    {
        // 
    }
    public function index()
    {
        $faculties = $this->facultyService->getAllFaculties();

        return view('pages.admin.faculties.index', compact('faculties'));
    }

    public function create()
    {
        return view('pages.admin.faculties.create');
    }

    public function store(FacultyStoreRequest $request)
    {
        $faculties = $this->facultyService->createFaculty($request->validated());

        return redirect()->route('admin.faculties.index')->with('success', __('Faculty created successfully'));
    }

    public function edit(string $id)
    {
        $faculty = Faculty::with('translations')->findOrFail($id);

        return view('pages.admin.faculties.edit', compact('faculty'));
    }

    public function update(UpdateStoreRequest $request, string $id)
    {
        $faculty = $this->facultyService->updateFaculty($request->validated(), $id);

        return redirect()->route('admin.faculties.index')->with('success', __('Faculty updated successfully'));
    }

    public function destroy(string $id)
    {
        $faculty = Faculty::findOrFail($id);

        $faculty->delete();

        return redirect()->route('admin.faculties.index')->with('success', __('Faculty deleted successfully'));
    }
}
