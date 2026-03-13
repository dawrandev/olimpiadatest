<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\GroupStoreRequest;
use App\Http\Requests\GroupUpdateRequest;
use App\Models\Faculty;
use App\Models\Group;
use App\Services\Admin\GroupService;
use Illuminate\Http\Request;

class GroupController extends Controller
{
    public function __construct(protected GroupService $groupService)
    {
        //
    }

    public function index(Request $request, $facultyId = null)
    {
        $facultyId = $facultyId ?? $request->get('faculty_id');

        $faculty = $facultyId ? Faculty::findOrFail($facultyId) : null;
        $groups = $this->groupService->getGroups($facultyId, 10);

        return view('pages.admin.groups.index', compact('groups', 'faculty'));
    }

    public function create()
    {
        $groups = Group::with('faculty')->paginate(10);

        return view('pages.admin.groups.create', compact('groups'));
    }

    public function store(GroupStoreRequest $request)
    {
        $this->groupService->createGroup($request->validated());

        return redirect()->route('admin.groups.index')->with('success', __('Group created successfully'));
    }

    public function edit(string $id)
    {
        $group = Group::with('faculty.translations')->findOrFail($id);

        return view('pages.admin.groups.edit', compact('group'));
    }

    public function update(GroupUpdateRequest $request, string $id)
    {
        $this->groupService->updateGroup($request->validated(), $id);

        return redirect()->route('admin.groups.index')->with('success', __('Group updated successfully'));
    }

    public function destroy(string $id)
    {
        $group = Group::findOrFail($id);

        $group->delete();

        return redirect()->route('admin.groups.index')->with('success', __('Group deleted successfully'));
    }
}
