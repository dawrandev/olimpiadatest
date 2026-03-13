<?php

namespace App\Services\Admin;

use App\Repositories\Admin\SubjectRepository;
use Illuminate\Http\Request;

class SubjectService
{
    public function __construct(protected SubjectRepository $subjectRepository)
    {
        //
    }

    public function getAllSubjects(Request $request)
    {
        $perPage = $request->get('per_page', 10);
        $search  = $request->get('search');

        return $this->subjectRepository->getAll($perPage, $search);
    }

    public function createSubject($data)
    {
        return $this->subjectRepository->create($data);
    }

    public function updateSubject($data, string $id)
    {
        return $this->subjectRepository->update($data, $id);
    }
}
