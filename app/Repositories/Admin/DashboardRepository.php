<?php

namespace App\Repositories\Admin;

use App\Models\Faculty;
use App\Models\Group;
use App\Models\User;
use App\Models\Student;
use App\Models\Question;
use App\Models\Subject;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardRepository
{
    public function getFacultiesCount(): int
    {
        return Faculty::count();
    }

    /**
     * Get total groups count
     */
    public function getGroupsCount(): int
    {
        return Group::count();
    }

    /**
     * Get total students count
     */
    public function getStudentsCount(): int
    {
        return Student::count();
    }

    /**
     * Get total subjects count
     */
    public function getSubjectsCount(): int
    {
        return Subject::count();
    }

    /**
     * Get faculties with groups count
     */
    public function getFacultiesWithGroupsCount()
    {
        $currentLocale = app()->getLocale();

        return Faculty::with(['translations' => function ($query) use ($currentLocale) {
            $query->whereHas('language', function ($q) use ($currentLocale) {
                $q->where('code', $currentLocale);
            });
        }])
            ->withCount('groups')
            ->orderBy('groups_count', 'desc')
            ->get()
            ->map(function ($faculty) {
                return [
                    'name' => optional($faculty->translations->first())->name,
                    'groups_count' => $faculty->groups_count,
                ];
            });
    }

    /**
     * Get faculties with students count
     */
    public function getFacultiesWithStudentsCount()
    {
        $currentLocale = app()->getLocale();
        return Faculty::with(['translations' => function ($query) use ($currentLocale) {
            $query->whereHas('language', function ($q) use ($currentLocale) {
                $q->where('code', $currentLocale);
            });
        }])
            ->withCount('students')
            ->orderBy('students_count', 'desc')
            ->get()
            ->map(function ($faculty) {
                return [
                    'name' => optional($faculty->translations->first())->name,
                    'students_count' => $faculty->students_count
                ];
            });
    }

    /**
     * Get subjects with topics count (Top 10)
     */
    public function getGroupsWithStudentsCount(int $limit = 10)
    {
        $currentLocale = app()->getLocale();
        return Group::withCount('students')
            ->orderBy('students_count', 'desc')
            ->get()
            ->map(function ($group) {
                return [
                    'name' => $group->name,
                    'students_count' => $group->students_count
                ];
            });
    }

    /**
     * Get subjects with questions count (Top 10)
     */
    public function getSubjectsWithQuestionsCount(int $limit = 10)
    {
        $currentLocale = app()->getLocale();

        return Subject::with(['translations' => function ($query) use ($currentLocale) {
            $query->whereHas('language', function ($q) use ($currentLocale) {
                $q->where('code', $currentLocale);
            });
        }])
            ->withCount('questions')
            ->orderBy('questions_count', 'desc')
            ->get()
            ->map(function ($subject) {
                return [
                    'name' => optional($subject->translations->first())->name,
                    'questions_count' => $subject->questions_count
                ];
            });
    }

    /**
     * Get all dashboard statistics at once
     */
    public function getDashboardStatistics(): array
    {
        return [
            'faculties_count' => $this->getFacultiesCount(),
            'groups_count' => $this->getGroupsCount(),
            'students_count' => $this->getStudentsCount(),
            'subjects_count' => $this->getSubjectsCount(),
        ];
    }

    /**
     * Get all chart data at once
     */
    public function getChartData(): array
    {
        return [
            'faculties_with_groups' => $this->getFacultiesWithGroupsCount(),
            'faculties_with_students' => $this->getFacultiesWithStudentsCount(),
            'groups_with_students' => $this->getGroupsWithStudentsCount(),
            'subjects_with_questions' => $this->getSubjectsWithQuestionsCount(),
        ];
    }
}
