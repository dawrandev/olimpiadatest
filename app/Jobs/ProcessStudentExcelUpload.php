<?php

namespace App\Jobs;

use App\Models\Faculty;
use App\Models\Group;
use App\Models\Student;
use App\Models\StudentUpload;
use App\Models\TestAssignment;
use App\Models\TestResult;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ProcessStudentExcelUpload implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 600; // 10 minutes
    public $tries = 1;

    protected array $columnPositions = [
        'student_id' => 0,  // A
        'full_name'  => 1,  // B
        'passport'   => 2,  // C
        'jshshir'    => 3,  // D
        'course'     => 4,  // E
        'faculty'    => 5,  // F
        'group'      => 6,  // G
    ];

    public function __construct(
        protected int $uploadId
    ) {}

    public function handle(): void
    {
        $upload = StudentUpload::find($this->uploadId);

        if (!$upload) {
            Log::error("StudentUpload not found: {$this->uploadId}");
            return;
        }

        $upload->update([
            'status' => 'processing',
            'started_at' => now(),
        ]);

        try {
            $this->processExcel($upload);
        } catch (\Exception $e) {
            Log::error("Excel upload failed: " . $e->getMessage());
            $upload->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
                'completed_at' => now(),
            ]);
        }
    }

    protected function processExcel(StudentUpload $upload): void
    {
        $filePath = storage_path('app/' . $upload->file_path);

        if (!file_exists($filePath)) {
            throw new \Exception("File not found: {$filePath}");
        }

        $spreadsheet = IOFactory::load($filePath);
        $worksheet = $spreadsheet->getActiveSheet();
        $rows = $worksheet->toArray();

        // Remove header row
        array_shift($rows);

        // Filter empty rows
        $rows = array_filter($rows, fn($row) => !empty(array_filter($row)));
        $rows = array_values($rows);

        $upload->update(['total_rows' => count($rows)]);

        $uploadedCount = 0;
        $skippedCount = 0;
        $errorCount = 0;
        $errors = [];
        $createdFaculties = [];
        $createdGroups = [];

        // Get existing JSSHIRs
        $existingJshshirs = Student::pluck('jshshir')->filter()->toArray();

        // Cache
        $facultyCache = [];
        $groupCache = [];

        // Process in chunks
        $chunkSize = 50;
        $chunks = array_chunk($rows, $chunkSize);

        foreach ($chunks as $chunkIndex => $chunk) {
            DB::beginTransaction();

            try {
                foreach ($chunk as $rowIndex => $row) {
                    $absoluteIndex = ($chunkIndex * $chunkSize) + $rowIndex;
                    $rowNumber = $absoluteIndex + 2;

                    $data = $this->extractRowData($row);
                    $jshshir = trim((string) ($data['jshshir'] ?? ''));

                    // Skip duplicates
                    if (!empty($jshshir) && in_array($jshshir, $existingJshshirs)) {
                        $skippedCount++;
                        continue;
                    }

                    // Validate
                    $validation = $this->validateStudentData($data, $rowNumber);

                    if (!$validation['valid']) {
                        $errorCount++;
                        $errors = array_merge($errors, $validation['errors']);
                        continue;
                    }

                    try {
                        // Faculty
                        $facultyName = trim((string) $data['faculty']);
                        if (!isset($facultyCache[$facultyName])) {
                            $faculty = Faculty::firstOrCreate(['name' => $facultyName]);
                            $facultyCache[$facultyName] = $faculty;
                            if ($faculty->wasRecentlyCreated) {
                                $createdFaculties[] = $facultyName;
                            }
                        } else {
                            $faculty = $facultyCache[$facultyName];
                        }

                        // Group
                        $groupName = trim((string) $data['group']);
                        $groupKey = $faculty->id . '_' . $groupName;
                        if (!isset($groupCache[$groupKey])) {
                            $group = Group::firstOrCreate(
                                ['name' => $groupName, 'faculty_id' => $faculty->id]
                            );
                            $groupCache[$groupKey] = $group;
                            if ($group->wasRecentlyCreated) {
                                $createdGroups[] = $groupName;
                            }
                        } else {
                            $group = $groupCache[$groupKey];
                        }

                        // Create user
                        $user = User::create([
                            'role' => 'student',
                            'login' => $jshshir,
                            'password' => Hash::make(trim((string) $data['passport'])),
                        ]);

                        // Create student
                        $student = Student::create([
                            'user_id' => $user->id,
                            'group_id' => $group->id,
                            'student_id' => trim((string) $data['student_id']),
                            'full_name' => trim((string) $data['full_name']),
                            'passport' => trim((string) $data['passport']),
                            'jshshir' => $jshshir,
                            'course' => $this->extractCourseNumber($data['course']),
                        ]);

                        // Create TestResults for active test assignments in this group
                        $this->createTestResultsForStudent($student);

                        $existingJshshirs[] = $jshshir;
                        $uploadedCount++;

                    } catch (\Exception $e) {
                        $errorCount++;
                        $errors[] = [
                            'row' => $rowNumber,
                            'message' => $e->getMessage()
                        ];
                    }
                }

                DB::commit();

                // Update progress
                $upload->update([
                    'processed_rows' => min(($chunkIndex + 1) * $chunkSize, count($rows)),
                    'uploaded_count' => $uploadedCount,
                    'skipped_count' => $skippedCount,
                    'error_count' => $errorCount,
                ]);

            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }
        }

        // Cleanup
        $spreadsheet->disconnectWorksheets();
        unset($spreadsheet);

        if (file_exists($filePath)) {
            @unlink($filePath);
        }

        // Final update
        $upload->update([
            'status' => 'completed',
            'processed_rows' => count($rows),
            'uploaded_count' => $uploadedCount,
            'skipped_count' => $skippedCount,
            'error_count' => $errorCount,
            'errors' => array_slice($errors, 0, 100), // Limit errors
            'created_faculties' => array_unique($createdFaculties),
            'created_groups' => array_unique($createdGroups),
            'completed_at' => now(),
        ]);
    }

    protected function extractRowData(array $row): array
    {
        $data = [];
        foreach ($this->columnPositions as $fieldName => $position) {
            $data[$fieldName] = $row[$position] ?? null;
        }
        return $data;
    }

    protected function validateStudentData(array $data, int $rowNumber): array
    {
        $errors = [];

        if (empty(trim((string) ($data['student_id'] ?? '')))) {
            $errors[] = ['row' => $rowNumber, 'message' => 'Talaba ID majburiy'];
        }

        $fullName = trim((string) ($data['full_name'] ?? ''));
        if (empty($fullName)) {
            $errors[] = ['row' => $rowNumber, 'message' => 'To\'liq ism majburiy'];
        }

        if (empty(trim((string) ($data['passport'] ?? '')))) {
            $errors[] = ['row' => $rowNumber, 'message' => 'Pasport raqami majburiy'];
        }

        if (empty(trim((string) ($data['jshshir'] ?? '')))) {
            $errors[] = ['row' => $rowNumber, 'message' => 'JSHSHIR-kod majburiy'];
        }

        $course = $this->extractCourseNumber($data['course'] ?? null);
        if (empty($course) || $course < 1 || $course > 6) {
            $errors[] = ['row' => $rowNumber, 'message' => 'Kurs 1-6 oralig\'ida bo\'lishi kerak'];
        }

        if (empty(trim((string) ($data['faculty'] ?? '')))) {
            $errors[] = ['row' => $rowNumber, 'message' => 'Fakultet majburiy'];
        }

        if (empty(trim((string) ($data['group'] ?? '')))) {
            $errors[] = ['row' => $rowNumber, 'message' => 'Guruh majburiy'];
        }

        return ['valid' => empty($errors), 'errors' => $errors];
    }

    protected function extractCourseNumber($value): ?int
    {
        if (empty($value)) return null;
        $value = trim((string) $value);
        if (is_numeric($value)) return (int) $value;
        if (preg_match('/(\d+)/', $value, $matches)) {
            return (int) $matches[1];
        }
        return null;
    }

    /**
     * Create TestResults for active test assignments in student's group
     */
    protected function createTestResultsForStudent(Student $student): void
    {
        // Find active test assignments for this group
        $activeAssignments = TestAssignment::where('group_id', $student->group_id)
            ->where('is_active', true)
            ->where('end_time', '>=', now())
            ->get();

        foreach ($activeAssignments as $assignment) {
            // Check if TestResult already exists
            $exists = TestResult::where('test_assignment_id', $assignment->id)
                ->where('student_id', $student->id)
                ->exists();

            if (!$exists) {
                TestResult::create([
                    'test_assignment_id' => $assignment->id,
                    'student_id' => $student->id,
                    'total_questions' => $assignment->question_count,
                    'status' => 'not_started',
                    'score' => 0,
                    'correct_answers' => 0,
                ]);
            }
        }
    }
}
