<?php

namespace App\Services\Admin;

use App\Models\TestAssignment;
use App\Models\TestResult;
use App\Repositories\Admin\TestAssignmentRepository;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;

class TestAssignmentService
{
    public function __construct(protected TestAssignmentRepository $repository) {}

    public function getFilteredAssignments(Request $request)
    {
        $filters = $request->only(['faculty_id', 'group_id', 'subject_id', 'status', 'date_from', 'date_to']);
        return $this->repository->getFilteredAssignments($filters);
    }

    public function create(array $validated): TestAssignment
    {
        DB::beginTransaction();
        try {
            $assignment = $this->repository->create($validated);

            $students = $this->repository->getStudentsByGroupId($validated['group_id']);

            foreach ($students as $student) {
                $this->repository->createTestResult([
                    'test_assignment_id' => $assignment->id,
                    'student_id' => $student->id,
                    'total_questions' => $validated['question_count'],
                    'status' => 'not_started',
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }

            DB::commit();
            return $assignment;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function createForAllGroups(array $validated): int
    {
        DB::beginTransaction();
        try {
            $groups = $this->repository->getAllGroups();
            $createdCount = 0;

            foreach ($groups as $group) {
                $assignmentData = array_merge($validated, [
                    'faculty_id' => $group->faculty_id,
                    'group_id' => $group->id,
                ]);

                $assignment = $this->repository->create($assignmentData);

                $students = $this->repository->getStudentsByGroupId($group->id);

                foreach ($students as $student) {
                    $this->repository->createTestResult([
                        'test_assignment_id' => $assignment->id,
                        'student_id' => $student->id,
                        'total_questions' => $validated['question_count'],
                        'status' => 'not_started',
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                }

                $createdCount++;
            }

            DB::commit();
            return $createdCount;
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error creating test assignments for all groups: ' . $e->getMessage());
            throw $e;
        }
    }

    public function update(TestAssignment $assignment, array $validated): bool
    {
        $result = $this->repository->update($assignment, $validated);

        if ($assignment->question_count != $validated['question_count']) {
            $this->repository->updateNotStartedTestResults($assignment, $validated['question_count']);
        }

        return $result;
    }

    public function delete($id): bool
    {
        return $this->repository->delete($id);
    }

    public function toggleStatus($id): string
    {
        return $this->repository->toggleStatus($id);
    }

    public function validateAvailableQuestions($subjectId, $languageId, $questionCount): array
    {
        $availableQuestions = $this->repository->countAvailableQuestions($subjectId, $languageId);

        if ($availableQuestions < $questionCount) {
            return [
                'valid' => false,
                'message' => __('Only :count questions available for this subject and language. Please enter fewer questions.', ['count' => $availableQuestions])
            ];
        }

        return ['valid' => true];
    }

    public function checkTimeConflict($groupId, $startTime, $endTime, $excludeId = null): bool
    {
        return $this->repository->checkConflict($groupId, $startTime, $endTime, $excludeId);
    }

    public function canEdit(TestAssignment $assignment): bool
    {
        if ($assignment->start_time <= now() && $this->repository->hasStartedTests($assignment)) {
            return false;
        }
        return true;
    }

    public function canUpdate(TestAssignment $assignment): bool
    {
        return !$this->repository->hasStartedTests($assignment);
    }

    public function getAssignmentWithStatistics($id): array
    {
        $assignment = $this->repository->findByIdWithRelations($id);
        $statistics = $this->repository->getStatistics($assignment);

        return compact('assignment', 'statistics');
    }

    public function getResults($assignmentId)
    {
        return $this->repository->getTestResults($assignmentId);
    }

    public function getStudentDetail($testAssignmentId, $testResultId): array
    {
        $testAssignment = $this->repository->findById($testAssignmentId);
        $testResult = $this->repository->getTestResult($testResultId, $testAssignmentId);
        $studentAnswers = $this->repository->getStudentAnswers($testResult->id, $testAssignment->language_id);

        $studentAnswers = $this->processStudentAnswers($studentAnswers, $testAssignment);

        return compact('testAssignment', 'testResult', 'studentAnswers');
    }

    public function processStudentAnswers($studentAnswers, $testAssignment)
    {
        return $studentAnswers->map(function ($studentAnswer) use ($testAssignment) {
            $question = $studentAnswer->question;

            if (!$question) {
                return $studentAnswer;
            }

            if ($question->text) {
                $question->text = $this->decodePreservingMath($question->text);
            }

            if ($question->answers) {
                $question->answers = $question->answers->map(function ($answer) {
                    if ($answer->text) {
                        $answer->text = $this->decodePreservingMath($answer->text);
                    }
                    return $answer;
                });
            }

            if ($question->matchingPairs) {
                $question->matchingPairs = $question->matchingPairs->map(function ($pair) {
                    if ($pair->text) {
                        $pair->text = $this->decodePreservingMath($pair->text);
                    }
                    return $pair;
                });
            }

            if ($question->type === 'matching' && $studentAnswer->answer_text) {
                $studentAnswer->parsed_matching = $this->parseMatchingAnswer($studentAnswer->answer_text);

                $correctAnswer = $question->answers->where('is_correct', true)->first();
                if ($correctAnswer) {
                    $studentAnswer->correct_matching = $this->parseMatchingAnswer($correctAnswer->text);
                }
            }

            if ($question->type === 'sequence' && $studentAnswer->answer_text) {
                // Student javoblari - ORDER raqamlari
                $studentAnswer->parsed_sequence_orders = array_map('intval', explode(',', $studentAnswer->answer_text));

                // To'g'ri javob
                $studentAnswer->correct_sequence = $question->answers
                    ->sortBy('id')
                    ->pluck('order')
                    ->values()
                    ->toArray();
            }

            return $studentAnswer;
        });
    }

    public function processStudentAnswersForPdf($studentAnswers, $testAssignment)
    {
        return $studentAnswers->map(function ($studentAnswer) use ($testAssignment) {
            $question = $studentAnswer->question;

            if (!$question) {
                return $studentAnswer;
            }

            if ($question->text) {
                $question->text = $this->decodePreservingMath($question->text);
                $question->text = $this->convertLatexToText($question->text);
            }

            if ($question->answers) {
                $question->answers = $question->answers->map(function ($answer) {
                    if ($answer->text) {
                        $answer->text = $this->decodePreservingMath($answer->text);
                        $answer->text = $this->convertLatexToText($answer->text);
                    }
                    return $answer;
                });
            }

            if ($question->matchingPairs) {
                $question->matchingPairs = $question->matchingPairs->map(function ($pair) {
                    if ($pair->text) {
                        $pair->text = $this->decodePreservingMath($pair->text);
                        $pair->text = $this->convertLatexToText($pair->text);
                    }
                    return $pair;
                });
            }

            if ($question->type === 'matching' && $studentAnswer->answer_text) {
                $studentAnswer->student_matching = $this->parseMatchingAnswer($studentAnswer->answer_text);

                $correctAnswer = $question->answers->where('is_correct', true)->first();
                if ($correctAnswer) {
                    $studentAnswer->correct_matching = $this->parseMatchingAnswer($correctAnswer->text);
                }
            }

            if ($question->type === 'sequence' && $studentAnswer->answer_text) {
                // ✅ Student javoblari - ORDER raqamlari
                $studentAnswer->parsed_sequence_orders = array_map('intval', explode(',', $studentAnswer->answer_text));

                // ✅ To'g'ri javob
                $studentAnswer->correct_sequence = $question->answers
                    ->sortBy('id')
                    ->pluck('order')
                    ->values()
                    ->toArray();
            }

            return $studentAnswer;
        });
    }

    public function setLocaleForPdf($languageId)
    {
        if ($languageId == 1) {
            app()->setLocale('kk');
        } elseif ($languageId == 2) {
            app()->setLocale('uz');
        } elseif ($languageId == 3) {
            app()->setLocale('ru');
        }
    }

    public function validateRetake(TestAssignment $assignment, array $studentIds): array
    {
        if ($assignment->is_retake) {
            return [
                'valid' => false,
                'message' => __('Cannot create retake for a retake assignment')
            ];
        }

        $failedStudentIds = $this->repository->getFailedStudents($assignment)->pluck('id')->toArray();
        $invalidStudents = array_diff($studentIds, $failedStudentIds);

        if (!empty($invalidStudents)) {
            return [
                'valid' => false,
                'message' => __('Some selected students have not failed this test')
            ];
        }

        return ['valid' => true];
    }

    public function createRetake(TestAssignment $assignment, array $validated): TestAssignment
    {
        DB::beginTransaction();
        try {
            $retakeAssignment = $this->repository->createRetakeAssignment([
                'parent_assignment_id' => $assignment->id,
                'is_retake' => true,
                'language_id' => $assignment->language_id,
                'faculty_id' => $assignment->faculty_id,
                'group_id' => $assignment->group_id,
                'subject_id' => $assignment->subject_id,
                'question_count' => $validated['question_count'],
                'duration' => $validated['duration'],
                'start_time' => $validated['start_time'],
                'end_time' => $validated['end_time'],
                'description' => $validated['description'] ?? $assignment->description,
                'is_active' => $validated['is_active'] ?? false,
            ]);

            foreach ($validated['student_ids'] as $studentId) {
                $this->repository->createRetakeTestResult([
                    'test_assignment_id' => $retakeAssignment->id,
                    'student_id' => $studentId,
                    'status' => 'not_started',
                    'total_questions' => $validated['question_count'],
                    'score' => 0,
                    'correct_answers' => 0,
                ]);
            }

            DB::commit();
            return $retakeAssignment;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function getFailedStudents(TestAssignment $assignment)
    {
        return $this->repository->getFailedStudents($assignment);
    }

    public function updateScore(TestResult $testResult, array $data): bool
    {
        return $this->repository->updateTestResult($testResult, $data);
    }

    private function decodePreservingMath(string $text): string
    {
        $mathPlaceholders = [];
        $placeholderIndex = 0;

        $text = preg_replace_callback('/\\\\\[.*?\\\\\]|\\\\\(.*?\\\\\)/s', function ($matches) use (&$mathPlaceholders, &$placeholderIndex) {
            $placeholder = "___MATH_PLACEHOLDER_{$placeholderIndex}___";
            $mathPlaceholders[$placeholder] = $matches[0];
            $placeholderIndex++;
            return $placeholder;
        }, $text);

        $text = preg_replace_callback('/\$\$.*?\$\$|\$.*?\$/s', function ($matches) use (&$mathPlaceholders, &$placeholderIndex) {
            $placeholder = "___MATH_PLACEHOLDER_{$placeholderIndex}___";
            $mathPlaceholders[$placeholder] = $matches[0];
            $placeholderIndex++;
            return $placeholder;
        }, $text);

        $text = html_entity_decode($text, ENT_QUOTES | ENT_XML1, 'UTF-8');

        foreach ($mathPlaceholders as $placeholder => $mathContent) {
            $text = str_replace($placeholder, $mathContent, $text);
        }

        return $text;
    }

    private function parseMatchingAnswer(string $text): array
    {
        $result = [];
        $text = str_replace([' ', ';'], ['', ','], $text);
        $text = trim($text, ',');
        $pairs = explode(',', $text);

        foreach ($pairs as $pair) {
            if (strpos($pair, '-') !== false) {
                list($left, $right) = explode('-', $pair, 2);
                $result[$left] = $right;
            }
        }

        return $result;
    }

    private function convertLatexToText($text)
    {
        $text = preg_replace('/\\\\lim_\{([^}]+)\s+\\\\rightarrow\s+([^}]+)\}/', 'lim($1→$2)', $text);

        $text = preg_replace('/([a-zA-Z0-9]+)\\^\{2\}/', '$1²', $text);
        $text = preg_replace('/([a-zA-Z0-9]+)\\^\{3\}/', '$1³', $text);
        $text = preg_replace('/([a-zA-Z0-9]+)\\^\{([0-9])\}/', '$1^$2', $text);

        $text = preg_replace('/([a-zA-Z0-9]+)_\{([0-9])\}/', '$1₂', $text);

        $text = str_replace(
            ['\left(', '\right)', '\left[', '\right]', '\left\{', '\right\}'],
            ['(', ')', '[', ']', '{', '}'],
            $text
        );

        $text = preg_replace('/\\\\frac\{([^}]+)\}\{([^}]+)\}/', '($1)/($2)', $text);

        $text = preg_replace('/\\\\sqrt\{([^}]+)\}/', '√($1)', $text);

        $text = preg_replace('/\\\\int_\{([^}]+)\}\\^\{([^}]+)\}/', '∫($1→$2)', $text);
        $text = str_replace('\\int', '∫', $text);

        $text = preg_replace('/\\\\sum_\{([^}]+)\}\\^\{([^}]+)\}/', 'Σ($1→$2)', $text);
        $text = str_replace('\\sum', 'Σ', $text);

        $text = str_replace(['\cdot', '\times', '\div'], ['·', '×', '÷'], $text);
        $text = str_replace(['\leq', '\geq', '\neq'], ['≤', '≥', '≠'], $text);
        $text = str_replace(['\approx', '\equiv', '\sim'], ['≈', '≡', '∼'], $text);

        $text = str_replace(
            ['\alpha', '\beta', '\gamma', '\delta', '\pi', '\theta'],
            ['α', 'β', 'γ', 'δ', 'π', 'θ'],
            $text
        );

        $text = preg_replace('/\\\\\[|\\\\\]|\\\\\(|\\\\\)/', '', $text);

        $text = str_replace('$', '', $text);

        $text = str_replace('\\', ' ', $text);

        $text = preg_replace('/\s+/', ' ', $text);

        return trim($text);
    }
}
