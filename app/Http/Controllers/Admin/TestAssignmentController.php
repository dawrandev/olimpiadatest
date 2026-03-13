<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreRetakeAssignmentRequest;
use App\Http\Requests\Admin\StoreTestAssignmentRequest;
use App\Http\Requests\Admin\UpdateTestAssignmentRequest;
use App\Http\Requests\Admin\UpdateScoreRequest;
use App\Models\TestAssignment;
use App\Models\Group;
use App\Models\Subject;
use App\Models\Language;
use App\Models\TestResult;
use App\Repositories\Admin\TestAssignmentRepository;
use App\Services\Admin\TestAssignmentService;
use Barryvdh\DomPDF\Facade\Pdf;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class TestAssignmentController extends Controller
{
    public function __construct(protected TestAssignmentService $testAssignmentService) {}

    public function index(Request $request)
    {
        $assignments = $this->testAssignmentService->getFilteredAssignments($request);

        return view('pages.admin.test-assignments.index', compact('assignments'));
    }

    public function create()
    {
        $faculties = getFaculties();
        $groups = getGroups();
        $subjects = Subject::all();
        $languages = Language::all();

        return view('pages.admin.test-assignments.create', compact('faculties', 'groups', 'subjects', 'languages'));
    }

    public function store(StoreTestAssignmentRequest $request)
    {
        $validated = $request->validated();

        $questionValidation = $this->testAssignmentService->validateAvailableQuestions(
            $validated['subject_id'],
            $validated['language_id'],
            $validated['question_count']
        );

        if (!$questionValidation['valid']) {
            return back()->withErrors([
                'question_count' => $questionValidation['message']
            ])->withInput();
        }

        $validated['is_active'] = $request->has('is_active') ? true : false;

        try {
            $createdCount = $this->testAssignmentService->createForAllGroups($validated);

            return redirect()->route('admin.test-assignments.index')
                ->with('success', __('Test assignment created successfully for :count groups!', ['count' => $createdCount]));
        } catch (Exception $e) {
            Log::error('Test assignment creation error: ' . $e->getMessage());
            return back()->withErrors(['error' => __('An error occurred. Please try again.')])->withInput();
        }
    }

    public function show($id)
    {
        $data = $this->testAssignmentService->getAssignmentWithStatistics($id);
        $testAssignment = $data['assignment'];
        $statistics = $data['statistics'];

        return view('pages.admin.test-assignments.show', compact('testAssignment', 'statistics'));
    }

    public function edit($testAssignment)
    {
        $testAssignment = TestAssignment::findOrFail($testAssignment);

        if (!$this->testAssignmentService->canEdit($testAssignment)) {
            return back()->with('error', __('Cannot edit a test that has started or completed.'));
        }

        return view('pages.admin.test-assignments.edit', compact('testAssignment'));
    }

    public function update(UpdateTestAssignmentRequest $request, $testAssignment)
    {
        $testAssignment = TestAssignment::findOrFail($testAssignment);

        if (!$this->testAssignmentService->canUpdate($testAssignment)) {
            return back()->with('error', __('Cannot edit a started test.'));
        }

        $validated = $request->validated();
        $validated['is_active'] = $request->has('is_active') ? 1 : 0;

        $this->testAssignmentService->update($testAssignment, $validated);

        return redirect()->route('admin.test-assignments.index')
            ->with('success', __('Test assignment updated successfully!'));
    }

    public function destroy($id)
    {
        try {
            $this->testAssignmentService->delete($id);

            return redirect()->route('admin.test-assignments.index')
                ->with('success', __('Test assignment deleted.'));
        } catch (Exception $e) {
            return back()->with('error', __('An error occurred while deleting the test assignment.'));
        }
    }

    public function toggleStatus($id)
    {
        $status = $this->testAssignmentService->toggleStatus($id);

        return back()->with('success', __('Test :status.', ['status' => __($status)]));
    }

    public function results($testAssignment)
    {
        $testAssignment = TestAssignment::findOrFail($testAssignment);
        $results = $this->testAssignmentService->getResults($testAssignment->id);

        return view('pages.admin.test-assignments.results', compact('testAssignment', 'results'));
    }

    public function getGroupsByFaculty(Request $request)
    {
        $groups = Group::where('faculty_id', $request->faculty_id)
            ->with('students')
            ->get()
            ->map(function ($group) {
                return [
                    'id' => $group->id,
                    'name' => $group->name,
                    'students_count' => $group->students->count()
                ];
            });

        return response()->json($groups);
    }

    public function studentDetail($testAssignmentId, $testResultId)
    {
        $data = $this->testAssignmentService->getStudentDetail($testAssignmentId, $testResultId);

        return view('pages.admin.test-assignments.student-detail', $data);
    }

    public function downloadPdf($testAssignmentId, $testResultId)
    {
        $testAssignment = TestAssignment::findOrFail($testAssignmentId);
        $testResult = TestResult::where('id', $testResultId)
            ->where('test_assignment_id', $testAssignmentId)
            ->with('student.user')
            ->firstOrFail();

        $this->testAssignmentService->setLocaleForPdf($testAssignment->language_id);

        $studentAnswers = $this->testAssignmentService->processStudentAnswersForPdf(
            app(TestAssignmentRepository::class)->getStudentAnswers($testResult->id, $testAssignment->language_id),
            $testAssignment
        );

        $pdf = Pdf::loadView('pages.admin.test-assignments.student-pdf', [
            'testAssignment' => $testAssignment,
            'testResult' => $testResult,
            'studentAnswers' => $studentAnswers
        ]);

        $pdf->setPaper('a4', 'portrait');
        $pdf->setOption('enable-local-file-access', true);

        $studentName = str_replace(' ', '_', $testResult->student->full_name);
        $date = now()->format('Y-m-d');
        $filename = "test_result_{$studentName}_{$date}.pdf";

        return $pdf->download($filename);
    }

    public function exportExcel($testAssignment)
    {
        $testAssignment = TestAssignment::findOrFail($testAssignment);
        $results = $this->testAssignmentService->getResults($testAssignment->id);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $date = $testAssignment->start_time->format('d.m.Y');
        $groupName = $testAssignment->group->name;
        $subjectName = optional($testAssignment->subject->translations->firstWhere('language_id', currentLanguageId()))->name;
        $retakeStatus = $testAssignment->is_retake ? __('Retake') : __('Regular');

        $sheet->mergeCells('A1:F1');
        $sheet->setCellValue('A1', __("Group: :group - Subject: :subject (:status) - :date", [
            'group' => $groupName,
            'subject' => $subjectName,
            'status' => $retakeStatus,
            'date' => $date,
        ]));
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $headers = [
            '#',
            __('Student Name'),
            __('Status'),
            __('Correct Answers'),
            __('Score (%)'),
            __('Grade'),
        ];
        $sheet->fromArray($headers, null, 'A3');

        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '4472C4']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ];
        $sheet->getStyle('A3:F3')->applyFromArray($headerStyle);

        $row = 4;
        foreach ($results as $index => $result) {
            $sheet->setCellValue('A' . $row, $index + 1);
            $sheet->setCellValue('B' . $row, $result->student->full_name);
            $sheet->setCellValue('C' . $row, __(ucfirst($result->status)));

            $correctAnswers = $result->status == 'completed'
                ? "{$result->correct_answers}/{$result->total_questions}"
                : __('-');
            $score = $result->status == 'completed' ? $result->score : __('-');
            $grade = $result->grade ?? __('-');

            $sheet->setCellValue('D' . $row, $correctAnswers);
            $sheet->setCellValue('E' . $row, $score);
            $sheet->setCellValue('F' . $row, $grade);

            $row++;
        }

        $row += 2;

        $sheet->mergeCells("A{$row}:C{$row}");
        $sheet->setCellValue("A{$row}", 'Saodat ___________________');
        $sheet->getStyle("A{$row}")->getFont()->setBold(true)->setSize(12);
        $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

        foreach (range('A', 'F') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $styleArray = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                ],
            ],
        ];
        $sheet->getStyle('A3:F' . ($row - 3))->applyFromArray($styleArray);

        $retakePrefix = $testAssignment->is_retake ? __('Retake_') : '';
        $filename = "{$retakePrefix}{$groupName}_{$date}_" . __('test_results') . ".xlsx";

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header("Content-Disposition: attachment; filename=\"{$filename}\"");
        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }

    public function retakeCreate($id)
    {
        $assignment = TestAssignment::findOrFail($id);

        if ($assignment->is_retake) {
            return redirect()
                ->route('admin.test-assignments.index')
                ->with('error', __('Cannot create retake for a retake assignment'));
        }

        $failedStudents = $this->testAssignmentService->getFailedStudents($assignment);

        if ($failedStudents->isEmpty()) {
            return redirect()
                ->route('admin.test-assignments.index')
                ->with('info', __('No failed students found for this assignment'));
        }

        return view('pages.admin.test-assignments.retake', compact('assignment', 'failedStudents'));
    }

    public function retakeStore(StoreRetakeAssignmentRequest $request, $id)
    {
        $assignment = TestAssignment::findOrFail($id);
        $validated = $request->validated();

        $retakeValidation = $this->testAssignmentService->validateRetake($assignment, $validated['student_ids']);

        if (!$retakeValidation['valid']) {
            return back()
                ->withErrors(['error' => $retakeValidation['message']])
                ->withInput();
        }

        $questionValidation = $this->testAssignmentService->validateAvailableQuestions(
            $assignment->subject_id,
            $assignment->language_id,
            $validated['question_count']
        );

        if (!$questionValidation['valid']) {
            return back()->withErrors([
                'question_count' => $questionValidation['message']
            ])->withInput();
        }

        try {
            $validated['is_active'] = $request->has('is_active');
            $retakeAssignment = $this->testAssignmentService->createRetake($assignment, $validated);

            return redirect()
                ->route('admin.test-assignments.index')
                ->with('success', __('Retake assignment created successfully for :count student(s)', [
                    'count' => count($validated['student_ids'])
                ]));
        } catch (Exception $e) {
            return back()
                ->withErrors(['error' => __('Failed to create retake assignment. Please try again.')])
                ->withInput();
        }
    }

    public function updateScore(UpdateScoreRequest $request, $testResult)
    {
        $testResult = TestResult::findOrFail($testResult);
        $validated = $request->validated();

        $this->testAssignmentService->updateScore($testResult, $validated);

        return redirect()->back()->with('success', __('Score updated successfully'));
    }
}
