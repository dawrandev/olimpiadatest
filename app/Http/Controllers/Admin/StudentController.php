<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StudentStoreRequest;
use App\Http\Requests\Admin\StudentUpdateRequest;
use App\Jobs\ProcessStudentExcelUpload;
use App\Models\Faculty;
use App\Models\Group;
use App\Models\Student;
use App\Models\StudentUpload;
use App\Models\TestResult;
use App\Models\User;
use App\Services\Admin\StudentsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class StudentController extends Controller
{
    public function __construct(protected StudentsService $studentService) {}

    public function index(Request $request, $faculty = null)
    {
        if ($faculty) {
            $faculty = Faculty::findOrFail($faculty);
            $students = $faculty->students()->paginate($request->get('per_page', 10));
        } else {
            $filters = $request->only(['search', 'faculty_id', 'group_id', 'per_page']);
            $students = $this->studentService->getAllStudents($filters);
        }

        return view('pages.admin.students.index', compact('students', 'faculty'));
    }

    public function create()
    {
        return view('pages.admin.students.create');
    }

    public function store(StudentStoreRequest $request)
    {
        $this->studentService->createStudent($request->validated());

        return redirect()->route('admin.students.index')->with('success', __('Student created successfully'));
    }

    public function show($id)
    {
        $data = $this->studentService->getStudentDetails($id);

        return view('pages.admin.students.show', $data);
    }


    public function edit(int $id)
    {
        $student = Student::with('user')->findOrFail($id);

        return view('pages.admin.students.edit', compact('student'));
    }

    public function update(StudentUpdateRequest $request, int $id)
    {
        $student = $this->studentService->updateStudent($request->validated(), $id);

        return redirect()->route('admin.students.index')->with('success', __('Student updated successfully'));
    }

    public function destroy(int $id)
    {
        $student = User::where('id', $id)->delete();

        return redirect()->route('admin.students.index')->with('success', __('Student deleted successfully'));
    }

    public function uploadForm()
    {
        // Get recent uploads for current user
        $recentUploads = StudentUpload::where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        return view('pages.admin.students.upload-students', compact('recentUploads'));
    }

    public function downloadTemplate()
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set column headers matching the required Excel format
        $headers = [
            'Talaba ID',
            'To\'liq ismi',
            'Pasport raqami',
            'JSHSHIR-kod',
            'Kurs',
            'Fakultet',
            'Guruh'
        ];
        $sheet->fromArray($headers, null, 'A1');

        $headerStyle = [
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
                'size' => 12
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '4472C4']
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000']
                ]
            ]
        ];

        $sheet->getStyle('A1:G1')->applyFromArray($headerStyle);

        // Sample data
        $sampleData = [
            ['123456', 'Sipatdinov Dawranbek Islamatdin uli', 'AB1234567', '12345678901234', '1-kurs', 'Fizika-matematika fakulteti', 'FM-21'],
            ['123457', 'Kayratdinov Islam Mirzabek uli', 'AB7654321', '43210987654321', '2-kurs', 'Informatika fakulteti', 'IF-22'],
        ];
        $sheet->fromArray($sampleData, null, 'A2');

        // Set column widths
        $sheet->getColumnDimension('A')->setWidth(15);
        $sheet->getColumnDimension('B')->setWidth(40);
        $sheet->getColumnDimension('C')->setWidth(20);
        $sheet->getColumnDimension('D')->setWidth(20);
        $sheet->getColumnDimension('E')->setWidth(10);
        $sheet->getColumnDimension('F')->setWidth(35);
        $sheet->getColumnDimension('G')->setWidth(15);

        $dataStyle = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'CCCCCC']
                ]
            ],
            'alignment' => [
                'vertical' => Alignment::VERTICAL_CENTER
            ]
        ];
        $sheet->getStyle('A2:G3')->applyFromArray($dataStyle);

        $sheet->getRowDimension(1)->setRowHeight(25);

        $writer = new Xlsx($spreadsheet);
        $fileName = 'students_template_' . date('Y-m-d_His') . '.xlsx';
        $tempFile = storage_path('app/temp/' . $fileName);

        if (!file_exists(storage_path('app/temp'))) {
            mkdir(storage_path('app/temp'), 0755, true);
        }

        $writer->save($tempFile);

        return response()->download($tempFile, $fileName)->deleteFileAfterSend(true);
    }

    public function uploadExcel(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls|max:10240', // 10MB
        ]);

        try {
            $file = $request->file('file');
            $fileName = $file->getClientOriginalName();
            $filePath = $file->store('student-uploads');

            // Create upload record
            $upload = StudentUpload::create([
                'user_id' => Auth::id(),
                'file_name' => $fileName,
                'file_path' => $filePath,
                'status' => 'pending',
            ]);

            // Dispatch job
            ProcessStudentExcelUpload::dispatch($upload->id);

            return response()->json([
                'success' => true,
                'upload_id' => $upload->id,
                'message' => __('Fayl yuklandi va navbatga qo\'shildi'),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function uploadProgress(int $id)
    {
        $upload = StudentUpload::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        return response()->json([
            'id' => $upload->id,
            'status' => $upload->status,
            'total_rows' => $upload->total_rows,
            'processed_rows' => $upload->processed_rows,
            'progress_percent' => $upload->progress_percent,
            'uploaded_count' => $upload->uploaded_count,
            'skipped_count' => $upload->skipped_count,
            'error_count' => $upload->error_count,
            'errors' => $upload->errors ?? [],
            'created_faculties' => $upload->created_faculties ?? [],
            'created_groups' => $upload->created_groups ?? [],
            'error_message' => $upload->error_message,
            'completed_at' => $upload->completed_at?->format('H:i:s'),
        ]);
    }
}
