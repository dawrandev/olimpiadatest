<?php

namespace App\Services;

use App\Models\Student;
use App\Models\StudentAnswer;
use App\Models\TestAssignment;
use App\Models\TestResult;
use App\Repositories\StudentTestRepository;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;

class StudentTestService
{
    protected StudentTestRepository $repository;

    public function __construct(StudentTestRepository $repository)
    {
        $this->repository = $repository;
    }

    public function getAvailableTestsWithStatus(Student $student): Collection
    {
        $availableTests = $this->repository->getAvailableTests($student->group_id);

        return $availableTests->map(function ($test) use ($student) {
            $testResult = $this->repository->findTestResult($test->id, $student->id);
            $test->student_status = $testResult ? $testResult->status : 'not_started';
            $test->test_result = $testResult;
            return $test;
        });
    }

    public function validateTestAccess(TestAssignment $testAssignment, Student $student): array
    {
        if ($testAssignment->group_id != $student->group_id) {
            return ['success' => false, 'message' => __('You are not allowed to take this test')];
        }

        if (!$testAssignment->is_active) {
            return ['success' => false, 'message' => __('The test is not active')];
        }

        if ($testAssignment->start_time > now()) {
            return ['success' => false, 'message' => __('The test has not started yet')];
        }

        if ($testAssignment->end_time < now()) {
            return ['success' => false, 'message' => __('The test time has expired')];
        }

        return ['success' => true];
    }

    public function validateTestResult(TestResult $testResult): array
    {
        if ($testResult->status == 'completed') {
            return ['success' => false, 'message' => __('You have already completed this test')];
        }

        if ($testResult->status == 'in_progress') {
            return [
                'success' => true,
                'redirect' => true,
                'message' => __('Test already in progress')
            ];
        }

        return ['success' => true];
    }

