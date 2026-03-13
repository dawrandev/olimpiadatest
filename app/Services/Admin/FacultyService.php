<?php

namespace App\Services\Admin;

use App\Repositories\Admin\FacultyRepository;

class FacultyService
{
    public function __construct(protected FacultyRepository $facultyRepository)
    {
        // 
    }

    public function getAllFaculties()
    {
        return $this->facultyRepository->getAll();
    }

    public function createFaculty($data)
    {
        return $this->facultyRepository->create($data);
    }

    public function getFaculty($id)
    {
        return $this->facultyRepository->getFaculty($id);
    }

    public function updateFaculty($data, $id)
    {
        return $this->facultyRepository->update($data, $id);
    }
}
