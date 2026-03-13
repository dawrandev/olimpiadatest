<?php

namespace App\Repositories\Admin;

use App\Models\Group;

class GroupRepository
{
    public function create($data)
    {
        return Group::create($data);
    }

    public function update($data, int $id)
    {
        $group = Group::findOrFail($id);
        $group->update($data);
        return $group;
    }

    public function getAllWithFaculty($facultyId = null, $perPage = 10)
    {
        $query = Group::with(['faculty.translations']);

        if ($facultyId) {
            $query->where('faculty_id', $facultyId);
        }

        return $query->paginate($perPage)->withQueryString();
    }
}
