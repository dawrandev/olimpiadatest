<?php

namespace App\Http\Controllers;

use App\Models\TestAssignment;
use App\Models\TestResult;
use App\Services\StudentTestService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StudentTestController extends Controller
{
    protected StudentTestService $testService;

    public function __construct(StudentTestService $testService)
    {
        $this->testService = $testService;
    }

    public function home()
    {
        $user = auth()->user();
        $student = $user->student;

        if (!$student) {
            return view('student.home', [
                'tests' => collect()
            ])->with('error', __('Student information not found'));
        }

        $testsWithStatus = $this->testService->getAvailableTestsWithStatus($student);

        return view('pages.student.home', [
            'tests' => $testsWithStatus
        ]);
    }

    public function startTest(Request $request, $id)
    {
        try {
            $user = Auth::user();
            $student = $user->student;

            if (!$student) {
                return response()->json([
                    'success' => false,
                    'message' => __('Student information not found')
                ], 403);
            }

            $result = $this->testService->handleStartTest($id, $student);

            return response()->json($result, $result['success'] ? 200 : 403);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function takeTest($id)
    {
        try {
            $user = auth()->user();
            $student = $user->student;

            if (!$student) {
                return redirect()->route('student.home')
                    ->with('error', __('Student information not found'));
            }

            $result = $this->testService->handleTakeTest($id, $student);

            if (isset($result['redirect'])) {
                return redirect()->route('student.home')
                    ->with('warning', __('Time is over'));
            }

            return view('pages.student.test', $result);
        } catch (\Exception $e) {
            return redirect()->route('student.home')
                ->with('error', __('Test could not be opened.'));
        }
    }

    public function submitAnswer(Request $request, TestAssignment $testAssignment)
    {
        $request->validate([
            'student_answer_id' => 'required|exists:student_answers,id',
            'answer_id' => 'nullable|exists:answers,id',
            'answer_ids' => 'nullable|string',
            'answer_text' => 'nullable|string',
            'question_type' => 'required|in:single_choice,matching,sequence'
        ]);

        try {
            $result = $this->testService->submitAnswer($request->all());
            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function submitTest($id)
    {
        $student = auth()->user()->student;

        if (!$student) {
            return response()->json([
                'success' => false,
                'message' => __('Student information not found')
            ], 403);
        }

        try {
            $result = $this->testService->handleSubmitTest($id, $student);
            return response()->json($result, $result['success'] ? 200 : 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function result(TestAssignment $testAssignment)
    {
        $student = auth()->user()->student;
        $result = $this->testService->getTestResult($testAssignment, $student);

        return view('student.test.result', $result);
    }

    public function updateScore(Request $request, TestResult $testResult)
    {
        $request->validate([
            'score' => 'required|integer|min:0|max:100',
            'grade' => 'nullable|integer|min:2|max:5'
        ]);

        $this->testService->updateScore($testResult, $request->only(['score', 'grade']));

        return redirect()->back()->with('success', __('Score updated successfully'));
    }
}
