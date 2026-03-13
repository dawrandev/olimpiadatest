<?php

namespace App\Services\Admin;

use App\Models\TestSession;
use App\Repositories\Admin\DashboardRepository;
use Carbon\Carbon;

class DashboardService
{
    protected $dashboardRepository;

    public function __construct(DashboardRepository $dashboardRepository)
    {
        $this->dashboardRepository = $dashboardRepository;
    }

    /**
     * Get dashboard overview statistics
     */
    public function getDashboardOverview(): array
    {
        $statistics = $this->dashboardRepository->getDashboardStatistics();
        $chartData = $this->dashboardRepository->getChartData();

        return [
            'faculties_count' => $statistics['faculties_count'],
            'groups_count' => $statistics['groups_count'],
            'students_count' => $statistics['students_count'],
            'subjects_count' => $statistics['subjects_count'],
            'facultiesWithGroups' => $chartData['faculties_with_groups'],
            'facultiesWithStudents' => $chartData['faculties_with_students'],
            'groupsWithStudents' => $chartData['groups_with_students'],
            'subjectsWithQuestions' => $chartData['subjects_with_questions'],
        ];
    }

    /**
     * Get formatted dashboard data for view
     */
    public function getFormattedDashboardData(): array
    {
        $overview = $this->getDashboardOverview();

        return [
            'statistics' => [
                [
                    'title' => 'Faculties',
                    'value' => $overview['faculties_count'],
                    'subtitle' => 'Total faculties',
                    'icon' => 'briefcase',
                    'color' => 'primary'
                ],
                [
                    'title' => 'Groups',
                    'value' => $overview['groups_count'],
                    'subtitle' => 'Total groups',
                    'icon' => 'users',
                    'color' => 'success'
                ],
                [
                    'title' => 'Students',
                    'value' => $overview['students_count'],
                    'subtitle' => 'Total students',
                    'icon' => 'user',
                    'color' => 'info'
                ],
                [
                    'title' => 'Subjects',
                    'value' => $overview['subjects_count'],
                    'subtitle' => 'Total subjects',
                    'icon' => 'book',
                    'color' => 'warning'
                ],
            ],
            'charts' => [
                'facultiesWithGroups' => $overview['facultiesWithGroups'],
                'facultiesWithStudents' => $overview['facultiesWithStudents'],
                'subjectsWithTopics' => $overview['subjectsWithTopics'],
                'subjectsWithQuestions' => $overview['subjectsWithQuestions'],
            ]
        ];
    }
}
