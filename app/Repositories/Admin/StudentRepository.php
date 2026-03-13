<?php

namespace App\Repositories\Admin;

use App\Models\Faculty;
use App\Models\Group;
use App\Models\Student;
use App\Models\TestResult;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class StudentRepository
{
    public function __construct(protected Student $model) {}

    public function getFilteredStudents(array $filters)
    {
        $query = Student::with(['group.faculty.translations']);

        if (!empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('full_name', 'like', "%{$filters['search']}%")
                    ->orWhere('student_id', 'like', "%{$filters['search']}%")
                    ->orWhere('jshshir', 'like', "%{$filters['search']}%");
            });
        }

        if (!empty($filters['faculty_id'])) {
            $query->whereHas('group.faculty', function ($q) use ($filters) {
                $q->where('id', $filters['faculty_id']);
            });
        }

        if (!empty($filters['group_id'])) {
            $query->where('group_id', $filters['group_id']);
        }

        $perPage = $filters['per_page'] ?? 10;

        return $query->paginate($perPage)->withQueryString();
    }

    public function create($data)
    {
        $user = User::create([
            'role' => 'student',
            'login' => $data['login'],
            'password' => Hash::make($data['password']),
        ]);

        $student = Student::create([
            'user_id' => $user->id,
            'group_id' => $data['group_id'],
            'student_id' => $data['student_id'],
            'full_name' => $data['full_name'],
            'passport' => $data['passport'],
            'jshshir' => $data['jshshir'],
            'course' => $data['course'],
        ]);

        return $student;
    }

    public function update($data, int $id)
    {
        $student = Student::findOrFail($id);

        $userUpdateData = [];
        if (!empty($data['login'])) {
            $userUpdateData['login'] = $data['login'];
        }
        if (!empty($data['password'])) {
            $userUpdateData['password'] = bcrypt($data['password']);
        }

        if (!empty($userUpdateData)) {
            $student->user->update($userUpdateData);
        }

        $studentUpdateData = [];
        if (!empty($data['full_name'])) {
            $studentUpdateData['full_name'] = $data['full_name'];
        }

        if (!empty($data['group_id'])) {
            $studentUpdateData['group_id'] = $data['group_id'];
        }

        if (!empty($studentUpdateData)) {
            $student->update($studentUpdateData);
        }

        return $student;
    }

    public function findWithRelations($id)
    {
        return Student::with(['user', 'group.faculty'])->findOrFail($id);
    }

    public function getAllResultsByStudent($studentId)
    {
        return TestResult::where('student_id', $studentId)->get();
    }

    public function getPaginatedResultsByStudent($studentId, $perPage = 10)
    {
        return TestResult::where('student_id', $studentId)
            ->with(['testAssignment.subject.translations', 'testAssignment.language'])
            ->orderBy('completed_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Get all existing logins (for Excel upload validation)
     */
    public function getAllLogins(): array
    {
        return User::where('role', 'student')->pluck('login')->toArray();
    }

    /**
     * Get all existing JSSHIRs (for Excel upload validation)
     */
    public function getAllJshshirs(): array
    {
        return Student::pluck('jshshir')->filter()->toArray();
    }

    /**
     * Check if JSHSHIR exists
     */
    public function jshshirExists(string $jshshir): bool
    {
        return Student::where('jshshir', $jshshir)->exists();
    }

    /**
     * Check if login exists (for Excel upload validation)
     */
    public function loginExists(string $login): bool
    {
        return User::where('role', 'student')->where('login', $login)->exists();
    }

    /**
     * Find or create faculty by name
     */
    public function findOrCreateFaculty(string $name): Faculty
    {
        $faculty = Faculty::where('name', $name)->first();

        if (!$faculty) {
            $faculty = Faculty::create(['name' => $name]);
        }

        return $faculty;
    }

    /**
     * Find or create group by name within a faculty
     */
    public function findOrCreateGroup(string $name, int $facultyId): Group
    {
        $group = Group::where('name', $name)
            ->where('faculty_id', $facultyId)
            ->first();

        if (!$group) {
            $group = Group::create([
                'name' => $name,
                'faculty_id' => $facultyId,
            ]);
        }

        return $group;
    }

    /**
     * Get students by group
     */
    public function getByGroupId(int $groupId)
    {
        return $this->model->where('group_id', $groupId)->get();
    }

    /**
     * Get students by faculty
     */
    public function getByFacultyId(int $facultyId)
    {
        return $this->model->whereHas('group', function ($q) use ($facultyId) {
            $q->where('faculty_id', $facultyId);
        })->get();
    }

    /**
     * Bulk create students (for Excel upload - optimized version)
     */
    public function bulkCreateStudents(array $studentsData): array
    {
        $createdStudents = [];

        foreach ($studentsData as $data) {
            $createdStudents[] = $this->create($data);
        }

        return $createdStudents;
    }
}
