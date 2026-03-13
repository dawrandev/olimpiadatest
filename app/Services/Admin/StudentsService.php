<?php

namespace App\Services\Admin;

use App\Models\Student;
use App\Repositories\Admin\StudentRepository;
use Illuminate\Http\UploadedFile;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class StudentsService
{
    /**
     * Column positions (0-indexed) for the 7 required columns
     * A=0, B=1, C=2, D=3, E=4, F=5, G=6
     */
    protected array $columnPositions = [
        'student_id' => 0,  // A - Talaba ID
        'full_name'  => 1,  // B - To'liq ismi
        'passport'   => 2,  // C - Pasport raqami
        'jshshir'    => 3,  // D - JSHSHIR-kod
        'course'     => 4,  // E - Kurs
        'faculty'    => 5,  // F - Fakultet
        'group'      => 6,  // G - Guruh
    ];

    public function __construct(protected StudentRepository $studentRepository)
    {
        //
    }

    public function getAllStudents(array $filters)
    {
        return $this->studentRepository->getFilteredStudents($filters);
    }

    public function createStudent($data)
    {
        return $this->studentRepository->create($data);
    }

    public function updateStudent($data, int $id)
    {
        return $this->studentRepository->update($data, $id);
    }

    public function getStudentDetails($studentId)
    {
        $student = $this->studentRepository->findWithRelations($studentId);
        $allResults = $this->studentRepository->getAllResultsByStudent($studentId);
        $testResults = $this->studentRepository->getPaginatedResultsByStudent($studentId);

        return compact('student', 'allResults', 'testResults');
    }

    /**
     * Process Excel file upload for students
     */
    public function processExcelUpload(UploadedFile $file): array
    {
        $filePath = $file->getPathname();

        try {
            $spreadsheet = IOFactory::load($filePath);
            $worksheet = $spreadsheet->getActiveSheet();
            $rows = $worksheet->toArray();

            // Remove header row (first row)
            array_shift($rows);

            $uploadedCount = 0;
            $skippedCount = 0;
            $errorCount = 0;
            $errors = [];
            $createdFaculties = [];
            $createdGroups = [];

            // Get existing JSSHIRs for duplicate check
            $existingJshshirs = $this->studentRepository->getAllJshshirs();

            // Cache for faculties and groups to avoid repeated DB queries
            $facultyCache = [];
            $groupCache = [];

            DB::beginTransaction();

            try {
                foreach ($rows as $index => $row) {
                    $rowNumber = $index + 2; // +2 because: +1 for header, +1 for Excel row numbering

                    // Skip empty rows
                    if (empty(array_filter($row))) {
                        continue;
                    }

                    // Extract data using fixed column positions
                    $data = $this->extractRowData($row);

                    // Check for JSHSHIR duplicate - skip if exists
                    $jshshir = trim((string) $data['jshshir']);
                    if (!empty($jshshir) && in_array($jshshir, $existingJshshirs)) {
                        $skippedCount++;
                        continue;
                    }

                    // Validate row data
                    $validation = $this->validateStudentData($data, $rowNumber);

                    if ($validation['valid']) {
                        try {
                            // Find or create faculty
                            $facultyName = trim((string) $data['faculty']);
                            if (!isset($facultyCache[$facultyName])) {
                                $faculty = $this->studentRepository->findOrCreateFaculty($facultyName);
                                $facultyCache[$facultyName] = $faculty;

                                // Track if newly created
                                if ($faculty->wasRecentlyCreated) {
                                    $createdFaculties[] = $facultyName;
                                }
                            } else {
                                $faculty = $facultyCache[$facultyName];
                            }

                            // Find or create group
                            $groupName = trim((string) $data['group']);
                            $groupKey = $faculty->id . '_' . $groupName;
                            if (!isset($groupCache[$groupKey])) {
                                $group = $this->studentRepository->findOrCreateGroup($groupName, $faculty->id);
                                $groupCache[$groupKey] = $group;

                                // Track if newly created
                                if ($group->wasRecentlyCreated) {
                                    $createdGroups[] = $groupName;
                                }
                            } else {
                                $group = $groupCache[$groupKey];
                            }

                            // Prepare student data
                            $studentData = [
                                'student_id' => trim((string) $data['student_id']),
                                'full_name' => trim((string) $data['full_name']),
                                'passport' => trim((string) $data['passport']),
                                'jshshir' => $jshshir,
                                'course' => $this->extractCourseNumber($data['course']),
                                'group_id' => $group->id,
                                'login' => $jshshir, // JSHSHIR as login
                                'password' => trim((string) $data['passport']), // Passport as password (will be hashed)
                            ];

                            // Create student
                            $this->studentRepository->create($studentData);

                            // Add to existing JSSHIRs to prevent duplicates in same file
                            $existingJshshirs[] = $jshshir;

                            $uploadedCount++;
                        } catch (\Exception $e) {
                            $errorCount++;
                            $errors[] = [
                                'row' => $rowNumber,
                                'message' => __('Database error: :error', ['error' => $e->getMessage()])
                            ];
                        }
                    } else {
                        $errorCount++;
                        $errors = array_merge($errors, $validation['errors']);
                    }
                }

                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }

            return [
                'success' => $errorCount === 0,
                'uploaded_count' => $uploadedCount,
                'skipped_count' => $skippedCount,
                'error_count' => $errorCount,
                'errors' => $errors,
                'created_faculties' => array_unique($createdFaculties),
                'created_groups' => array_unique($createdGroups),
            ];
        } finally {
            if (file_exists($filePath)) {
                @unlink($filePath);
            }

            if (isset($spreadsheet)) {
                $spreadsheet->disconnectWorksheets();
                unset($spreadsheet);
            }
        }
    }

    /**
     * Extract row data using fixed column positions
     */
    protected function extractRowData(array $row): array
    {
        $data = [];

        foreach ($this->columnPositions as $fieldName => $position) {
            $data[$fieldName] = $row[$position] ?? null;
        }

        return $data;
    }

    /**
     * Validate student data from Excel
     */
    protected function validateStudentData(array $data, int $rowNumber): array
    {
        $errors = [];

        // Validate student_id
        if (empty(trim((string) $data['student_id']))) {
            $errors[] = [
                'row' => $rowNumber,
                'message' => __('Talaba ID majburiy (A ustuni)')
            ];
        }

        // Validate full_name
        $fullName = trim((string) $data['full_name']);
        if (empty($fullName)) {
            $errors[] = [
                'row' => $rowNumber,
                'message' => __('To\'liq ism majburiy (B ustuni)')
            ];
        } elseif (mb_strlen($fullName) < 3) {
            $errors[] = [
                'row' => $rowNumber,
                'message' => __('To\'liq ism kamida 3 ta belgidan iborat bo\'lishi kerak')
            ];
        }

        // Validate passport
        if (empty(trim((string) $data['passport']))) {
            $errors[] = [
                'row' => $rowNumber,
                'message' => __('Pasport raqami majburiy (C ustuni)')
            ];
        }

        // Validate JSHSHIR
        if (empty(trim((string) $data['jshshir']))) {
            $errors[] = [
                'row' => $rowNumber,
                'message' => __('JSHSHIR-kod majburiy (D ustuni)')
            ];
        }

        // Validate course (extract number from "1-kurs", "2-kurs", etc.)
        $course = $this->extractCourseNumber($data['course']);
        if (empty($course)) {
            $errors[] = [
                'row' => $rowNumber,
                'message' => __('Kurs majburiy (E ustuni)')
            ];
        } elseif ($course < 1 || $course > 6) {
            $errors[] = [
                'row' => $rowNumber,
                'message' => __('Kurs 1-6 oralig\'ida bo\'lishi kerak')
            ];
        }

        // Validate faculty
        if (empty(trim((string) $data['faculty']))) {
            $errors[] = [
                'row' => $rowNumber,
                'message' => __('Fakultet majburiy (F ustuni)')
            ];
        }

        // Validate group
        if (empty(trim((string) $data['group']))) {
            $errors[] = [
                'row' => $rowNumber,
                'message' => __('Guruh majburiy (G ustuni)')
            ];
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }

    /**
     * Extract course number from string like "1-kurs", "2-kurs" or just "1", "2"
     */
    protected function extractCourseNumber($value): ?int
    {
        if (empty($value)) {
            return null;
        }

        $value = trim((string) $value);

        // If it's already a number
        if (is_numeric($value)) {
            return (int) $value;
        }

        // Extract number from string like "1-kurs", "2-kurs", "1 kurs", etc.
        if (preg_match('/(\d+)/', $value, $matches)) {
            return (int) $matches[1];
        }

        return null;
    }
}
