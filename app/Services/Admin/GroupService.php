<?php

namespace App\Services\Admin;

use App\Repositories\Admin\GroupRepository;
use Illuminate\Support\Facades\Log;

class GroupService
{
    public function __construct(protected GroupRepository $groupRepository)
    {
        // 
    }

    public function createGroup($data)
    {
        return $this->groupRepository->create($data);
    }

    public function updateGroup($data, int $id)
    {
        return $this->groupRepository->update($data, $id);
    }

    public function getGroups($facultyId = null, $perPage = 10)
    {
        try {
            return $this->groupRepository->getAllWithFaculty($facultyId, $perPage);
        } catch (\Exception $e) {
            throw new \Exception(__('Unable to load groups at this time.'));
        }
    }
}
