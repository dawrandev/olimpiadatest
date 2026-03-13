<?php

namespace App\Repositories\Admin;

use App\Models\TestAssignment;
use App\Models\TestResult;
use App\Models\StudentAnswer;
use App\Models\Question;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Collection;

class TestAssignmentRepository
{
    public function getFilteredAssignments($filters)
    {
        $query = TestAssignment::query()
            ->with(['group.faculty', 'subject', 'language']);

        if (isset($filters['faculty_id'])) {
            $query->where('faculty_id', $filters['faculty_id']);
        }

        if (isset($filters['group_id'])) {
            $query->where('group_id', $filters['group_id']);
        }

        if (isset($filters['subject_id'])) {
            $query->where('subject_id', $filters['subject_id']);
        }

        if (isset($filters['status'])) {
            $query->where('is_active', $filters['status'] === 'active');
        }

        if (isset($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        return $query->latest()->paginate(15);
    }

    public function findById($id): TestAssignment
    {
        return TestAssignment::findOrFail($id);
    }

    public function findByIdWithRelations($id): TestAssignment
    {
        return TestAssignment::with([
            'group.faculty',
            'subject',
            'language',
            'testResults.student'
        ])->findOrFail($id);
    }

    public function create(array $data): TestAssignment
    {
        return TestAssignment::create($data);
    }

    public function update(TestAssignment $assignment, array $data): bool
    {
        return $assignment->update($data);
    }

    public function delete($id): bool
    {
        $assignment = $this->findById($id);
        return $assignment->delete();
    }

    public function toggleStatus($id): string
    {
        $assignment = $this->findById($id);
        $assignment->is_active = !$assignment->is_active;
        $assignment->save();

        return $assignment->is_active ? 'activated' : 'deactivated';
    }

    public function checkConflict($groupId, $startTime, $endTime, $excludeId = null): bool
    {
        $query = TestAssignment::where('group_id', $groupId)
            ->where('is_active', true)
            ->where(function ($query) use ($startTime, $endTime) {
                $query->whereBetween('start_time', [$startTime, $endTime])
                    ->orWhereBetween('end_time', [$startTime, $endTime])
                    ->orWhere(function ($q) use ($startTime, $endTime) {
                        $q->where('start_time', '<=', $startTime)
                            ->where('end_time', '>=', $endTime);
                    });
            });

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }

    public function countAvailableQuestions($subjectId, $languageId): int
    {
        return Question::where('subject_id', $subjectId)
            ->where('language_id', $languageId)
            ->count();
    }

    public function getAllGroups()
    {
        return DB::table('groups')->get();
    }

    public function getStudentsByGroupId($groupId)
    {
        return DB::table('students')
            ->where('group_id', $groupId)
            ->get();
    }

    public function createTestResult(array $data)
    {
        return DB::table('test_results')->insert($data);
    }

    public function getTestResults($assignmentId)
    {
        return TestResult::where('test_assignment_id', $assignmentId)
            ->with('student')
            ->orderByDesc('score')
            ->get();
    }

    public function hasStartedTests(TestAssignment $assignment): bool
    {
        return $assignment->testResults()
            ->where('status', '!=', 'not_started')
            ->exists();
    }

    public function updateNotStartedTestResults(TestAssignment $assignment, $questionCount)
    {
        return $assignment->testResults()
            ->where('status', 'not_started')
            ->update(['total_questions' => $questionCount]);
    }

    public function getTestResult($testResultId, $testAssignmentId)
    {
        return TestResult::where('id', $testResultId)
            ->where('test_assignment_id', $testAssignmentId)
            ->with('student.user')
            ->firstOrFail();
    }

    public function getStudentAnswers($testResultId, $languageId): Collection
    {
        return StudentAnswer::where('test_result_id', $testResultId)
            ->with([
                'question' => function ($query) use ($languageId) {
                    $query->where('language_id', $languageId);
                },
                'question.answers' => function ($query) use ($languageId) {
                    $query->where('language_id', $languageId)
                        ->orderBy('order', 'asc');
                },
                'question.matchingPairs' => function ($query) {
                    $query->orderBy('order', 'asc');
                },
                'answer'
            ])
            ->get();
    }

    public function getFailedStudents(TestAssignment $assignment)
    {
        return DB::table('students')
            ->join('test_results', 'students.id', '=', 'test_results.student_id')
            ->where('test_results.test_assignment_id', $assignment->id)
            ->where('test_results.status', 'completed')
            ->whereRaw('(test_results.correct_answers * 100 / test_results.total_questions) < 60')
            ->select('students.*', 'test_results.score', 'test_results.correct_answers', 'test_results.total_questions')
            ->get();
    }

    public function createRetakeAssignment(array $data): TestAssignment
    {
        return TestAssignment::create($data);
    }

    public function createRetakeTestResult(array $data)
    {
        return TestResult::create($data);
    }

    public function updateTestResult(TestResult $testResult, array $data): bool
    {
        return $testResult->update($data);
    }

    public function getStatistics(TestAssignment $assignment): array
    {
        $results = $assignment->testResults;

        return [
            'total_students' => $results->count(),
            'completed' => $results->where('status', 'completed')->count(),
            'in_progress' => $results->where('status', 'in_progress')->count(),
            'not_started' => $results->where('status', 'not_started')->count(),
            'average_score' => $results->where('status', 'completed')->avg('score') ?? 0,
        ];
    }
}