    public function startTest(TestAssignment $testAssignment, TestResult $testResult): void
    {
        DB::beginTransaction();
        try {
            $selectedQuestions = $this->selectQuestions($testAssignment);

            if ($selectedQuestions->count() < $testAssignment->question_count) {
                throw new \Exception(__('Not enough questions available'));
            }

            $this->repository->updateTestResultStatus($testResult, [
                'status' => 'in_progress',
                'started_at' => now()
            ]);

            foreach ($selectedQuestions->shuffle() as $order => $question) {
                $this->repository->createStudentAnswer([
                    'test_result_id' => $testResult->id,
                    'question_id' => $question->id,
                    'answer_id' => null,
                    'is_correct' => false,
                    'order' => $order + 1
                ]);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    protected function selectQuestions(TestAssignment $testAssignment): Collection
    {
        $topics = $this->repository->getTopicsWithQuestions($testAssignment->subject_id);
        $totalQuestions = $testAssignment->question_count;
        $topicCount = $topics->count();
        $questionsPerTopic = floor($totalQuestions / $topicCount);
        $remainingQuestions = $totalQuestions % $topicCount;

        $selectedQuestions = collect();

        foreach ($topics as $index => $topic) {
            $count = $questionsPerTopic + ($index < $remainingQuestions ? 1 : 0);

            $topicQuestions = $this->repository->getRandomQuestionsByTopic(
                $testAssignment->subject_id,
                $testAssignment->language_id,
                $topic->id,
                $count
            );

            $selectedQuestions = $selectedQuestions->merge($topicQuestions);
        }

        if ($selectedQuestions->count() < $totalQuestions) {
            $usedQuestionIds = $selectedQuestions->pluck('id')->toArray();
            $needed = $totalQuestions - $selectedQuestions->count();

            $additionalQuestions = $this->repository->getAdditionalRandomQuestions(
                $testAssignment->subject_id,
                $testAssignment->language_id,
                $usedQuestionIds,
                $needed
            );

            $selectedQuestions = $selectedQuestions->merge($additionalQuestions);
        }

        return $selectedQuestions;
    }

    public function prepareTestData(TestAssignment $testAssignment, TestResult $testResult, Student $student): array
    {
        if ($testAssignment->language && $testAssignment->language->code) {
            App::setLocale($testAssignment->language->code);
            session()->put('locale', $testAssignment->language->code);
        }

        $studentAnswers = $this->repository->getStudentAnswersWithRelations(
            $testResult->id,
            $testAssignment->language_id
        );

        $questions = $this->processQuestions($studentAnswers, $testAssignment, $student);

        $totalSeconds = $testAssignment->duration * 60;
        $elapsedSeconds = now()->diffInSeconds($testResult->started_at);
        $remainingSeconds = max(0, $totalSeconds - $elapsedSeconds);

        return [
            'testAssignment' => $testAssignment,
            'testResult' => $testResult,
            'questions' => $questions,
            'studentAnswers' => $studentAnswers,
            'language' => $testAssignment->language,
            'remainingSeconds' => $remainingSeconds,
            'totalSeconds' => $totalSeconds,
            'startedAt' => $testResult->started_at->timestamp,
            'translations' => $this->getTranslations()
        ];
    }

    protected function processQuestions(Collection $studentAnswers, TestAssignment $testAssignment, Student $student): Collection
    {
        return $studentAnswers->map(function ($studentAnswer) use ($testAssignment, $student) {
            $question = $studentAnswer->question;

            if (!$question) {
                return null;
            }

            if ($question->text) {
                $question->text = html_entity_decode($question->text, ENT_QUOTES | ENT_XML1, 'UTF-8');
            }

            if ($question->answers && $question->answers->isNotEmpty()) {
                if ($question->type === 'sequence') {
                    $question->answers = $question->answers->map(function ($answer) {
                        if (isset($answer->text)) {
                            $answer->text = html_entity_decode($answer->text, ENT_QUOTES | ENT_XML1, 'UTF-8');
                        }
                        return $answer;
                    });
                    $question->is_multiple = false;
                } else {
                    $question->answers = $this->shuffleAnswers($question->answers, $testAssignment, $question, $student);

                    if ($question->type === 'single_choice') {
                        $correctCount = $question->answers->where('is_correct', true)->count();
                        $question->is_multiple = $correctCount > 1;
                    } else {
                        $question->is_multiple = false;
                    }
                }
            }

            if (method_exists($question, 'matchingPairs')) {
                $pairs = $question->matchingPairs()->get();
                $pairs->each(function ($pair) {
                    if ($pair->text) {
                        $pair->text = html_entity_decode($pair->text, ENT_QUOTES | ENT_XML1, 'UTF-8');
                    }
                });
            }

            $question->is_answered = !is_null($studentAnswer->answer_id) || !is_null($studentAnswer->answer_text);
            $question->student_answer_id = $studentAnswer->id;

            if ($studentAnswer->answer_id) {
                $question->selected_answer_id = $studentAnswer->answer_id;
            } elseif ($studentAnswer->answer_text) {
                $question->selected_answer_text = $studentAnswer->answer_text;
            }

            return $question;
        })->filter()->values();
    }

    protected function shuffleAnswers(Collection $answers, TestAssignment $testAssignment, $question, Student $student): Collection
    {
        $seed = crc32($testAssignment->id . '-' . $question->id . '-' . $student->id);
        $answersArray = $answers->toArray();

        mt_srand($seed);
        shuffle($answersArray);
        mt_srand();

        return collect($answersArray)->map(function ($answer) {
            if (is_array($answer)) {
                if (isset($answer['text'])) {
                    $answer['text'] = html_entity_decode($answer['text'], ENT_QUOTES | ENT_XML1, 'UTF-8');
                }
                return (object) $answer;
            } else {
                if (isset($answer->text)) {
                    $answer->text = html_entity_decode($answer->text, ENT_QUOTES | ENT_XML1, 'UTF-8');
                }
                return $answer;
            }
        });
    }

    protected function getTranslations(): array
    {
        return [
            'timeUpTitle' => __('Time is up!'),
            'timeUpText' => __('Test is being completed automatically...'),
            'warningTitle' => __('Warning!'),
            'selectAnswer' => __('Please select an answer!'),
            'enterSequence' => __('Please enter the correct sequence!'),
            'enterMatching' => __('Please complete all matching pairs!'),
            'invalidSequence' => __('Invalid sequence! Use numbers from 1 to N without repetition.'),
            'errorTitle' => __('Error!'),
            'errorOccurred' => __('An error occurred'),
            'serverError' => __('Could not connect to server'),
            'finishTitle' => __('Finish Test'),
            'finishText' => __('Are you sure you want to finish the test?'),
            'yesFinish' => __('Yes, finish'),
            'cancel' => __('Cancel'),
            'testFinished' => __('Test Finished!'),
            'answeredQuestions' => __('Answered Questions'),
            'correctAnswers' => __('Correct Answers'),
            'score' => __('Score'),
            'timeUsed' => __('Time Used'),
            'logout' => __('Logout'),
            'cannotSelectAll' => __('You cannot select all variants!'),
            'answerSubmitted' => __('Answer submitted'),
        ];
    }

    public function submitAnswer(array $data): array
    {
        $studentAnswer = $this->repository->findStudentAnswer($data['student_answer_id']);
        $question = $studentAnswer->question;
        $isCorrect = false;
        $partialScore = 0;

        if ($data['question_type'] === 'single_choice') {
            [$isCorrect, $partialScore] = $this->processSingleChoiceAnswer($data, $question, $studentAnswer);
        } elseif ($data['question_type'] === 'matching') {
            [$isCorrect, $partialScore] = $this->processMatchingAnswer($data, $question, $studentAnswer);
        } elseif ($data['question_type'] === 'sequence') {
            [$isCorrect, $partialScore] = $this->processSequenceAnswer($data, $question, $studentAnswer);
        }

        return [
            'success' => true,
            'is_correct' => $isCorrect,
            'partial_score' => $partialScore
        ];
    }

    protected function processSingleChoiceAnswer(array $data, $question, StudentAnswer $studentAnswer): array
    {
        if (!empty($data['answer_ids'])) {
            $selectedAnswerIds = array_map('intval', explode(',', $data['answer_ids']));
            $correctAnswerIds = $question->answers()->where('is_correct', true)->pluck('id')->toArray();
            $totalCorrectAnswers = count($correctAnswerIds);

            $correctSelectedCount = count(array_intersect($selectedAnswerIds, $correctAnswerIds));
            $incorrectSelectedCount = count(array_diff($selectedAnswerIds, $correctAnswerIds));

            $partialScore = 0;
            if ($totalCorrectAnswers > 0) {
                $partialScore = max(0, ($correctSelectedCount - $incorrectSelectedCount) / $totalCorrectAnswers);
                $partialScore = round($partialScore * 100, 2);
            }

            sort($selectedAnswerIds);
            sort($correctAnswerIds);
            $isCorrect = ($selectedAnswerIds === $correctAnswerIds);

            $this->repository->updateStudentAnswer($studentAnswer, [
                'answer_id' => null,
                'answer_text' => $data['answer_ids'],
                'is_correct' => $isCorrect,
                'partial_score' => $partialScore
            ]);

            return [$isCorrect, $partialScore];
        } else {
            $answer = \App\Models\Answer::find($data['answer_id']);
            $isCorrect = $answer && $answer->is_correct;
            $partialScore = $isCorrect ? 100 : 0;

            $this->repository->updateStudentAnswer($studentAnswer, [
                'answer_id' => $data['answer_id'],
                'answer_text' => null,
                'is_correct' => $isCorrect,
                'partial_score' => $partialScore
            ]);

            return [$isCorrect, $partialScore];
        }
    }

    protected function processMatchingAnswer(array $data, $question, StudentAnswer $studentAnswer): array
    {
        $userAnswer = $data['answer_text'];
        $correctAnswer = $question->answers()->where('is_correct', true)->first();
        $isCorrect = false;

        if ($correctAnswer) {
            $normalizedUser = $this->normalizeMatchingAnswer($userAnswer);
            $normalizedCorrect = $this->normalizeMatchingAnswer($correctAnswer->text);
            $isCorrect = ($normalizedUser === $normalizedCorrect);
        }

        $partialScore = $isCorrect ? 100 : 0;

        $this->repository->updateStudentAnswer($studentAnswer, [
            'answer_id' => null,
            'answer_text' => $userAnswer,
            'is_correct' => $isCorrect,
            'partial_score' => $partialScore
        ]);

        return [$isCorrect, $partialScore];
    }

    protected function processSequenceAnswer(array $data, $question, StudentAnswer $studentAnswer): array
    {
        $userSequence = $data['answer_text'];
        $userOrderNumbers = array_map('intval', array_filter(explode(',', $userSequence)));

        $correctOrderSequence = $question->answers()
            ->orderBy('id')
            ->pluck('order')
            ->toArray();

        $isCorrect = (count($userOrderNumbers) === count($correctOrderSequence))
            && ($userOrderNumbers === $correctOrderSequence);

        $partialScore = $isCorrect ? 100 : 0;

        $this->repository->updateStudentAnswer($studentAnswer, [
            'answer_id' => null,
            'answer_text' => $userSequence,
            'is_correct' => $isCorrect,
            'partial_score' => $partialScore
        ]);

        return [$isCorrect, $partialScore];
    }

    protected function normalizeMatchingAnswer(string $text): string
    {
        $text = str_replace([' ', ';'], ['', ','], $text);
        $text = trim($text, ',');
        $pairs = explode(',', $text);
        sort($pairs);
        return implode(',', $pairs);
    }

    public function finishTest(TestResult $testResult): TestResult
    {
        $studentAnswers = $this->repository->getStudentAnswers($testResult->id);
        $studentAnswers->each->fresh();

        $totalQuestions = $studentAnswers->count();
        $totalScore = 0;
        $correctAnswers = 0;

        if ($totalQuestions == 0) {
            $this->repository->updateTestResult($testResult, [
                'status' => 'completed',
                'completed_at' => now(),
                'correct_answers' => 0,
                'score' => 0,
                'grade' => null
            ]);
            return $testResult;
        }

        foreach ($studentAnswers as $studentAnswer) {
            $studentAnswer->refresh();

            $partialScore = $studentAnswer->partial_score;

            if ($partialScore === null || $partialScore == 0) {
                if ($studentAnswer->is_correct) {
                    $partialScore = 100;
                } elseif ($studentAnswer->answer_text && strpos($studentAnswer->answer_text, ',') !== false) {
                    $partialScore = $this->recalculatePartialScore($studentAnswer);
                }
            }

            $partialScoreFloat = floatval($partialScore);
            $totalScore += $partialScoreFloat;

            if ($studentAnswer->is_correct) {
                $correctAnswers++;
            }
        }

        $finalScore = round($totalScore / $totalQuestions, 2);
        $grade = $this->calculateGrade($finalScore);

        $this->repository->updateTestResult($testResult, [
            'status' => 'completed',
            'completed_at' => now(),
            'correct_answers' => $correctAnswers,
            'score' => $finalScore,
            'grade' => $grade
        ]);

        return $testResult;
    }

    protected function recalculatePartialScore(StudentAnswer $studentAnswer): float
    {
        $question = $studentAnswer->question()->with('answers')->first();
        if ($question && $question->type === 'single_choice') {
            $selectedAnswerIds = array_map('intval', explode(',', $studentAnswer->answer_text));
            $correctAnswerIds = $question->answers()->where('is_correct', true)->pluck('id')->toArray();
            $totalCorrectAnswers = count($correctAnswerIds);

            if ($totalCorrectAnswers > 1) {
                $correctSelectedCount = count(array_intersect($selectedAnswerIds, $correctAnswerIds));
                $incorrectSelectedCount = count(array_diff($selectedAnswerIds, $correctAnswerIds));

                $partialScore = max(0, ($correctSelectedCount - $incorrectSelectedCount) / $totalCorrectAnswers);
                $partialScore = round($partialScore * 100, 2);

                $this->repository->updateStudentAnswer($studentAnswer, ['partial_score' => $partialScore]);

                return $partialScore;
            }
        }
        return 0;
    }

    protected function calculateGrade(float $score): ?int
    {
        if ($score >= 90) {
            return 5;
        } elseif ($score >= 70) {
            return 4;
        } elseif ($score >= 60) {
            return 3;
        }

        return null;
    }

    public function updateScore(TestResult $testResult, array $data): void
    {
        $this->repository->updateTestResult($testResult, $data);
    }

    // Service klasining oxiriga quyidagi metodlarni qo'shing:

    public function handleStartTest(int $testAssignmentId, Student $student): array
    {
        $testAssignment = $this->repository->findTestAssignment($testAssignmentId);

        $accessValidation = $this->validateTestAccess($testAssignment, $student);
        if (!$accessValidation['success']) {
            return $accessValidation;
        }
        $testResult = $this->repository->findTestResult($testAssignment->id, $student->id);

        if (!$testResult) {
            return [
                'success' => false,
                'message' => __('You are not assigned to this test')
            ];
        }

        $resultValidation = $this->validateTestResult($testResult);
        if (!$resultValidation['success']) {
            return $resultValidation;
        }

        if (isset($resultValidation['redirect'])) {
            return [
                'success' => true,
                'redirect_url' => route('student.test.take', $testAssignment->id)
            ];
        }

        $this->startTest($testAssignment, $testResult);

        return [
            'success' => true,
            'redirect_url' => route('student.test.take', $testAssignment->id)
        ];
    }

    public function handleTakeTest(int $testAssignmentId, Student $student): array
    {
        $testAssignment = $this->repository->findTestAssignmentWithLanguage($testAssignmentId);

        $testResult = $this->repository->findInProgressTestResult($testAssignment->id, $student->id);

        $testData = $this->prepareTestData($testAssignment, $testResult, $student);

        if ($testData['remainingSeconds'] == 0) {
            $this->finishTest($testResult);
            return ['redirect' => true];
        }

        return $testData;
    }

    public function handleSubmitTest(int $testAssignmentId, Student $student): array
    {
        $testAssignment = $this->repository->findTestAssignment($testAssignmentId);

        $testResult = $this->repository->findTestResult($testAssignment->id, $student->id);

        if (!$testResult) {
            return [
                'success' => false,
                'message' => __('Test not found')
            ];
        }

        if ($testResult->status === 'completed') {
            return [
                'success' => false,
                'message' => __('The test has already been completed'),
                'redirect_url' => route('student.home')
            ];
        }

        $testResult = $this->finishTest($testResult);

        return [
            'success' => true,
            'correct_answers' => $testResult->correct_answers,
            'score' => $testResult->score,
            'redirect_url' => route('student.home')
        ];
    }

    public function getTestResult(TestAssignment $testAssignment, Student $student): array
    {
        $testResult = $this->repository->findCompletedTestResult($testAssignment->id, $student->id);

        if ($testAssignment->language && $testAssignment->language->code) {
            app()->setLocale($testAssignment->language->code);
            session()->put('locale', $testAssignment->language->code);
        }

        return [
            'testAssignment' => $testAssignment,
            'testResult' => $testResult
        ];
    }
}
