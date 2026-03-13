<?php

namespace App\Repositories;

use App\Models\Question;
use App\Models\StudentAnswer;
use App\Models\TestAssignment;
use App\Models\TestResult;
use App\Models\Topic;
use Illuminate\Support\Collection;

class StudentTestRepository
{
    public function findTestAssignment(int $id): TestAssignment
    {
        return TestAssignment::findOrFail($id);
    }

    public function findTestAssignmentWithLanguage(int $id): TestAssignment
    {
        return TestAssignment::with('language')->findOrFail($id);
    }

    public function getAvailableTests(int $groupId): Collection
    {
        $tests = TestAssignment::where('group_id', $groupId)
            ->where('is_active', true)
            ->where('start_time', '<=', now())
            ->where('end_time', '>=', now())
            ->with(['subject', 'language'])
            ->orderBy('created_at', 'desc')
            ->get();

        // Agar test original bo'lsa va uning active retake'si mavjud bo'lsa - yashir
        return $tests->filter(function ($test) {
            // Retake testni doim ko'rsat
            if ($test->is_retake) {
                return true;
            }

            // Original test: agar active retake mavjud bo'lsa - yashir
            $hasActiveRetake = TestAssignment::where('parent_assignment_id', $test->id)
                ->where('is_active', true)
                ->where('start_time', '<=', now())
                ->where('end_time', '>=', now())
                ->exists();

            return !$hasActiveRetake;
        });
    }

    public function findTestResult(int $testAssignmentId, int $studentId): ?TestResult
    {
        return TestResult::where('test_assignment_id', $testAssignmentId)
            ->where('student_id', $studentId)
            ->first();
    }

    public function findInProgressTestResult(int $testAssignmentId, int $studentId): TestResult
    {
        return TestResult::where('test_assignment_id', $testAssignmentId)
            ->where('student_id', $studentId)
            ->where('status', 'in_progress')
            ->firstOrFail();
    }

    public function findCompletedTestResult(int $testAssignmentId, int $studentId): TestResult
    {
        return TestResult::where('test_assignment_id', $testAssignmentId)
            ->where('student_id', $studentId)
            ->where('status', 'completed')
            ->with(['studentAnswers.question', 'studentAnswers.answer'])
            ->firstOrFail();
    }

    public function updateTestResultStatus(TestResult $testResult, array $data): void
    {
        $testResult->update($data);
    }

    public function getTopicsWithQuestions(int $subjectId): Collection
    {
        return Topic::where('subject_id', $subjectId)
            ->has('questions')
            ->get();
    }

    public function getRandomQuestionsByTopic(int $subjectId, int $languageId, int $topicId, int $count): Collection
    {
        return Question::where('subject_id', $subjectId)
            ->where('language_id', $languageId)
            ->where('topic_id', $topicId)
            ->whereHas('answers', function ($query) use ($languageId) {
                $query->where('language_id', $languageId);
            })
            ->inRandomOrder()
            ->limit($count)
            ->get();
    }

    public function getAdditionalRandomQuestions(int $subjectId, int $languageId, array $excludeIds, int $count): Collection
    {
        return Question::where('subject_id', $subjectId)
            ->where('language_id', $languageId)
            ->whereNotIn('id', $excludeIds)
            ->whereHas('answers', function ($query) use ($languageId) {
                $query->where('language_id', $languageId);
            })
            ->inRandomOrder()
            ->limit($count)
            ->get();
    }

    public function createStudentAnswer(array $data): StudentAnswer
    {
        return StudentAnswer::create($data);
    }

    public function getStudentAnswersWithRelations(int $testResultId, int $languageId): Collection
    {
        return StudentAnswer::where('test_result_id', $testResultId)
            ->with([
                'question' => function ($query) use ($languageId) {
                    $query->where('language_id', $languageId);
                },
                'question.answers' => function ($query) use ($languageId) {
                    $query->where('language_id', $languageId)
                        ->orderBy('id', 'asc');
                },
                'question.matchingPairs' => function ($query) {
                    $query->orderBy('order', 'asc');
                }
            ])
            ->orderBy('order', 'asc')
            ->get();
    }

    public function getStudentAnswers(int $testResultId): Collection
    {
        return StudentAnswer::where('test_result_id', $testResultId)->get();
    }

    public function findStudentAnswer(int $id): StudentAnswer
    {
        return StudentAnswer::with('question.answers')->findOrFail($id);
    }

    public function updateStudentAnswer(StudentAnswer $studentAnswer, array $data): void
    {
        $studentAnswer->update($data);
    }

    public function updateTestResult(TestResult $testResult, array $data): void
    {
        $testResult->update($data);
    }
}
