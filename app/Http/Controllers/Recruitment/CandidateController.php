<?php

namespace App\Http\Controllers\Recruitment;

use App\Http\Controllers\Controller;
use App\Models\RecruitmentCandidate;
use App\Models\RecruitmentDepartment;
use App\Models\RecruitmentPosition;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class CandidateController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:super_admin,lead_manager,telecallers');
    }

    /**
     * Build the base query with filters applied.
     */
    private function buildFilteredQuery(Request $request)
    {
        $query = RecruitmentCandidate::with(['position', 'department']);

        // Search
        if ($request->filled('search')) {
            $query->search($request->search);
        }

        // Position filter
        if ($request->filled('position_id')) {
            $query->where('recruitment_position_id', $request->position_id);
        }

        // Department filter
        if ($request->filled('department_id')) {
            $query->where('recruitment_department_id', $request->department_id);
        }

        // Interview Status filter
        if ($request->filled('interview_status')) {
            $query->where('interview_status', $request->interview_status);
        }

        // Interview Result filter
        if ($request->filled('interview_result')) {
            $query->where('interview_result', $request->interview_result);
        }

        // Joining Status filter
        if ($request->filled('joining_status')) {
            $query->where('joining_status', $request->joining_status);
        }

        // Reference filter
        if ($request->filled('reference')) {
            $query->where('reference', $request->reference);
        }

        // Decision Reason filter
        if ($request->filled('decision_reason')) {
            $query->where('decision_reason', $request->decision_reason);
        }

        // Date range filter (interview date)
        if ($request->filled('date_from')) {
            $query->where('interview_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->where('interview_date', '<=', $request->date_to);
        }

        return $query;
    }

    /**
     * Display candidate listing with stats, search, filters.
     */
    public function index(Request $request)
    {
        $query = $this->buildFilteredQuery($request);

        // Sorting
        $sortColumn = $request->get('sort_column', 'created_at');
        $sortDirection = $request->get('sort_direction', 'desc');
        $allowedColumns = [
            'full_name', 'phone', 'interview_date', 'interview_status',
            'interview_result', 'joining_status', 'created_at', 'expected_join_date', 'joined_date', 'follow_up_date'
        ];
        if (in_array($sortColumn, $allowedColumns)) {
            $query->orderBy($sortColumn, $sortDirection);
        } else {
            $query->orderBy('created_at', 'desc');
        }

        $candidates = $query->paginate(20);

        // Statistics (computed from ALL candidates, not filtered)
        $stats = [
            'total'    => RecruitmentCandidate::count(),
            'selected' => RecruitmentCandidate::where('interview_result', 'Selected')->count(),
            'joined'   => RecruitmentCandidate::where('joining_status', 'Joined')->count(),
            'pending'  => RecruitmentCandidate::where('joining_status', 'Pending')->count(),
        ];

        // Dropdown data for filters
        $departments = RecruitmentDepartment::active()->orderBy('name')->get();
        $positions = RecruitmentPosition::active()->orderBy('name')->get();

        if ($request->ajax()) {
            return response()->json([
                'html' => view('recruitment.candidates.partials.table-rows', compact('candidates'))->render(),
                'pagination' => $candidates->links('pagination::bootstrap-5')->render(),
                'total' => $candidates->total(),
                'stats' => $stats,
            ]);
        }

        return view('recruitment.candidates.index', compact('candidates', 'stats', 'departments', 'positions'));
    }

    /**
     * Show create form.
     */
    public function create()
    {
        $departments = RecruitmentDepartment::active()->orderBy('name')->get();
        $positions = RecruitmentPosition::active()->orderBy('name')->get();

        return view('recruitment.candidates.create', compact('departments', 'positions'));
    }

    /**
     * Store a new candidate.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'full_name'                => 'required|string|max:255',
            'phone'                    => 'required|string|max:20',
            'email'                    => 'nullable|email|max:255',
            'place'                    => 'nullable|string|max:255',
            'district'                 => 'nullable|string|max:255',
            'date_of_birth'            => 'nullable|date|before:today',
            'driving_skill'            => 'required|in:Yes,No',
            'recruitment_position_id'  => 'required|exists:recruitment_positions,id',
            'recruitment_department_id'=> 'required|exists:recruitment_departments,id',
            'current_company'          => 'nullable|string|max:255',
            'interview_date'           => 'nullable|date',
            'interview_time'           => 'nullable|date_format:H:i',
            'interviewer'              => 'nullable|string|max:255',
            'interview_status'         => 'required|in:' . implode(',', RecruitmentCandidate::INTERVIEW_STATUSES),
            'interview_result'         => 'required|in:' . implode(',', RecruitmentCandidate::INTERVIEW_RESULTS),
            'joining_status'           => 'required|in:' . implode(',', RecruitmentCandidate::JOINING_STATUSES),
            'joined_date'              => 'nullable|date',
            'expected_join_date'       => 'nullable|date',
            'follow_up_date'           => 'nullable|date',
            'decision_reason'          => 'nullable|in:' . implode(',', RecruitmentCandidate::DECISION_REASONS),
            'remarks'                  => 'nullable|string|max:2000',
            'reference'                => 'nullable|in:' . implode(',', RecruitmentCandidate::REFERENCES),
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $data = $validator->validated();
        $data['created_by'] = auth()->id();

        $candidate = RecruitmentCandidate::create($data);

        Log::info('Recruitment candidate created', [
            'id' => $candidate->id,
            'name' => $candidate->full_name,
            'user' => auth()->id(),
        ]);

        return redirect()->route('recruitment.candidates.index')
            ->with('success', 'Candidate added successfully.');
    }

    /**
     * Show candidate details.
     */
    public function show(RecruitmentCandidate $candidate)
    {
        $candidate->load(['position', 'department', 'creator']);

        return view('recruitment.candidates.show', compact('candidate'));
    }

    /**
     * Show edit form.
     */
    public function edit(RecruitmentCandidate $candidate)
    {
        $candidate->load(['position', 'department']);

        $departments = RecruitmentDepartment::active()->orderBy('name')->get();
        $positions = RecruitmentPosition::active()->orderBy('name')->get();

        return view('recruitment.candidates.edit', compact('candidate', 'departments', 'positions'));
    }

    /**
     * Update candidate.
     */
    public function update(Request $request, RecruitmentCandidate $candidate)
    {
        $validator = Validator::make($request->all(), [
            'full_name'                => 'required|string|max:255',
            'phone'                    => 'required|string|max:20',
            'email'                    => 'nullable|email|max:255',
            'place'                    => 'nullable|string|max:255',
            'district'                 => 'nullable|string|max:255',
            'date_of_birth'            => 'nullable|date|before:today',
            'driving_skill'            => 'required|in:Yes,No',
            'recruitment_position_id'  => 'required|exists:recruitment_positions,id',
            'recruitment_department_id'=> 'required|exists:recruitment_departments,id',
            'current_company'          => 'nullable|string|max:255',
            'interview_date'           => 'nullable|date',
            'interview_time'           => 'nullable|date_format:H:i',
            'interviewer'              => 'nullable|string|max:255',
            'interview_status'         => 'required|in:' . implode(',', RecruitmentCandidate::INTERVIEW_STATUSES),
            'interview_result'         => 'required|in:' . implode(',', RecruitmentCandidate::INTERVIEW_RESULTS),
            'joining_status'           => 'required|in:' . implode(',', RecruitmentCandidate::JOINING_STATUSES),
            'joined_date'              => 'nullable|date',
            'expected_join_date'       => 'nullable|date',
            'follow_up_date'           => 'nullable|date',
            'decision_reason'          => 'nullable|in:' . implode(',', RecruitmentCandidate::DECISION_REASONS),
            'remarks'                  => 'nullable|string|max:2000',
            'reference'                => 'nullable|in:' . implode(',', RecruitmentCandidate::REFERENCES),
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $candidate->update($validator->validated());

        Log::info('Recruitment candidate updated', [
            'id' => $candidate->id,
            'name' => $candidate->full_name,
            'user' => auth()->id(),
        ]);

        return redirect()->route('recruitment.candidates.show', $candidate)
            ->with('success', 'Candidate updated successfully.');
    }

    /**
     * Delete candidate (soft delete).
     */
    public function destroy(RecruitmentCandidate $candidate)
    {
        $candidate->delete();

        Log::info('Recruitment candidate deleted', [
            'id' => $candidate->id,
            'name' => $candidate->full_name,
            'user' => auth()->id(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Candidate deleted successfully.',
        ]);
    }

    /**
     * Bulk delete selected candidates.
     */
    public function bulkDelete(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'ids' => 'required|array|min:1',
            'ids.*' => 'exists:recruitment_candidates,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid candidate selection.',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $ids = $request->ids;
        $count = RecruitmentCandidate::whereIn('id', $ids)->delete();

        Log::info('Recruitment candidates bulk deleted', [
            'count' => $count,
            'ids' => $ids,
            'user' => auth()->id(),
        ]);

        return response()->json([
            'success' => true,
            'message' => "{$count} candidate(s) deleted successfully.",
        ]);
    }

    /**
     * Export filtered candidates (Excel .xlsx or CSV)
     */
    public function export(Request $request)
    {
        try {
            $query = $this->buildFilteredQuery($request);
            $query->orderBy('created_at', 'desc');

            $candidates = $query->get();
            $format = strtolower($request->get('format', 'xlsx'));

            $headersList = [
                'SL No', 'Candidate Name', 'Phone Number', 'Email', 'Place', 'District',
                'Date of Birth', 'Age', 'Driving Skill', 'Position Applied',
                'Department', 'Interview Date', 'Interview Time', 'Interviewer',
                'Interview Status', 'Interview Result', 'Joining Status',
                'Expected Joining Date', 'Joined Date', 'Follow-up Date', 'Decision Reason',
                'Remarks', 'Reference'
            ];

            // Handle CSV format export
            if ($format === 'csv') {
                $fileName = 'candidates_export_' . now()->format('Y-m-d_His') . '.csv';
                $headers = [
                    'Content-Type' => 'text/csv; charset=UTF-8',
                    'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
                    'Pragma' => 'no-cache',
                    'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
                    'Expires' => '0',
                ];

                $callback = function () use ($candidates, $headersList) {
                    $file = fopen('php://output', 'w');
                    // UTF-8 BOM for Excel compatibility
                    fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));
                    fputcsv($file, $headersList);

                    foreach ($candidates as $index => $candidate) {
                        fputcsv($file, [
                            $index + 1,
                            $candidate->full_name,
                            $candidate->phone,
                            $candidate->email ?? '',
                            $candidate->place ?? '',
                            $candidate->district ?? '',
                            $candidate->date_of_birth ? $candidate->date_of_birth->format('d-m-Y') : '',
                            $candidate->age ? $candidate->age . ' yrs' : '',
                            $candidate->driving_skill ?? 'No',
                            optional($candidate->position)->name ?? '',
                            optional($candidate->department)->name ?? '',
                            $candidate->interview_date ? $candidate->interview_date->format('d-m-Y') : '',
                            $candidate->interview_time ?? '',
                            $candidate->interviewer ?? '',
                            $candidate->interview_status,
                            $candidate->interview_result,
                            $candidate->joining_status,
                            $candidate->expected_join_date ? $candidate->expected_join_date->format('d-m-Y') : '',
                            $candidate->joined_date ? $candidate->joined_date->format('d-m-Y') : '',
                            $candidate->follow_up_date ? $candidate->follow_up_date->format('d-m-Y') : '',
                            $candidate->decision_reason ?? '',
                            $candidate->remarks ?? '',
                            $candidate->reference ?? '',
                        ]);
                    }
                    fclose($file);
                };

                Log::info('Candidate CSV export', [
                    'user' => auth()->id(),
                    'filters' => $request->all(),
                    'count' => $candidates->count(),
                ]);

                return response()->streamDownload($callback, $fileName, $headers);
            }

            // Excel .xlsx format export
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle('Candidates');

            $col = 'A';
            foreach ($headersList as $header) {
                $sheet->setCellValue($col . '1', $header);
                $col++;
            }

            $lastColLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(count($headersList));

            // Style headers
            $headerRange = 'A1:' . $lastColLetter . '1';
            $sheet->getStyle($headerRange)->applyFromArray([
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 11],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '4472C4'],
                ],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                'borders' => [
                    'allBorders' => ['borderStyle' => Border::BORDER_THIN],
                ],
            ]);

            // Data rows
            $row = 2;
            foreach ($candidates as $index => $candidate) {
                $sheet->setCellValue('A' . $row, $index + 1);
                $sheet->setCellValue('B' . $row, $candidate->full_name);
                $sheet->setCellValue('C' . $row, $candidate->phone);
                $sheet->setCellValue('D' . $row, $candidate->email ?? '');
                $sheet->setCellValue('E' . $row, $candidate->place ?? '');
                $sheet->setCellValue('F' . $row, $candidate->district ?? '');
                $sheet->setCellValue('G' . $row, $candidate->date_of_birth ? $candidate->date_of_birth->format('d-m-Y') : '');
                $sheet->setCellValue('H' . $row, $candidate->age ? $candidate->age . ' yrs' : '');
                $sheet->setCellValue('I' . $row, $candidate->driving_skill ?? 'No');
                $sheet->setCellValue('J' . $row, optional($candidate->position)->name ?? '');
                $sheet->setCellValue('K' . $row, optional($candidate->department)->name ?? '');
                $sheet->setCellValue('L' . $row, $candidate->interview_date ? $candidate->interview_date->format('d-m-Y') : '');
                $sheet->setCellValue('M' . $row, $candidate->interview_time ?? '');
                $sheet->setCellValue('N' . $row, $candidate->interviewer ?? '');
                $sheet->setCellValue('O' . $row, $candidate->interview_status);
                $sheet->setCellValue('P' . $row, $candidate->interview_result);
                $sheet->setCellValue('Q' . $row, $candidate->joining_status);
                $sheet->setCellValue('R' . $row, $candidate->expected_join_date ? $candidate->expected_join_date->format('d-m-Y') : '');
                $sheet->setCellValue('S' . $row, $candidate->joined_date ? $candidate->joined_date->format('d-m-Y') : '');
                $sheet->setCellValue('T' . $row, $candidate->follow_up_date ? $candidate->follow_up_date->format('d-m-Y') : '');
                $sheet->setCellValue('U' . $row, $candidate->decision_reason ?? '');
                $sheet->setCellValue('V' . $row, $candidate->remarks ?? '');
                $sheet->setCellValue('W' . $row, $candidate->reference ?? '');
                $row++;
            }

            // Auto-width columns
            for ($i = 1; $i <= count($headersList); $i++) {
                $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i);
                $sheet->getColumnDimension($colLetter)->setAutoSize(true);
            }

            // Add data borders
            if ($row > 2) {
                $sheet->getStyle('A2:' . $lastColLetter . ($row - 1))->applyFromArray([
                    'borders' => [
                        'allBorders' => ['borderStyle' => Border::BORDER_THIN],
                    ],
                ]);
            }

            $fileName = 'candidates_export_' . now()->format('Y-m-d_His') . '.xlsx';
            $writer = new Xlsx($spreadsheet);

            $headers = [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
                'Cache-Control' => 'max-age=0',
            ];

            Log::info('Candidate Excel export', [
                'user' => auth()->id(),
                'filters' => $request->all(),
                'count' => $candidates->count(),
            ]);

            return response()->streamDownload(function () use ($writer) {
                $writer->save('php://output');
            }, $fileName, $headers);

        } catch (\Exception $e) {
            Log::error('Candidate export error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            return back()->withErrors(['export' => 'Error exporting candidates: ' . $e->getMessage()]);
        }
    }
}
